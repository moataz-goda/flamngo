<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class RecentlyViewedService
{
    protected function sessionKey(): string
    {
        return 'recently_viewed_'.(current_shop()?->id ?? 0);
    }

    public function push(int $productId, int $limit = 8): void
    {
        $ids = array_values(array_filter(
            Session::get($this->sessionKey(), []),
            fn ($id) => (int) $id !== $productId
        ));

        array_unshift($ids, $productId);
        Session::put($this->sessionKey(), array_slice($ids, 0, $limit));
    }

    public function products(int $excludeId = 0, int $limit = 4): Collection
    {
        $ids = array_values(array_filter(
            array_map('intval', Session::get($this->sessionKey(), [])),
            fn ($id) => $id !== $excludeId
        ));

        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->with(['images', 'category'])
            ->whereIn('id', array_slice($ids, 0, $limit))
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids, true))
            ->values();
    }
}
