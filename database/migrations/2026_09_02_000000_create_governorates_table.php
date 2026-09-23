<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(75);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['shop_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
