<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id',
    'name',
    'name_en',
    'slug',
    'description',
    'description_en',
    'image',
    'icon',
    'sort_order',
    'is_active',
])]
class Category extends Model
{
    use BelongsToShop;
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http') || str_starts_with($this->image, '/')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
}
