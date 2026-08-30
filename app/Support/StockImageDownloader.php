<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StockImageDownloader
{
    /**
     * @var array<string, list<string>>
     */
    private const PHOTOS = [
        'watches' => [
            'photo-1523275335684-37898b6baf30',
            'photo-1524592094714-0f0654e20314',
            'photo-1533139502658-0198f920d8e8',
            'photo-1547996160-81dfa63595aa',
        ],
        'wallets' => [
            'photo-1627123424574-724758594e93',
            'photo-1553062407-98eeb64c6a62',
            'photo-1620799140408-edc6dcb6d633',
            'photo-1627123424574-724758594e93',
        ],
        'perfume' => [
            'photo-1541643600914-78b084683601',
            'photo-1594035910387-fea47794261f',
            'photo-1587017539504-67cfbddac569',
            'photo-1541643600914-78b084683601',
        ],
        'sunglasses' => [
            'photo-1511499767150-a48a237f0083',
            'photo-1572635196237-14b3f281503f',
            'photo-1577803645773-f96470509666',
            'photo-1511499767150-a48a237f0083',
        ],
        'bags' => [
            'photo-1548036328-c9fa89d128fa',
            'photo-1590874103328-eac38a67478e',
            'photo-1553062407-98eeb64c6a62',
            'photo-1548036328-c9fa89d128fa',
        ],
        'bracelets' => [
            'photo-1611591437281-460bfbe1220a',
            'photo-1605100804763-247f67b3557e',
            'photo-1515562141207-7a88fb7ce338',
            'photo-1611591437281-460bfbe1220a',
        ],
        'belts' => [
            'photo-1624222247344-550fb60583fd',
            'photo-1553062407-98eeb64c6a62',
            'photo-1584917865442-de89df76afd3',
            'photo-1624222247344-550fb60583fd',
        ],
        'banners' => [
            'photo-1441986300917-64674bd600d8',
            'photo-1483985988355-763728e1935b',
            'photo-1490481651871-ab68de25d43d',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const ALIASES = [
        'ساعات يد' => 'watches',
        'watches' => 'watches',
        'محافظ' => 'wallets',
        'wallets' => 'wallets',
        'عطور' => 'perfume',
        'perfume' => 'perfume',
        'نظارات شمس' => 'sunglasses',
        'sunglasses' => 'sunglasses',
        'حقائب' => 'bags',
        'bags' => 'bags',
        'أساور' => 'bracelets',
        'bracelets' => 'bracelets',
        'أحزمة' => 'belts',
        'belts' => 'belts',
        'banners' => 'banners',
    ];

    /**
     * Copy bundled stock photo or download remotely into the public disk.
     *
     * @return string|null Relative path like `products/foo.jpg`
     */
    public function download(string $folder, string $filename, string $url, ?string $categoryKey = null, int $index = 0): ?string
    {
        $relative = $folder.'/'.$filename.'.jpg';
        $disk = Storage::disk('public');

        if ($disk->exists($relative) && $disk->size($relative) > 1000) {
            $existing = $disk->path($relative);
            $head = @file_get_contents($existing, false, null, 0, 240) ?: '';
            // Keep real photos; replace GD gradient placeholders.
            if ($head !== '' && ! str_contains($head, 'gd-jpeg')) {
                return $relative;
            }
        }

        $disk->makeDirectory($folder);

        // Prefer local bundled stock images (works offline / for client demos).
        if ($categoryKey !== null) {
            $local = $this->localStockPath($categoryKey, $index);
            if ($local && is_file($local) && filesize($local) > 1000) {
                $disk->put($relative, file_get_contents($local));

                return $relative;
            }
        }

        try {
            $contents = null;

            try {
                $response = Http::timeout(20)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 FlamingoSeeder/1.0'])
                    ->get($url);

                if ($response->successful() && strlen($response->body()) > 1000) {
                    $contents = $response->body();
                }
            } catch (Throwable) {
                // Fall through.
            }

            if ($contents === null) {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 20,
                        'header' => "User-Agent: Mozilla/5.0 FlamingoSeeder/1.0\r\n",
                    ],
                ]);
                $fetched = @file_get_contents($url, false, $context);
                if ($fetched === false || strlen($fetched) <= 1000) {
                    return null;
                }
                $contents = $fetched;
            }

            $disk->put($relative, $contents);

            return $relative;
        } catch (Throwable) {
            return null;
        }
    }

    public function urlFor(string $categoryKey, int $index = 0): string
    {
        $key = self::ALIASES[$categoryKey] ?? $categoryKey;
        $photos = self::PHOTOS[$key] ?? self::PHOTOS['banners'];
        $photoId = $photos[$index % count($photos)];

        return "https://images.unsplash.com/{$photoId}?w=800&h=1000&fit=crop&q=80";
    }

    public function resolveCategoryKey(string $categoryKey): string
    {
        return self::ALIASES[$categoryKey] ?? $categoryKey;
    }

    protected function localStockPath(string $categoryKey, int $index): ?string
    {
        $key = $this->resolveCategoryKey($categoryKey);
        $dir = public_path('images/stock/'.$key);

        if (! is_dir($dir)) {
            return null;
        }

        $files = glob($dir.'/*.jpg') ?: [];
        if ($files === []) {
            return null;
        }

        sort($files);

        return $files[$index % count($files)];
    }
}
