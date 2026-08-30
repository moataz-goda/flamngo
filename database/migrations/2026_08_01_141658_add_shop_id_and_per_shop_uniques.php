<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure a Flamingo shop exists for backfill before adding FKs.
        $flamingoId = DB::table('shops')->insertGetId([
            'name' => 'Flamingo Gift Shop',
            'slug' => 'flamingo',
            'domain' => 'flamingo.test',
            'theme' => 'flamingo',
            'logo' => '/images/brands/flamingo/logo.jpeg',
            'hero_image' => '/images/brands/flamingo/hero.jpeg',
            'tagline' => 'أهديك بذوق فلامنجو',
            'phone' => '01000000000',
            'email' => 'hello@flamingo.shop',
            'address' => 'القاهرة — جمهورية مصر العربية',
            'currency_symbol' => 'ج.م',
            'reference_prefix' => 'FLM',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->foreignId('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropUnique(['sku']);
            $table->foreignId('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('categories')->whereNull('shop_id')->update(['shop_id' => $flamingoId]);
        DB::table('products')->whereNull('shop_id')->update(['shop_id' => $flamingoId]);
        DB::table('reservations')->whereNull('shop_id')->update(['shop_id' => $flamingoId]);
        DB::table('users')->whereNull('shop_id')->update(['shop_id' => $flamingoId]);

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable(false)->change();
            $table->unique(['shop_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable(false)->change();
            $table->unique(['shop_id', 'slug']);
            $table->unique(['shop_id', 'sku']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'slug']);
            $table->dropConstrainedForeignId('shop_id');
            $table->unique('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['shop_id', 'slug']);
            $table->dropUnique(['shop_id', 'sku']);
            $table->dropConstrainedForeignId('shop_id');
            $table->unique('slug');
            $table->unique('sku');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });
    }
};
