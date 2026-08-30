<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $flamingo = Shop::query()->updateOrCreate(
            ['slug' => 'flamingo'],
            [
                'name' => 'Flamingo Gift Shop',
                'domain' => 'flamingo.test',
                'theme' => 'flamingo',
                'default_theme' => 'flamingo',
                'logo' => '/images/brands/flamingo/logo.jpeg',
                'hero_image' => '/images/brands/flamingo/hero.jpeg',
                'tagline' => 'إكسسوارات فاخرة بذوق فلامنجو',
                'tagline_en' => 'Luxury accessories with Flamingo taste',
                'phone' => '01000000000',
                'email' => 'hello@flamingo.shop',
                'address' => 'القاهرة — جمهورية مصر العربية',
                'address_en' => 'Cairo — Arab Republic of Egypt',
                'currency_symbol' => 'ج.م',
                'reference_prefix' => 'FLM',
                'is_active' => true,
            ]
        );

        $bubbles = Shop::query()->updateOrCreate(
            ['slug' => 'bubbles'],
            [
                'name' => 'Bubbles Gift Shop',
                'domain' => 'bubbles.test',
                'theme' => 'bubbles',
                'default_theme' => 'bubbles',
                'logo' => '/images/brands/bubbles/logo.jpeg',
                'hero_image' => '/images/brands/bubbles/hero.jpeg',
                'tagline' => 'إكسسوارات أنيقة تطفو كالفرح',
                'tagline_en' => 'Elegant accessories that float like joy',
                'phone' => '01111111111',
                'email' => 'hello@bubbles.shop',
                'address' => 'القاهرة — جمهورية مصر العربية',
                'address_en' => 'Cairo — Arab Republic of Egypt',
                'currency_symbol' => 'ج.م',
                'reference_prefix' => 'BUB',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@flamingo.shop'],
            [
                'name' => 'مدير فلامنجو',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => User::ROLE_OWNER,
                'shop_id' => $flamingo->id,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@bubbles.shop'],
            [
                'name' => 'مدير بابلز',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => User::ROLE_OWNER,
                'shop_id' => $bubbles->id,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@flamingo.shop'],
            [
                'name' => 'موظف فلامنجو',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => User::ROLE_STAFF,
                'shop_id' => $flamingo->id,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@bubbles.shop'],
            [
                'name' => 'موظف بابلز',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => User::ROLE_STAFF,
                'shop_id' => $bubbles->id,
            ]
        );
    }
}
