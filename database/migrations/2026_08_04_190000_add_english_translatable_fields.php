<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('tagline_en')->nullable()->after('tagline');
            $table->string('address_en')->nullable()->after('address');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('short_description_en')->nullable()->after('short_description');
            $table->text('description_en')->nullable()->after('description');
            $table->string('color_en')->nullable()->after('color');
            $table->string('material_en')->nullable()->after('material');
            $table->string('size_en')->nullable()->after('size');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('subtitle_en')->nullable()->after('subtitle');
            $table->string('button_text_en')->nullable()->after('button_text');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('size_en')->nullable()->after('size');
            $table->string('color_en')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['size_en', 'color_en']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'subtitle_en', 'button_text_en']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'short_description_en', 'description_en', 'color_en', 'material_en', 'size_en']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'description_en']);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['tagline_en', 'address_en']);
        });
    }
};
