<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ShopScope;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToShop
{
    public static function bootBelongsToShop(): void
    {
        static::addGlobalScope(new ShopScope);

        static::creating(function (Model $model) {
            if (! $model->shop_id && function_exists('current_shop') && current_shop()) {
                $model->shop_id = current_shop()->id;
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
