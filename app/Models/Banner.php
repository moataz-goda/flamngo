<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'shop_id',
    'title',
    'title_en',
    'subtitle',
    'subtitle_en',
    'image',
    'button_text',
    'button_text_en',
    'button_url',
    'sort_order',
    'is_active',
])]
class Banner extends Model
{
    use BelongsToShop;
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return current_shop()?->hero_url;
        }

        if (str_starts_with($this->image, '/') || str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }
}
