<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id',
    'category_id',
    'name',
    'name_en',
    'slug',
    'short_description',
    'short_description_en',
    'description',
    'description_en',
    'price',
    'sale_price',
    'stock_quantity',
    'reserved_quantity',
    'sku',
    'brand',
    'color',
    'color_en',
    'material',
    'material_en',
    'size',
    'size_en',
    'is_featured',
    'is_active',
])]
class Product extends Model
{
    use BelongsToShop;
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function hasVariants(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
    }

    public function getAvailableQuantityAttribute(): int
    {
        if ($this->relationLoaded('variants') ? $this->variants->isNotEmpty() : $this->variants()->exists()) {
            $variants = $this->relationLoaded('variants')
                ? $this->variants->where('is_active', true)
                : $this->activeVariants()->get();

            return (int) $variants->sum(fn (ProductVariant $v) => $v->available_quantity);
        }

        return max(0, (int) $this->stock_quantity - (int) $this->reserved_quantity);
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->available_quantity > 0;
    }

    public function getCoverImageAttribute(): ?string
    {
        $image = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $image?->url;
    }
}
