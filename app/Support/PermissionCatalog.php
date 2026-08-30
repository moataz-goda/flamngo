<?php

namespace App\Support;

class PermissionCatalog
{
    public const MODE_AUTO = 'auto';

    public const MODE_APPROVAL = 'approval';

    /**
     * @return list<array{code: string, group: string, name: string, name_en: string, supports_approval: bool, sort_order: int}>
     */
    public static function definitions(): array
    {
        return [
            ['code' => 'categories.view', 'group' => 'categories', 'name' => 'عرض التصنيفات', 'name_en' => 'View categories', 'supports_approval' => false, 'sort_order' => 10],
            ['code' => 'categories.manage', 'group' => 'categories', 'name' => 'إدارة التصنيفات', 'name_en' => 'Manage categories', 'supports_approval' => true, 'sort_order' => 11],
            ['code' => 'products.view', 'group' => 'products', 'name' => 'عرض المنتجات', 'name_en' => 'View products', 'supports_approval' => false, 'sort_order' => 20],
            ['code' => 'products.manage', 'group' => 'products', 'name' => 'إدارة المنتجات', 'name_en' => 'Manage products', 'supports_approval' => true, 'sort_order' => 21],
            ['code' => 'banners.view', 'group' => 'banners', 'name' => 'عرض البانرات', 'name_en' => 'View banners', 'supports_approval' => false, 'sort_order' => 30],
            ['code' => 'banners.manage', 'group' => 'banners', 'name' => 'إدارة البانرات', 'name_en' => 'Manage banners', 'supports_approval' => true, 'sort_order' => 31],
            ['code' => 'reservations.view', 'group' => 'reservations', 'name' => 'عرض الحجوزات', 'name_en' => 'View reservations', 'supports_approval' => false, 'sort_order' => 40],
            ['code' => 'reservations.decide', 'group' => 'reservations', 'name' => 'قبول/رفض الحجوزات', 'name_en' => 'Decide reservations', 'supports_approval' => true, 'sort_order' => 41],
            ['code' => 'reports.view', 'group' => 'reports', 'name' => 'عرض التقارير', 'name_en' => 'View reports', 'supports_approval' => false, 'sort_order' => 50],
            ['code' => 'activity.view', 'group' => 'activity', 'name' => 'عرض سجل النشاط', 'name_en' => 'View activity log', 'supports_approval' => false, 'sort_order' => 60],
        ];
    }

    /**
     * Having manage/decide also grants the matching view permission.
     *
     * @return list<string>
     */
    public static function aliasesFor(string $code): array
    {
        return match ($code) {
            'categories.view' => ['categories.view', 'categories.manage'],
            'products.view' => ['products.view', 'products.manage'],
            'banners.view' => ['banners.view', 'banners.manage'],
            'reservations.view' => ['reservations.view', 'reservations.decide'],
            default => [$code],
        };
    }

    public static function labelFor(string $code): string
    {
        foreach (self::definitions() as $definition) {
            if ($definition['code'] !== $code) {
                continue;
            }

            return app()->getLocale() === 'en'
                ? $definition['name_en']
                : $definition['name'];
        }

        return $code;
    }
}
