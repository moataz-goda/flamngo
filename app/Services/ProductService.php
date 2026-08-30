<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(protected ProductRepositoryInterface $products)
    {
    }

    public function findActiveBySlug(string $slug)
    {
        return $this->products->findActiveBySlug($slug);
    }

    public function paginateByCategory(int $categoryId, array $filters = [], int $perPage = 12)
    {
        return $this->products->paginateByCategory($categoryId, $filters, $perPage);
    }

    public function latestGifts(int $limit = 8)
    {
        return $this->products->latestGifts($limit);
    }

    public function onOffer(int $limit = 8)
    {
        return $this->products->onOffer($limit);
    }

    public function featured(int $limit = 8)
    {
        return $this->products->featured($limit);
    }

    public function search(string $term, array $filters = [], int $perPage = 12)
    {
        return $this->products->search($term, $filters, $perPage);
    }

    public function related(Product $product, int $limit = 4): Collection
    {
        return Product::query()
            ->with(['images', 'category', 'variants'])
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function attributeOptions(?int $categoryId = null): array
    {
        $query = Product::query()->where('is_active', true);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get(['brand', 'color', 'color_en', 'material', 'material_en', 'size', 'size_en']);

        $variantQuery = ProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', function ($q) use ($categoryId) {
                $q->where('is_active', true);
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
            });

        $variants = $variantQuery->get(['color', 'color_en', 'size', 'size_en']);
        $en = app()->getLocale() === 'en';

        $colorField = $en ? 'color_en' : 'color';
        $materialField = $en ? 'material_en' : 'material';
        $sizeField = $en ? 'size_en' : 'size';

        $colors = $products->map(fn ($p) => $p->{$colorField} ?: $p->color)
            ->merge($variants->map(fn ($v) => $v->{$colorField} ?: $v->color));
        $materials = $products->map(fn ($p) => $p->{$materialField} ?: $p->material);
        $sizes = $products->map(fn ($p) => $p->{$sizeField} ?: $p->size)
            ->merge($variants->map(fn ($v) => $v->{$sizeField} ?: $v->size));

        return [
            'brands' => $products->pluck('brand')->filter()->unique()->sort()->values()->all(),
            'colors' => $colors->filter()->unique()->sort()->values()->all(),
            'materials' => $materials->filter()->unique()->sort()->values()->all(),
            'sizes' => $sizes->filter()->unique()->sort()->values()->all(),
        ];
    }

    public function lowStock(int $threshold = 3, int $limit = 10)
    {
        $simple = Product::query()
            ->with('variants')
            ->where('is_active', true)
            ->whereDoesntHave('variants')
            ->whereRaw('(stock_quantity - reserved_quantity) <= ?', [$threshold])
            ->get();

        $withVariants = Product::query()
            ->with(['variants' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('variants', function ($q) use ($threshold) {
                $q->where('is_active', true)
                    ->whereRaw('(stock_quantity - reserved_quantity) <= ?', [$threshold]);
            })
            ->get();

        return $simple->merge($withVariants)
            ->unique('id')
            ->sortBy(fn (Product $p) => $p->available_quantity)
            ->take($limit)
            ->values();
    }

    public function adminPaginate(array $filters = [], int $perPage = 15)
    {
        return $this->products->adminPaginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->products->findOrFail($id)->load(['images', 'variants', 'category']);
    }

    public function create(array $data, array $images = [], ?array $variants = null)
    {
        return DB::transaction(function () use ($data, $images, $variants) {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['reserved_quantity'] = 0;
            $product = $this->products->create($data);
            $this->storeImages($product->id, $images);

            if ($variants !== null) {
                $this->syncVariants($product->id, $variants);
            }

            return $product->load(['images', 'variants']);
        });
    }

    public function update(int $id, array $data, array $images = [], ?array $variants = null)
    {
        return DB::transaction(function () use ($id, $data, $images, $variants) {
            $product = $this->products->findOrFail($id);

            if (! empty($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->uniqueSlug($data['name'], $id);
            }

            if (isset($data['stock_quantity'])) {
                $data['stock_quantity'] = max((int) $data['stock_quantity'], (int) $product->reserved_quantity);
            }

            $product = $this->products->update($id, $data);

            if ($images) {
                $this->storeImages($product->id, $images);
            }

            if ($variants !== null) {
                $this->syncVariants($product->id, $variants);
            }

            return $product->load(['images', 'variants']);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $product = $this->products->findOrFail($id);

            foreach ($product->images as $image) {
                if (! str_starts_with($image->path, '/')) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }

            $product->variants()->delete();

            return $this->products->delete($id);
        });
    }

    public function deleteImage(int $imageId): void
    {
        $image = ProductImage::query()->findOrFail($imageId);

        if (! str_starts_with($image->path, '/')) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();
    }

    public function importExcel(string $path): array
    {
        try {
            $rows = (new \App\Support\SimpleXlsxReader)->rows($path);
        } catch (\Throwable $e) {
            throw new \RuntimeException(__('Unable to read Excel file.'));
        }

        if ($rows === []) {
            throw new \RuntimeException(__('Unable to read Excel file.'));
        }

        $header = array_map(
            fn ($h) => Str::of((string) $h)->trim()->lower()->toString(),
            array_shift($rows) ?? []
        );

        $created = 0;
        $updated = 0;
        $errors = [];
        $rowNum = 1;

        foreach ($rows as $row) {
            $rowNum++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach ($header as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $data[$key] = $row[$i] ?? null;
            }

            try {
                $name = trim((string) ($data['name'] ?? ''));
                $categoryId = (int) ($data['category_id'] ?? 0);

                if ($name === '' || $categoryId < 1) {
                    throw new \RuntimeException('name and category_id are required');
                }

                $payload = [
                    'category_id' => $categoryId,
                    'name' => $name,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'] ?? null,
                    'price' => (float) ($data['price'] ?? 0),
                    'sale_price' => ($data['sale_price'] ?? '') !== '' ? (float) $data['sale_price'] : null,
                    'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
                    'sku' => $data['sku'] ?? null,
                    'brand' => $data['brand'] ?? null,
                    'color' => $data['color'] ?? null,
                    'material' => $data['material'] ?? null,
                    'size' => $data['size'] ?? null,
                    'is_featured' => filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];

                $existing = null;
                if (! empty($payload['sku'])) {
                    $existing = Product::query()->where('sku', $payload['sku'])->first();
                }

                if ($existing) {
                    $this->update($existing->id, $payload);
                    $updated++;
                } else {
                    $this->create($payload);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        return compact('created', 'updated', 'errors');
    }

    protected function syncVariants(int $productId, array $variants): void
    {
        $keepIds = [];

        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $size = trim((string) ($variant['size'] ?? ''));
            $color = trim((string) ($variant['color'] ?? ''));
            $sku = trim((string) ($variant['sku'] ?? ''));

            if ($size === '' && $color === '' && $sku === '') {
                continue;
            }

            $payload = [
                'sku' => $sku !== '' ? $sku : null,
                'size' => $size !== '' ? $size : null,
                'size_en' => trim((string) ($variant['size_en'] ?? '')) ?: null,
                'color' => $color !== '' ? $color : null,
                'color_en' => trim((string) ($variant['color_en'] ?? '')) ?: null,
                'stock_quantity' => (int) ($variant['stock_quantity'] ?? 0),
                'price_override' => ($variant['price_override'] ?? '') !== '' ? (float) $variant['price_override'] : null,
                'is_active' => filter_var($variant['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];

            if (! empty($variant['id'])) {
                $model = ProductVariant::query()
                    ->where('product_id', $productId)
                    ->whereKey((int) $variant['id'])
                    ->first();

                if ($model) {
                    $payload['stock_quantity'] = max($payload['stock_quantity'], (int) $model->reserved_quantity);
                    $model->update($payload);
                    $keepIds[] = $model->id;

                    continue;
                }
            }

            $payload['product_id'] = $productId;
            $payload['reserved_quantity'] = 0;
            $created = ProductVariant::query()->create($payload);
            $keepIds[] = $created->id;
        }

        $query = ProductVariant::query()->where('product_id', $productId);

        if ($keepIds !== []) {
            $query->whereNotIn('id', $keepIds);
        }

        $query->delete();
    }

    protected function storeImages(int $productId, array $images): void
    {
        $sort = (int) ProductImage::query()->where('product_id', $productId)->max('sort_order');

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $sort++;
            ProductImage::query()->create([
                'product_id' => $productId,
                'path' => $image->store('products', 'public'),
                'sort_order' => $sort,
            ]);
        }
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = $this->makeSlug($name);
        $slug = $base;
        $i = 1;

        while (
            ($existing = $this->products->findBySlug($slug))
            && ($ignoreId === null || $existing->id !== $ignoreId)
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function makeSlug(string $name): string
    {
        $slug = Str::slug($name);

        if ($slug !== '') {
            return $slug;
        }

        $slug = preg_replace('/\s+/u', '-', trim($name)) ?? '';
        $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug) ?? '';

        return $slug !== '' ? mb_strtolower($slug) : Str::random(8);
    }
}
