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
    'shipping_cost',
    'sort_order',
])]
class Governorate extends Model
{
    use BelongsToShop;
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
