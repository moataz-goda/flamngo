<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class WishlistService
{
    protected function sessionKey(): string
    {
        return 'wishlist_'.(current_shop()?->id ?? 0);
    }

    public function ids(): array
    {
        return array_values(array_unique(array_map('intval', Session::get($this->sessionKey(), []))));
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function toggle(int $productId): bool
    {
        $ids = $this->ids();

        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_filter($ids, fn ($id) => $id !== $productId));
            Session::put($this->sessionKey(), $ids);

            return false;
        }

        $ids[] = $productId;
        Session::put($this->sessionKey(), $ids);

        return true;
    }

    public function remove(int $productId): void
    {
        Session::put(
            $this->sessionKey(),
            array_values(array_filter($this->ids(), fn ($id) => $id !== $productId))
        );
    }

    public function products(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        return \App\Models\Product::query()
            ->with(['images', 'category'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids, true))
            ->values();
    }
}
