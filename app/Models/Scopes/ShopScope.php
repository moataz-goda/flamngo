<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ShopScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! function_exists('current_shop') || ! current_shop()) {
            return;
        }

        $builder->where($model->getTable().'.shop_id', current_shop()->id);
    }
}
