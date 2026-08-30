<?php

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'sku',
    'size',
    'size_en',
    'color',
    'color_en',
    'stock_quantity',
    'reserved_quantity',
    'price_override',
    'is_active',
])]
class ProductVariant extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'price_override' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int) $this->stock_quantity - (int) $this->reserved_quantity);
    }

    public function getLabelAttribute(): string
    {
        return collect([$this->t('size'), $this->t('color')])->filter()->implode(' / ') ?: ($this->sku ?: '#'.$this->id);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->available_quantity > 0;
    }

    public function unitPriceFor(Product $product): float
    {
        if ($this->price_override !== null) {
            return (float) $this->price_override;
        }

        return $product->current_price;
    }
}
