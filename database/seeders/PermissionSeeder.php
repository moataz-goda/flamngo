<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Shop;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::definitions() as $definition) {
            Permission::query()->updateOrCreate(
                ['code' => $definition['code']],
                $definition
            );
        }

        foreach (Shop::query()->get() as $shop) {
            Role::withoutGlobalScopes()->firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'name' => 'بدون صلاحيات',
                ],
                [
                    'name_en' => 'No access',
                    'is_system' => true,
                ]
            );
        }
    }
}
