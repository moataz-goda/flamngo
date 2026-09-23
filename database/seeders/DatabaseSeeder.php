<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShopSeeder::class,
            PermissionSeeder::class,
            GovernorateSeeder::class,
            GiftCatalogSeeder::class,
        ]);
    }
}
