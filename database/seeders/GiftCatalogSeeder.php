<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Support\StockImageDownloader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GiftCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('categories');
        Storage::disk('public')->makeDirectory('products');
        Storage::disk('public')->makeDirectory('banners');

        $flamingo = Shop::query()->where('slug', 'flamingo')->firstOrFail();
        $bubbles = Shop::query()->where('slug', 'bubbles')->firstOrFail();

        $this->wipeCatalog([$flamingo->id, $bubbles->id]);

        $categories = [
            [
                'name' => 'ساعات يد',
                'name_en' => 'Wristwatches',
                'description' => 'ساعات أنيقة تناسب الإطلالات اليومية والمناسبات.',
                'description_en' => 'Elegant watches for everyday looks and special occasions.',
            ],
            [
                'name' => 'محافظ',
                'name_en' => 'Wallets',
                'description' => 'محافظ جلدية وعصرية بهدايا عملية فاخرة.',
                'description_en' => 'Leather and modern wallets — practical luxury gifts.',
            ],
            [
                'name' => 'عطور',
                'name_en' => 'Perfume',
                'description' => 'عطور مختارة بثبات أنيق ولمسة راقية.',
                'description_en' => 'Selected fragrances with lasting elegance.',
            ],
            [
                'name' => 'نظارات شمس',
                'name_en' => 'Sunglasses',
                'description' => 'نظارات شمس عصرية بحماية أنيقة للمظهر.',
                'description_en' => 'Modern sunglasses with stylish protection.',
            ],
            [
                'name' => 'حقائب',
                'name_en' => 'Bags',
                'description' => 'حقائب عملية وأنيقة للاستخدام اليومي والسفر.',
                'description_en' => 'Practical and elegant bags for daily use and travel.',
            ],
            [
                'name' => 'أساور',
                'name_en' => 'Bracelets',
                'description' => 'أساور معدنية وحجرية تضيف لمسة مميزة.',
                'description_en' => 'Metal and stone bracelets with a distinctive touch.',
            ],
            [
                'name' => 'أحزمة',
                'name_en' => 'Belts',
                'description' => 'أحزمة جلدية كلاسيكية بتشطيبات فاخرة.',
                'description_en' => 'Classic leather belts with refined finishes.',
            ],
        ];

        $flamingoCatalog = $this->flamingoProducts();
        $bubblesCatalog = $this->bubblesProducts();

        $flamingoPalette = [
            [109, 21, 128],
            [139, 35, 168],
            [180, 79, 198],
            [233, 198, 206],
            [228, 205, 180],
            [61, 10, 75],
            [92, 40, 120],
        ];

        $bubblesPalette = [
            [232, 70, 124],
            [247, 198, 217],
            [142, 31, 75],
            [255, 143, 199],
            [230, 199, 156],
            [201, 167, 255],
            [190, 60, 110],
        ];

        $images = new StockImageDownloader;

        $this->seedShop($flamingo, $categories, $flamingoCatalog, $flamingoPalette, 'FLM', 'Flamingo', $images);
        $this->seedShop($bubbles, $categories, $bubblesCatalog, $bubblesPalette, 'BUB', 'Bubbles', $images);

        $this->seedBanners($flamingo, $flamingoPalette[0], 'Flamingo', $images);
        $this->seedBanners($bubbles, $bubblesPalette[0], 'Bubbles', $images);
    }

    /**
     * @param  list<int>  $shopIds
     */
    protected function wipeCatalog(array $shopIds): void
    {
        $reservationIds = DB::table('reservations')->whereIn('shop_id', $shopIds)->pluck('id');

        if ($reservationIds->isNotEmpty()) {
            DB::table('reservation_items')->whereIn('reservation_id', $reservationIds)->delete();
            DB::table('reservations')->whereIn('id', $reservationIds)->delete();
        }

        $productIds = Product::withoutGlobalScopes()->whereIn('shop_id', $shopIds)->pluck('id');

        if ($productIds->isNotEmpty()) {
            DB::table('product_images')->whereIn('product_id', $productIds)->delete();
            DB::table('product_variants')->whereIn('product_id', $productIds)->delete();
            Product::withoutGlobalScopes()->whereIn('id', $productIds)->delete();
        }

        Category::withoutGlobalScopes()->whereIn('shop_id', $shopIds)->delete();
        Banner::withoutGlobalScopes()->whereIn('shop_id', $shopIds)->delete();
        DB::table('activity_logs')->whereIn('shop_id', $shopIds)->delete();
    }

    /**
     * @param  list<array{name: string, name_en?: string, description: string, description_en?: string}>  $categories
     * @param  array<string, list<array<string, mixed>>>  $catalog
     * @param  list<array{0: int, 1: int, 2: int}>  $palette
     */
    protected function seedShop(
        Shop $shop,
        array $categories,
        array $catalog,
        array $palette,
        string $skuPrefix,
        string $imageLabel,
        StockImageDownloader $images,
    ): void {
        $skuCounter = 1;

        foreach ($categories as $index => $item) {
            $catPath = $images->download(
                'categories',
                strtolower($skuPrefix).'-cat-'.$index,
                $images->urlFor($item['name'], $index),
                $item['name'],
                $index
            ) ?? $this->makeGradientImage(
                'categories',
                strtolower($skuPrefix).'-cat-'.$index,
                $palette[$index % count($palette)],
                $imageLabel
            );

            $category = Category::withoutGlobalScopes()->create([
                'shop_id' => $shop->id,
                'name' => $item['name'],
                'name_en' => $item['name_en'] ?? null,
                'slug' => $this->arabicSlug($item['name_en'] ?? $item['name']),
                'description' => $item['description'],
                'description_en' => $item['description_en'] ?? null,
                'image' => $catPath,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            foreach ($catalog[$item['name']] as $pIndex => $productData) {
                $sku = $skuPrefix.'-'.str_pad((string) $skuCounter++, 4, '0', STR_PAD_LEFT);
                $hasVariants = ! empty($productData['variants']);

                $product = Product::withoutGlobalScopes()->create([
                    'shop_id' => $shop->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'name_en' => $productData['name_en'] ?? null,
                    'slug' => $this->arabicSlug($productData['name_en'] ?? $productData['name']),
                    'short_description' => $productData['short_description'],
                    'short_description_en' => $productData['short_description_en'] ?? null,
                    'description' => $productData['description'],
                    'description_en' => $productData['description_en'] ?? null,
                    'price' => $productData['price'],
                    'sale_price' => $productData['sale_price'] ?? null,
                    'stock_quantity' => $hasVariants ? 0 : $productData['stock'],
                    'reserved_quantity' => 0,
                    'sku' => $sku,
                    'brand' => $productData['brand'],
                    'color' => $productData['color'],
                    'color_en' => $productData['color_en'] ?? null,
                    'material' => $productData['material'],
                    'material_en' => $productData['material_en'] ?? null,
                    'size' => $productData['size'] ?? null,
                    'size_en' => $productData['size_en'] ?? null,
                    'is_featured' => $pIndex === 0,
                    'is_active' => true,
                ]);

                $imgPath = $images->download(
                    'products',
                    strtolower($skuPrefix).'-prod-'.$product->id,
                    $images->urlFor($item['name'], $pIndex + $index),
                    $item['name'],
                    $pIndex + $index
                ) ?? $this->makeGradientImage(
                    'products',
                    strtolower($skuPrefix).'-prod-'.$product->id,
                    $palette[($index + $pIndex) % count($palette)],
                    $imageLabel
                );

                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => $imgPath,
                    'sort_order' => 1,
                ]);

                // Second gallery image for a richer product page
                $imgPath2 = $images->download(
                    'products',
                    strtolower($skuPrefix).'-prod-'.$product->id.'-b',
                    $images->urlFor($item['name'], $pIndex + $index + 1),
                    $item['name'],
                    $pIndex + $index + 1
                );
                if ($imgPath2) {
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'path' => $imgPath2,
                        'sort_order' => 2,
                    ]);
                }

                if ($hasVariants) {
                    foreach ($productData['variants'] as $vIndex => $variant) {
                        ProductVariant::query()->create([
                            'product_id' => $product->id,
                            'sku' => $sku.'-V'.($vIndex + 1),
                            'size' => $variant['size'] ?? null,
                            'size_en' => $variant['size_en'] ?? null,
                            'color' => $variant['color'] ?? null,
                            'color_en' => $variant['color_en'] ?? null,
                            'stock_quantity' => $variant['stock'],
                            'reserved_quantity' => 0,
                            'price_override' => $variant['price_override'] ?? null,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    protected function seedBanners(Shop $shop, array $rgb, string $label, StockImageDownloader $images): void
    {
        $banners = $shop->slug === 'flamingo'
            ? [
                [
                    'title' => 'إكسسوارات فلامنجو الفاخرة',
                    'title_en' => 'Flamingo luxury accessories',
                    'subtitle' => 'ساعات وعطور وحقائب بذوق راقٍ',
                    'subtitle_en' => 'Watches, perfume and bags with refined taste',
                    'button_text' => 'تسوق الآن',
                    'button_text_en' => 'Shop now',
                    'button_url' => '/',
                ],
                [
                    'title' => 'عروض مختارة هذا الأسبوع',
                    'title_en' => 'Selected offers this week',
                    'subtitle' => 'خصومات على نظارات الشمس والمحافظ',
                    'subtitle_en' => 'Discounts on sunglasses and wallets',
                    'button_text' => 'اكتشف العروض',
                    'button_text_en' => 'Discover offers',
                    'button_url' => '/',
                ],
            ]
            : [
                [
                    'title' => 'ستايل بابلز العصري',
                    'title_en' => 'Bubbles modern style',
                    'subtitle' => 'إكسسوارات أنيقة بأسعار متوسطة',
                    'subtitle_en' => 'Elegant accessories at mid-range prices',
                    'button_text' => 'تصفح المجموعة',
                    'button_text_en' => 'Browse collection',
                    'button_url' => '/',
                ],
                [
                    'title' => 'هدايا جاهزة للإهداء',
                    'title_en' => 'Gifts ready to give',
                    'subtitle' => 'أساور وأحزمة وعطور بلمسة وردية',
                    'subtitle_en' => 'Bracelets, belts and perfume with a rose touch',
                    'button_text' => 'اختر هديتك',
                    'button_text_en' => 'Choose your gift',
                    'button_url' => '/',
                ],
            ];

        foreach ($banners as $index => $banner) {
            // Prefer brand hero for first banner so homepage stays on-brand.
            if ($index === 0 && $shop->hero_image) {
                $path = ltrim((string) $shop->hero_image, '/');
                // Store as public path reference for Banner model
                $bannerImage = str_starts_with((string) $shop->hero_image, '/')
                    ? $shop->hero_image
                    : '/'.$path;
            } else {
                $path = $images->download(
                    'banners',
                    $shop->slug.'-banner-'.$index,
                    $images->urlFor('banners', $index + ($shop->slug === 'bubbles' ? 1 : 0)),
                    'banners',
                    $index + ($shop->slug === 'bubbles' ? 1 : 0)
                ) ?? $this->makeGradientImage(
                    'banners',
                    $shop->slug.'-banner-'.$index,
                    $rgb,
                    $label
                );
                $bannerImage = $path;
            }

            Banner::withoutGlobalScopes()->create([
                'shop_id' => $shop->id,
                'title' => $banner['title'],
                'title_en' => $banner['title_en'],
                'subtitle' => $banner['subtitle'],
                'subtitle_en' => $banner['subtitle_en'],
                'image' => $bannerImage,
                'button_text' => $banner['button_text'],
                'button_text_en' => $banner['button_text_en'],
                'button_url' => $banner['button_url'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function flamingoProducts(): array
    {
        return [
            'ساعات يد' => [
                [
                    'name' => 'ساعة فلامنجو كلاسيك ذهبية',
                    'name_en' => 'Flamingo Classic Gold Watch',
                    'short_description' => 'ساعة يد فاخرة بوجه ذهبي وإطار أنيق.',
                    'short_description_en' => 'A luxury wristwatch with a gold dial and elegant case.',
                    'description' => 'ساعة يد فاخرة من مجموعة فلامنجو، بهيكل معدني مصقول وسوار جلد طبيعي يناسب الإطلالات الرسمية.',
                    'description_en' => 'A luxury Flamingo collection watch with a polished metal case and genuine leather strap, suited to formal looks.',
                    'brand' => 'Flamingo Prestige',
                    'color' => 'ذهبي',
                    'color_en' => 'Gold',
                    'material' => 'ستانلس ستيل وجلد',
                    'material_en' => 'Stainless steel and leather',
                    'size' => '42 مم',
                    'size_en' => '42 mm',
                    'price' => 2890,
                    'sale_price' => 2490,
                    'stock' => 8,
                ],
                [
                    'name' => 'ساعة أوركيد بنفسجية',
                    'name_en' => 'Orchid Purple Watch',
                    'short_description' => 'تصميم بنفسجي راقٍ بعقارب دقيقة.',
                    'short_description_en' => 'An elegant purple design with precise hands.',
                    'description' => 'ساعة أنيقة مستوحاة من ألوان فلامنجو البنفسجية، مثالية كهدية فاخرة للمناسبات الخاصة.',
                    'description_en' => 'An elegant watch inspired by Flamingo violet tones — an ideal luxury gift for special occasions.',
                    'brand' => 'Orchid Time',
                    'color' => 'بنفسجي',
                    'color_en' => 'Purple',
                    'material' => 'ستانلس ستيل',
                    'material_en' => 'Stainless steel',
                    'size' => '40 مم',
                    'size_en' => '40 mm',
                    'price' => 2190,
                    'sale_price' => null,
                    'stock' => 0,
                    'variants' => [
                        ['size' => '40 مم', 'size_en' => '40 mm', 'color' => 'بنفسجي', 'color_en' => 'Purple', 'stock' => 5],
                        ['size' => '42 مم', 'size_en' => '42 mm', 'color' => 'أسود', 'color_en' => 'Black', 'stock' => 4],
                    ],
                ],
                [
                    'name' => 'ساعة منتصف الليل الفاخرة',
                    'name_en' => 'Midnight Luxury Watch',
                    'short_description' => 'وجه أسود لامع بسوار معدني.',
                    'short_description_en' => 'A glossy black dial with a metal bracelet.',
                    'description' => 'ساعة مسائية فاخرة بتفاصيل دقيقة ولمسة مطلية مقاومة للخدش، من تشكيلة فلامنجو الراقية.',
                    'description_en' => 'A luxurious evening watch with fine details and a scratch-resistant plated finish, from the refined Flamingo line.',
                    'brand' => 'Midnight Atelier',
                    'color' => 'أسود',
                    'color_en' => 'Black',
                    'material' => 'ستانلس ستيل',
                    'material_en' => 'Stainless steel',
                    'size' => '41 مم',
                    'size_en' => '41 mm',
                    'price' => 3250,
                    'sale_price' => 2990,
                    'stock' => 6,
                ],
            ],
            'محافظ' => [
                [
                    'name' => 'محفظة جلد إيطالي فاخرة',
                    'name_en' => 'Luxury Italian Leather Wallet',
                    'short_description' => 'محفظة ثنائية الطي بجلد إيطالي ناعم.',
                    'short_description_en' => 'A bifold wallet in soft Italian leather.',
                    'description' => 'محفظة يدوية التشطيب من جلد إيطالي فاخر، بمقصورات متعددة للبطاقات والنقود.',
                    'description_en' => 'A hand-finished wallet in fine Italian leather, with multiple compartments for cards and cash.',
                    'brand' => 'Milano Hide',
                    'color' => 'بني داكن',
                    'color_en' => 'Dark brown',
                    'material' => 'جلد طبيعي',
                    'material_en' => 'Genuine leather',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 890,
                    'sale_price' => 749,
                    'stock' => 15,
                ],
                [
                    'name' => 'محفظة فلامنجو سليم',
                    'name_en' => 'Flamingo Slim Wallet',
                    'short_description' => 'تصميم نحيف أنيق للجيب الأمامي.',
                    'short_description_en' => 'A sleek slim design for the front pocket.',
                    'description' => 'محفظة نحيفة بتشطيب بنفسجي داكن، مثالية للإهداء اليومي مع الحفاظ على المظهر الفاخر.',
                    'description_en' => 'A slim wallet with a dark purple finish — ideal for everyday gifting while keeping a luxury look.',
                    'brand' => 'Flamingo Leather',
                    'color' => 'بنفسجي داكن',
                    'color_en' => 'Dark purple',
                    'material' => 'جلد صناعي فاخر',
                    'material_en' => 'Premium faux leather',
                    'size' => 'نحيف',
                    'size_en' => 'Slim',
                    'price' => 620,
                    'sale_price' => null,
                    'stock' => 0,
                    'variants' => [
                        ['size' => 'نحيف', 'size_en' => 'Slim', 'color' => 'بنفسجي داكن', 'color_en' => 'Dark purple', 'stock' => 10],
                        ['size' => 'نحيف', 'size_en' => 'Slim', 'color' => 'أسود', 'color_en' => 'Black', 'stock' => 12],
                    ],
                ],
                [
                    'name' => 'محفظة كارد هولدر ذهبية',
                    'name_en' => 'Gold Card Holder Wallet',
                    'short_description' => 'حافظة بطاقات معدنية بلمسة ذهبية.',
                    'short_description_en' => 'A metal card holder with a gold finish.',
                    'description' => 'حافظة بطاقات أنيقة بحماية RFID ولمسة ذهبية مطفية تناسب الهدايا الفاخرة.',
                    'description_en' => 'An elegant card holder with RFID protection and a matte gold finish, suited to luxury gifts.',
                    'brand' => 'Gold Card Atelier',
                    'color' => 'ذهبي مطفي',
                    'color_en' => 'Matte gold',
                    'material' => 'ألومنيوم',
                    'material_en' => 'Aluminum',
                    'size' => 'صغير',
                    'size_en' => 'Small',
                    'price' => 480,
                    'sale_price' => null,
                    'stock' => 20,
                ],
            ],
            'عطور' => [
                [
                    'name' => 'عطر أوركيد نايت',
                    'name_en' => 'Orchid Night Perfume',
                    'short_description' => 'عطر شرقي فاخر بقاعدة مسكية.',
                    'short_description_en' => 'A luxurious oriental scent with a musk base.',
                    'description' => 'عطر فاخر برائحة الأوركيد والمسك، بثبات عالٍ يناسب السهرات والمناسبات الخاصة.',
                    'description_en' => 'A luxury fragrance of orchid and musk with lasting sillage — perfect for evenings and special occasions.',
                    'brand' => 'Orchid Night',
                    'color' => 'شفاف بنفسجي',
                    'color_en' => 'Transparent purple',
                    'material' => 'زجاج كريستالي',
                    'material_en' => 'Crystal glass',
                    'size' => '100 مل',
                    'size_en' => '100 ml',
                    'price' => 1850,
                    'sale_price' => 1590,
                    'stock' => 10,
                ],
                [
                    'name' => 'عطر بلوم فيلفيت',
                    'name_en' => 'Bloom Velvet Perfume',
                    'short_description' => 'نفحات زهرية مخملية ناعمة.',
                    'short_description_en' => 'Soft velvet floral notes.',
                    'description' => 'عطر زهري مخملي من مجموعة فلامنجو، بتركيبة متوازنة تناسب الاستخدام اليومي الراقي.',
                    'description_en' => 'A velvet floral scent from the Flamingo collection, with a balanced formula for refined everyday wear.',
                    'brand' => 'Bloom Velvet',
                    'color' => 'وردي فاتح',
                    'color_en' => 'Light pink',
                    'material' => 'زجاج',
                    'material_en' => 'Glass',
                    'size' => '75 مل',
                    'size_en' => '75 ml',
                    'price' => 1420,
                    'sale_price' => null,
                    'stock' => 14,
                ],
                [
                    'name' => 'مجموعة عطور فلامنجو المصغرة',
                    'name_en' => 'Flamingo Mini Perfume Set',
                    'short_description' => 'طقم ثلاثي فاخر بأحجام سفر.',
                    'short_description_en' => 'A luxury trio set in travel sizes.',
                    'description' => 'مجموعة من ثلاثة عطور مصغرة بتغليف بنفسجي أنيق، مثالية كهدية فاخرة أو للسفر.',
                    'description_en' => 'A set of three mini fragrances in elegant purple packaging — ideal as a luxury gift or for travel.',
                    'brand' => 'Flamingo Parfum',
                    'color' => 'متعدد',
                    'color_en' => 'Multi',
                    'material' => 'زجاج',
                    'material_en' => 'Glass',
                    'size' => '3×15 مل',
                    'size_en' => '3×15 ml',
                    'price' => 980,
                    'sale_price' => 850,
                    'stock' => 12,
                ],
            ],
            'نظارات شمس' => [
                [
                    'name' => 'نظارة أفياتور بنفسجية',
                    'name_en' => 'Purple Aviator Sunglasses',
                    'short_description' => 'إطار معدني بعدسات مستقطبة.',
                    'short_description_en' => 'A metal frame with polarized lenses.',
                    'description' => 'نظارة شمس أفياتور فاخرة بعدسات مستقطبة وإطار معدني بلمسة بنفسجية مطفية.',
                    'description_en' => 'Luxury aviator sunglasses with polarized lenses and a metal frame in a matte purple finish.',
                    'brand' => 'Violet Aviator',
                    'color' => 'بنفسجي مطفي',
                    'color_en' => 'Matte purple',
                    'material' => 'معدن',
                    'material_en' => 'Metal',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 1290,
                    'sale_price' => 1090,
                    'stock' => 9,
                ],
                [
                    'name' => 'نظارة كات آي فلامنجو',
                    'name_en' => 'Flamingo Cat-Eye Sunglasses',
                    'short_description' => 'تصميم عين القطة بإطار أسيتات فاخر.',
                    'short_description_en' => 'A cat-eye design with a luxury acetate frame.',
                    'description' => 'نظارة شمس بتصميم عين القطة الكلاسيكي، بإطار أسيتات فاخر وعدسات حماية UV400.',
                    'description_en' => 'Classic cat-eye sunglasses with a luxury acetate frame and UV400 protective lenses.',
                    'brand' => 'Flamingo Eyewear',
                    'color' => 'أسود لامع',
                    'color_en' => 'Glossy black',
                    'material' => 'أسيتات',
                    'material_en' => 'Acetate',
                    'size' => 'متوسط',
                    'size_en' => 'Medium',
                    'price' => 1150,
                    'sale_price' => null,
                    'stock' => 0,
                    'variants' => [
                        ['size' => 'متوسط', 'size_en' => 'Medium', 'color' => 'أسود لامع', 'color_en' => 'Glossy black', 'stock' => 7],
                        ['size' => 'كبير', 'size_en' => 'Large', 'color' => 'بني سلحفاة', 'color_en' => 'Tortoise brown', 'stock' => 5],
                    ],
                ],
            ],
            'حقائب' => [
                [
                    'name' => 'حقيبة كروس فلامنجو جلد',
                    'name_en' => 'Flamingo Leather Crossbody Bag',
                    'short_description' => 'حقيبة كروس أنيقة بجلد ناعم.',
                    'short_description_en' => 'An elegant crossbody bag in soft leather.',
                    'description' => 'حقيبة كروس فاخرة بجلد ناعم وسلسلة معدنية ذهبية، مثالية للإطلالات المسائية.',
                    'description_en' => 'A luxury crossbody in soft leather with a gold metal chain — perfect for evening looks.',
                    'brand' => 'Flamingo Atelier',
                    'color' => 'بنفسجي',
                    'color_en' => 'Purple',
                    'material' => 'جلد طبيعي',
                    'material_en' => 'Genuine leather',
                    'size' => 'صغير',
                    'size_en' => 'Small',
                    'price' => 2450,
                    'sale_price' => 2190,
                    'stock' => 7,
                ],
                [
                    'name' => 'حقيبة تووت كرافت ذهبية',
                    'name_en' => 'Craft Gold Tote Bag',
                    'short_description' => 'تووت واسعة بلمسات ذهبية.',
                    'short_description_en' => 'A spacious tote with gold accents.',
                    'description' => 'حقيبة تووت واسعة بتشطيب كرافت وذهبي، تناسب العمل والسفر اليومي بمظهر فاخر.',
                    'description_en' => 'A roomy tote with craft and gold finishes — suited to work and daily travel with a luxury look.',
                    'brand' => 'Craft & Gold',
                    'color' => 'بيج ذهبي',
                    'color_en' => 'Gold beige',
                    'material' => 'قماش فاخر وجلد',
                    'material_en' => 'Premium fabric and leather',
                    'size' => 'كبير',
                    'size_en' => 'Large',
                    'price' => 1890,
                    'sale_price' => null,
                    'stock' => 11,
                ],
                [
                    'name' => 'حقيبة ظهر سفر فلامنجو',
                    'name_en' => 'Flamingo Travel Backpack',
                    'short_description' => 'حقيبة ظهر عملية بتشطيب فاخر.',
                    'short_description_en' => 'A practical backpack with a refined finish.',
                    'description' => 'حقيبة ظهر للسفر اليومي بمقصورات منظمة ومادة مقاومة للماء بلمسة بنفسجية راقية.',
                    'description_en' => 'A daily travel backpack with organized compartments and water-resistant material in a refined purple tone.',
                    'brand' => 'Flamingo Travel',
                    'color' => 'رمادي بنفسجي',
                    'color_en' => 'Purple gray',
                    'material' => 'نايلون فاخر',
                    'material_en' => 'Premium nylon',
                    'size' => 'متوسط',
                    'size_en' => 'Medium',
                    'price' => 1680,
                    'sale_price' => 1490,
                    'stock' => 0,
                    'variants' => [
                        ['size' => 'متوسط', 'size_en' => 'Medium', 'color' => 'رمادي بنفسجي', 'color_en' => 'Purple gray', 'stock' => 6],
                        ['size' => 'كبير', 'size_en' => 'Large', 'color' => 'أسود', 'color_en' => 'Black', 'stock' => 4],
                    ],
                ],
            ],
            'أساور' => [
                [
                    'name' => 'سوار ذهب مطفي فاخر',
                    'name_en' => 'Luxury Matte Gold Bracelet',
                    'short_description' => 'سوار معدني بتشطيب ذهبي مطفي.',
                    'short_description_en' => 'A metal bracelet with a matte gold finish.',
                    'description' => 'سوار أنيق بتشطيب ذهبي مطفي وسلسلة قابلة للتعديل، من مجموعة فلامنجو للمجوهرات الخفيفة.',
                    'description_en' => 'An elegant bracelet with a matte gold finish and adjustable chain, from Flamingo’s light jewelry collection.',
                    'brand' => 'Gilded Arc',
                    'color' => 'ذهبي مطفي',
                    'color_en' => 'Matte gold',
                    'material' => 'ستانلس ستيل مطلي',
                    'material_en' => 'Plated stainless steel',
                    'size' => 'قابل للتعديل',
                    'size_en' => 'Adjustable',
                    'price' => 720,
                    'sale_price' => 640,
                    'stock' => 18,
                ],
                [
                    'name' => 'سوار حجر الأميثيست',
                    'name_en' => 'Amethyst Stone Bracelet',
                    'short_description' => 'خرز أميثيست طبيعي بخيط حريري.',
                    'short_description_en' => 'Natural amethyst beads on a silk cord.',
                    'description' => 'سوار من أحجار الأميثيست الطبيعية بلمسة بنفسجية مميزة، مثالي كهدية روحية أنيقة.',
                    'description_en' => 'A bracelet of natural amethyst stones with a distinctive purple touch — an elegant spiritual gift.',
                    'brand' => 'Amethyst Lane',
                    'color' => 'بنفسجي',
                    'color_en' => 'Purple',
                    'material' => 'حجر طبيعي',
                    'material_en' => 'Natural stone',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 540,
                    'sale_price' => null,
                    'stock' => 16,
                ],
            ],
            'أحزمة' => [
                [
                    'name' => 'حزام جلد إيطالي مشبك ذهبي',
                    'name_en' => 'Italian Leather Belt with Gold Buckle',
                    'short_description' => 'حزام كلاسيكي بمشبك ذهبي مصقول.',
                    'short_description_en' => 'A classic belt with a polished gold buckle.',
                    'description' => 'حزام جلد إيطالي فاخر بمشبك ذهبي مصقول، مناسب للإطلالات الرسمية والكاجوال الراقي.',
                    'description_en' => 'A luxury Italian leather belt with a polished gold buckle — suited to formal and refined casual looks.',
                    'brand' => 'Milano Belt',
                    'color' => 'أسود',
                    'color_en' => 'Black',
                    'material' => 'جلد طبيعي',
                    'material_en' => 'Genuine leather',
                    'size' => null,
                    'price' => 980,
                    'sale_price' => 850,
                    'stock' => 0,
                    'variants' => [
                        ['size' => '95 سم', 'size_en' => '95 cm', 'color' => 'أسود', 'color_en' => 'Black', 'stock' => 8],
                        ['size' => '105 سم', 'size_en' => '105 cm', 'color' => 'بني', 'color_en' => 'Brown', 'stock' => 6],
                    ],
                ],
                [
                    'name' => 'حزام فلامنجو بنفسجي',
                    'name_en' => 'Flamingo Purple Belt',
                    'short_description' => 'حزام بلمسة بنفسجية مميزة.',
                    'short_description_en' => 'A belt with a distinctive purple touch.',
                    'description' => 'حزام أنيق بلون بنفسجي داكن ومشبك فضي مطفي، يعكس هوية فلامنجو الفاخرة.',
                    'description_en' => 'An elegant dark purple belt with a matte silver buckle, reflecting Flamingo’s luxury identity.',
                    'brand' => 'Flamingo Leather',
                    'color' => 'بنفسجي داكن',
                    'color_en' => 'Dark purple',
                    'material' => 'جلد صناعي فاخر',
                    'material_en' => 'Premium faux leather',
                    'size' => '100 سم',
                    'size_en' => '100 cm',
                    'price' => 690,
                    'sale_price' => null,
                    'stock' => 13,
                ],
                [
                    'name' => 'حزام ضفيرة كلاسيك',
                    'name_en' => 'Classic Braided Belt',
                    'short_description' => 'تصميم مضفور بتشطيب يدوي.',
                    'short_description_en' => 'A braided design with a hand finish.',
                    'description' => 'حزام مضفور بتشطيب يدوي فاخر، يضيف لمسة كلاسيكية لأي إطلالة رسمية.',
                    'description_en' => 'A braided belt with a luxury hand finish that adds a classic touch to any formal look.',
                    'brand' => 'Braided Prestige',
                    'color' => 'بني كراميل',
                    'color_en' => 'Caramel brown',
                    'material' => 'جلد طبيعي',
                    'material_en' => 'Genuine leather',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 820,
                    'sale_price' => 740,
                    'stock' => 10,
                ],
            ],
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function bubblesProducts(): array
    {
        return [
            'ساعات يد' => [
                [
                    'name' => 'ساعة روز كوارتز بابلز',
                    'name_en' => 'Bubbles Rose Quartz Watch',
                    'short_description' => 'ساعة وردية ناعمة بسوار شبكي.',
                    'short_description_en' => 'A soft pink watch with a mesh bracelet.',
                    'description' => 'ساعة عصرية بلون روز كوارتز وسوار شبكي معدني، من تشكيلة بابلز الأنيقة بأسعار متوسطة.',
                    'description_en' => 'A modern rose-quartz watch with a metal mesh bracelet, from Bubbles’ elegant mid-range collection.',
                    'brand' => 'Bubbles Rose',
                    'color' => 'وردي',
                    'color_en' => 'Pink',
                    'material' => 'ستانلس ستيل',
                    'material_en' => 'Stainless steel',
                    'size' => '38 مم',
                    'size_en' => '38 mm',
                    'price' => 1290,
                    'sale_price' => 1090,
                    'stock' => 14,
                ],
                [
                    'name' => 'ساعة فقاعة الوقت',
                    'name_en' => 'Bubble Time Watch',
                    'short_description' => 'وجه دائري شفاف بلمسة عصرية.',
                    'short_description_en' => 'A transparent round dial with a modern touch.',
                    'description' => 'ساعة بتصميم فقاعي شفاف وإطار فضي لامع، مثالية للإطلالات الكاجوال العصرية.',
                    'description_en' => 'A watch with a transparent bubble design and shiny silver case — ideal for modern casual looks.',
                    'brand' => 'Bubble Time',
                    'color' => 'فضي',
                    'color_en' => 'Silver',
                    'material' => 'معدن وبلاستيك شفاف',
                    'material_en' => 'Metal and clear plastic',
                    'size' => '40 مم',
                    'size_en' => '40 mm',
                    'price' => 980,
                    'sale_price' => null,
                    'stock' => 0,
                    'variants' => [
                        ['size' => '38 مم', 'size_en' => '38 mm', 'color' => 'فضي', 'color_en' => 'Silver', 'stock' => 8],
                        ['size' => '40 مم', 'size_en' => '40 mm', 'color' => 'وردي', 'color_en' => 'Pink', 'stock' => 7],
                    ],
                ],
                [
                    'name' => 'ساعة بينك ميني',
                    'name_en' => 'Pink Mini Watch',
                    'short_description' => 'ساعة صغيرة أنيقة بوجه وردي.',
                    'short_description_en' => 'A small elegant watch with a pink dial.',
                    'description' => 'ساعة ميني بحجم صغير ووجه وردي ناعم، مناسبة للهدايا العصرية بأسعار معقولة.',
                    'description_en' => 'A mini watch with a soft pink dial — great for modern gifts at an accessible price.',
                    'brand' => 'Pink Mini',
                    'color' => 'بيبي بينك',
                    'color_en' => 'Baby pink',
                    'material' => 'ستانلس ستيل',
                    'material_en' => 'Stainless steel',
                    'size' => '32 مم',
                    'size_en' => '32 mm',
                    'price' => 850,
                    'sale_price' => 749,
                    'stock' => 16,
                ],
            ],
            'محافظ' => [
                [
                    'name' => 'محفظة لؤلؤ وردي',
                    'name_en' => 'Pink Pearl Wallet',
                    'short_description' => 'محفظة ناعمة بلون لؤلؤي وردي.',
                    'short_description_en' => 'A soft wallet in pearl pink.',
                    'description' => 'محفظة أنيقة بتشطيب لؤلؤي وردي ومقصورات عملية، من مجموعة بابلز للهدايا اليومية.',
                    'description_en' => 'An elegant wallet with a pearl-pink finish and practical compartments, from Bubbles’ everyday gift line.',
                    'brand' => 'Pearl Pop',
                    'color' => 'لؤلؤ وردي',
                    'color_en' => 'Pink pearl',
                    'material' => 'جلد صناعي',
                    'material_en' => 'Faux leather',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 390,
                    'sale_price' => 329,
                    'stock' => 22,
                ],
                [
                    'name' => 'محفظة فقاعات كارد',
                    'name_en' => 'Bubbles Card Wallet',
                    'short_description' => 'حافظة بطاقات بطبعة فقاعات.',
                    'short_description_en' => 'A card holder with a bubble print.',
                    'description' => 'حافظة بطاقات خفيفة بطبعة فقاعات وردية، مثالية للجيب أو الحقيبة الصغيرة.',
                    'description_en' => 'A light card holder with a pink bubble print — perfect for a pocket or small bag.',
                    'brand' => 'Bubbles Card',
                    'color' => 'وردي فاتح',
                    'color_en' => 'Light pink',
                    'material' => 'جلد صناعي',
                    'material_en' => 'Faux leather',
                    'size' => 'صغير',
                    'size_en' => 'Small',
                    'price' => 220,
                    'sale_price' => null,
                    'stock' => 0,
                    'variants' => [
                        ['size' => 'صغير', 'size_en' => 'Small', 'color' => 'وردي فاتح', 'color_en' => 'Light pink', 'stock' => 15],
                        ['size' => 'صغير', 'size_en' => 'Small', 'color' => 'أبيض لؤلؤي', 'color_en' => 'Pearl white', 'stock' => 12],
                    ],
                ],
                [
                    'name' => 'محفظة سحاب ميني بابلز',
                    'name_en' => 'Bubbles Mini Zip Wallet',
                    'short_description' => 'محفظة بسحاب دائري مدمج.',
                    'short_description_en' => 'A wallet with a built-in round zipper.',
                    'description' => 'محفظة ميني بسحاب دائري عملي، تناسب حمل العملات والبطاقات الأساسية بسهولة.',
                    'description_en' => 'A mini wallet with a practical round zipper, easy for coins and essential cards.',
                    'brand' => 'Zip Bubble',
                    'color' => 'ماجنتا',
                    'color_en' => 'Magenta',
                    'material' => 'قماش وجلد صناعي',
                    'material_en' => 'Fabric and faux leather',
                    'size' => 'ميني',
                    'size_en' => 'Mini',
                    'price' => 280,
                    'sale_price' => null,
                    'stock' => 18,
                ],
            ],
            'عطور' => [
                [
                    'name' => 'عطر بابل مسك',
                    'name_en' => 'Bubble Musk Perfume',
                    'short_description' => 'مسك ناعم بلمسة فاكهية.',
                    'short_description_en' => 'Soft musk with a fruity touch.',
                    'description' => 'عطر مسكي ناعم بنفحات فاكهية خفيفة، ثبات جيد للاستخدام اليومي من متجر بابلز.',
                    'description_en' => 'A soft musk fragrance with light fruity notes and solid everyday longevity, from Bubbles.',
                    'brand' => 'Bubble Musk',
                    'color' => 'وردي شفاف',
                    'color_en' => 'Transparent pink',
                    'material' => 'زجاج',
                    'material_en' => 'Glass',
                    'size' => '100 مل',
                    'size_en' => '100 ml',
                    'price' => 890,
                    'sale_price' => 749,
                    'stock' => 15,
                ],
                [
                    'name' => 'عطر بينك ميست',
                    'name_en' => 'Pink Mist Perfume',
                    'short_description' => 'رذاذ عطري خفيف ومنعش.',
                    'short_description_en' => 'A light, refreshing fragrance mist.',
                    'description' => 'عطر خفيف برائحة الورد والفراولة، مثالي للهدايا السريعة والإطلالات اليومية.',
                    'description_en' => 'A light scent of rose and strawberry — ideal for quick gifts and everyday looks.',
                    'brand' => 'Pink Mist',
                    'color' => 'وردي',
                    'color_en' => 'Pink',
                    'material' => 'زجاج',
                    'material_en' => 'Glass',
                    'size' => '50 مل',
                    'size_en' => '50 ml',
                    'price' => 520,
                    'sale_price' => null,
                    'stock' => 20,
                ],
                [
                    'name' => 'عطر روز فيلفيت بابلز',
                    'name_en' => 'Bubbles Rose Velvet Perfume',
                    'short_description' => 'رائحة ورد مخملية بثبات متوسط.',
                    'short_description_en' => 'A velvet rose scent with medium longevity.',
                    'description' => 'عطر زهري مخملي بتركيبة متوسطة الثبات، بتغليف وردي لامع من هوية بابلز.',
                    'description_en' => 'A velvet floral fragrance with medium longevity, in glossy pink packaging from the Bubbles brand.',
                    'brand' => 'Rose Velvet Bubbles',
                    'color' => 'أحمر وردي',
                    'color_en' => 'Rose red',
                    'material' => 'زجاج',
                    'material_en' => 'Glass',
                    'size' => '75 مل',
                    'size_en' => '75 ml',
                    'price' => 740,
                    'sale_price' => 650,
                    'stock' => 11,
                ],
            ],
            'نظارات شمس' => [
                [
                    'name' => 'نظارة راوند بينك',
                    'name_en' => 'Round Pink Sunglasses',
                    'short_description' => 'إطار دائري بلون وردي شفاف.',
                    'short_description_en' => 'A round frame in transparent pink.',
                    'description' => 'نظارة شمس دائرية عصرية بإطار وردي شفاف وعدسات داكنة خفيفة، ستايل شبابي مميز.',
                    'description_en' => 'Modern round sunglasses with a transparent pink frame and lightly tinted lenses — a distinctive youth style.',
                    'brand' => 'Round Pink',
                    'color' => 'وردي شفاف',
                    'color_en' => 'Transparent pink',
                    'material' => 'أسيتات',
                    'material_en' => 'Acetate',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 480,
                    'sale_price' => 399,
                    'stock' => 17,
                ],
                [
                    'name' => 'نظارة سكوير فقاعة',
                    'name_en' => 'Square Bubble Sunglasses',
                    'short_description' => 'إطار مربع بعدسات مرآة.',
                    'short_description_en' => 'A square frame with mirror lenses.',
                    'description' => 'نظارة شمس مربعة بعدسات مرآة وردية، تصميم جريء يناسب الإطلالات الصيفية.',
                    'description_en' => 'Square sunglasses with pink mirror lenses — a bold design for summer looks.',
                    'brand' => 'Square Bubble',
                    'color' => 'أسود / وردي',
                    'color_en' => 'Black / Pink',
                    'material' => 'بلاستيك مقوى',
                    'material_en' => 'Reinforced plastic',
                    'size' => 'متوسط',
                    'size_en' => 'Medium',
                    'price' => 420,
                    'sale_price' => null,
                    'stock' => 0,
                    'variants' => [
                        ['size' => 'متوسط', 'size_en' => 'Medium', 'color' => 'أسود', 'color_en' => 'Black', 'stock' => 9],
                        ['size' => 'متوسط', 'size_en' => 'Medium', 'color' => 'وردي', 'color_en' => 'Pink', 'stock' => 10],
                    ],
                ],
            ],
            'حقائب' => [
                [
                    'name' => 'حقيبة شولدر لؤلؤية',
                    'name_en' => 'Pearl Shoulder Bag',
                    'short_description' => 'حقيبة كتف بلون لؤلؤي ناعم.',
                    'short_description_en' => 'A shoulder bag in soft pearl tone.',
                    'description' => 'حقيبة كتف متوسطة بلون لؤلؤي وردي وسلسلة فضية، مثالية للخروج اليومي.',
                    'description_en' => 'A medium shoulder bag in pearl pink with a silver chain — perfect for everyday outings.',
                    'brand' => 'Pearl Shoulder',
                    'color' => 'لؤلؤي',
                    'color_en' => 'Pearl',
                    'material' => 'جلد صناعي',
                    'material_en' => 'Faux leather',
                    'size' => 'متوسط',
                    'size_en' => 'Medium',
                    'price' => 790,
                    'sale_price' => 690,
                    'stock' => 13,
                ],
                [
                    'name' => 'حقيبة ميني فقاعة',
                    'name_en' => 'Mini Bubble Bag',
                    'short_description' => 'حقيبة ميني بشكل دائري مرح.',
                    'short_description_en' => 'A playful round mini bag.',
                    'description' => 'حقيبة ميني دائرية بتصميم فقاعي مرح، مثالية كإكسسوار لافت للهدايا الشبابية.',
                    'description_en' => 'A round mini bag with a playful bubble design — an eye-catching accessory for youthful gifts.',
                    'brand' => 'Mini Bubble Bag',
                    'color' => 'ماجنتا',
                    'color_en' => 'Magenta',
                    'material' => 'جلد صناعي',
                    'material_en' => 'Faux leather',
                    'size' => 'ميني',
                    'size_en' => 'Mini',
                    'price' => 560,
                    'sale_price' => null,
                    'stock' => 19,
                ],
                [
                    'name' => 'حقيبة تسوق كانفاس بابلز',
                    'name_en' => 'Bubbles Canvas Shopper Bag',
                    'short_description' => 'تووت كانفاس خفيفة بطبعة بابلز.',
                    'short_description_en' => 'A light canvas tote with a Bubbles print.',
                    'description' => 'حقيبة تسوق من الكانفاس الخفيف بطبعة فقاعات، عملية للاستخدام اليومي والسوق.',
                    'description_en' => 'A light canvas shopper with a bubble print — practical for daily use and market runs.',
                    'brand' => 'Canvas Bubbles',
                    'color' => 'أبيض وردي',
                    'color_en' => 'White pink',
                    'material' => 'كانفاس',
                    'material_en' => 'Canvas',
                    'size' => 'كبير',
                    'size_en' => 'Large',
                    'price' => 340,
                    'sale_price' => 290,
                    'stock' => 0,
                    'variants' => [
                        ['size' => 'كبير', 'size_en' => 'Large', 'color' => 'أبيض وردي', 'color_en' => 'White pink', 'stock' => 14],
                        ['size' => 'كبير', 'size_en' => 'Large', 'color' => 'بيج', 'color_en' => 'Beige', 'stock' => 11],
                    ],
                ],
            ],
            'أساور' => [
                [
                    'name' => 'سوار فقاعات ملونة',
                    'name_en' => 'Colorful Bubble Bracelet',
                    'short_description' => 'خرز فقاعي بألوان ناعمة.',
                    'short_description_en' => 'Bubble beads in soft colors.',
                    'description' => 'سوار مرح من خرز فقاعي ملون، خفيف ومناسب للهدايا الشبابية من بابلز.',
                    'description_en' => 'A playful bracelet of colorful bubble beads — light and great for youthful Bubbles gifts.',
                    'brand' => 'Bubble Beads',
                    'color' => 'متعدد',
                    'color_en' => 'Multi',
                    'material' => 'خرز أكريليك',
                    'material_en' => 'Acrylic beads',
                    'size' => 'قابل للتعديل',
                    'size_en' => 'Adjustable',
                    'price' => 180,
                    'sale_price' => 149,
                    'stock' => 30,
                ],
                [
                    'name' => 'سوار سلسلة روز جولد',
                    'name_en' => 'Rose Gold Chain Bracelet',
                    'short_description' => 'سلسلة رفيعة بلون روز جولد.',
                    'short_description_en' => 'A slim chain in rose gold.',
                    'description' => 'سوار سلسلة رفيعة بتشطيب روز جولد لامع، لمسة أنيقة بسعر متوسط.',
                    'description_en' => 'A slim chain bracelet with a shiny rose-gold finish — an elegant touch at a mid-range price.',
                    'brand' => 'Rose Chain',
                    'color' => 'روز جولد',
                    'color_en' => 'Rose gold',
                    'material' => 'ستانلس ستيل مطلي',
                    'material_en' => 'Plated stainless steel',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 260,
                    'sale_price' => null,
                    'stock' => 24,
                ],
            ],
            'أحزمة' => [
                [
                    'name' => 'حزام جلد وردي رفيع',
                    'name_en' => 'Slim Pink Leather Belt',
                    'short_description' => 'حزام رفيع بلون وردي أنيق.',
                    'short_description_en' => 'A slim belt in elegant pink.',
                    'description' => 'حزام جلد صناعي رفيع بلون وردي ومشبك فضي، يضيف لمسة أنثوية لأي إطلالة.',
                    'description_en' => 'A slim faux-leather belt in pink with a silver buckle — adds a feminine touch to any look.',
                    'brand' => 'Slim Rose Belt',
                    'color' => 'وردي',
                    'color_en' => 'Pink',
                    'material' => 'جلد صناعي',
                    'material_en' => 'Faux leather',
                    'size' => null,
                    'price' => 320,
                    'sale_price' => 279,
                    'stock' => 0,
                    'variants' => [
                        ['size' => '90 سم', 'size_en' => '90 cm', 'color' => 'وردي', 'color_en' => 'Pink', 'stock' => 10],
                        ['size' => '100 سم', 'size_en' => '100 cm', 'color' => 'بيج', 'color_en' => 'Beige', 'stock' => 8],
                    ],
                ],
                [
                    'name' => 'حزام مشبك قلب بابلز',
                    'name_en' => 'Bubbles Heart Buckle Belt',
                    'short_description' => 'مشبك على شكل قلب بلمسة مرحة.',
                    'short_description_en' => 'A playful heart-shaped buckle.',
                    'description' => 'حزام عصري بمشبك قلب وردي، تصميم مرح يناسب الهدايا والإطلالات الكاجوال.',
                    'description_en' => 'A modern belt with a pink heart buckle — a playful design for gifts and casual looks.',
                    'brand' => 'Heart Bubbles',
                    'color' => 'أسود',
                    'color_en' => 'Black',
                    'material' => 'جلد صناعي',
                    'material_en' => 'Faux leather',
                    'size' => 'قياسي',
                    'size_en' => 'Standard',
                    'price' => 290,
                    'sale_price' => null,
                    'stock' => 16,
                ],
                [
                    'name' => 'حزام كانفاس مطبوع',
                    'name_en' => 'Printed Canvas Belt',
                    'short_description' => 'حزام قماشي بطبعة فقاعات.',
                    'short_description_en' => 'A fabric belt with a bubble print.',
                    'description' => 'حزام كانفاس خفيف بطبعة فقاعات بابلز، عملي ومريح للاستخدام اليومي.',
                    'description_en' => 'A light canvas belt with a Bubbles bubble print — practical and comfortable for everyday wear.',
                    'brand' => 'Canvas Pop',
                    'color' => 'متعدد',
                    'color_en' => 'Multi',
                    'material' => 'كانفاس',
                    'material_en' => 'Canvas',
                    'size' => 'قابل للتعديل',
                    'size_en' => 'Adjustable',
                    'price' => 210,
                    'sale_price' => 179,
                    'stock' => 20,
                ],
            ],
        ];
    }

    protected function arabicSlug(string $name): string
    {
        $slug = Str::slug($name);
        if ($slug !== '') {
            return $slug;
        }

        $slug = preg_replace('/\s+/u', '-', trim($name)) ?? '';
        $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug) ?? '';

        return $slug !== '' ? mb_strtolower($slug) : Str::random(8);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    protected function makeGradientImage(string $folder, string $filename, array $rgb, string $label): string
    {
        $path = $folder.'/'.$filename.'.jpg';
        $full = Storage::disk('public')->path($path);

        $w = 800;
        $h = 1000;
        $img = imagecreatetruecolor($w, $h);
        $cream = imagecolorallocate($img, 251, 247, 244);
        $white = imagecolorallocate($img, 255, 255, 255);
        $end = imagecolorallocate($img, min(255, $rgb[0] + 70), min(255, $rgb[1] + 40), min(255, $rgb[2] + 50));

        for ($y = 0; $y < $h; $y++) {
            $ratio = $y / $h;
            $r = (int) ($rgb[0] * (1 - $ratio) + min(255, $rgb[0] + 80) * $ratio);
            $g = (int) ($rgb[1] * (1 - $ratio) + min(255, $rgb[1] + 50) * $ratio);
            $b = (int) ($rgb[2] * (1 - $ratio) + min(255, $rgb[2] + 60) * $ratio);
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $w, $y, $color);
        }

        imagefilledellipse($img, (int) ($w * 0.7), (int) ($h * 0.25), 260, 260, $cream);
        imagefilledellipse($img, (int) ($w * 0.25), (int) ($h * 0.75), 180, 180, $end);
        imagestring($img, 5, (int) ($w / 2 - 20), (int) ($h / 2), $label, $white);
        imagejpeg($img, $full, 88);
        imagedestroy($img);

        return $path;
    }
}
