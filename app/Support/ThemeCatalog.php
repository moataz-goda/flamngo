<?php

namespace App\Support;

class ThemeCatalog
{
    /**
     * @return list<array{code: string, name: string, name_en: string, description: string, description_en: string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'code' => 'flamingo',
                'name' => 'فلامنجو',
                'name_en' => 'Flamingo',
                'description' => 'مظهر بنفسجي فاخر مناسب لمتجر الهدايا الكلاسيكي.',
                'description_en' => 'A rich purple look for a classic gift boutique.',
            ],
            [
                'code' => 'bubbles',
                'name' => 'بابلز',
                'name_en' => 'Bubbles',
                'description' => 'مظهر وردي زجاجي خفيف بأجواء مرحة وعصرية.',
                'description_en' => 'A soft pink glass look with a playful modern feel.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_column(self::definitions(), 'code');
    }

    public static function isValid(string $code): bool
    {
        return in_array($code, self::codes(), true);
    }

    /**
     * @return list<array{code: string, name: string, description: string}>
     */
    public static function forLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $useEn = str_starts_with(strtolower((string) $locale), 'en');

        return array_map(function (array $theme) use ($useEn) {
            return [
                'code' => $theme['code'],
                'name' => $useEn ? $theme['name_en'] : $theme['name'],
                'description' => $useEn ? $theme['description_en'] : $theme['description'],
            ];
        }, self::definitions());
    }

    public static function label(string $code): string
    {
        foreach (self::forLocale() as $theme) {
            if ($theme['code'] === $code) {
                return $theme['name'];
            }
        }

        return $code;
    }
}
