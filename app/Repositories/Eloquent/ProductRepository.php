<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findActiveBySlug(string $slug)
    {
        return $this->model->newQuery()
            ->with(['images', 'category', 'variants'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function paginateByCategory(int $categoryId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['images', 'variants'])
            ->where('category_id', $categoryId)
            ->where('is_active', true);

        $this->applyAttributeFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? null);

        return $query->paginate($perPage)->withQueryString();
    }

    public function latestGifts(int $limit = 8): Collection
    {
        return $this->model->newQuery()
            ->with(['images', 'category', 'variants'])
            ->where('is_active', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function onOffer(int $limit = 8): Collection
    {
        return $this->model->newQuery()
            ->with(['images', 'category', 'variants'])
            ->where('is_active', true)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->model->newQuery()
            ->with(['images', 'category', 'variants'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function search(string $term, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['images', 'category', 'variants'])
            ->where('is_active', true);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        $this->applyAttributeFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? null);

        return $query->paginate($perPage)->withQueryString();
    }

    public function lockForUpdate(int $id)
    {
        return $this->model->newQuery()->whereKey($id)->lockForUpdate()->first();
    }

    public function adminPaginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['category', 'images', 'variants'])->latest();

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (! empty($filters['q'])) {
            $query->where('name', 'like', '%'.$filters['q'].'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    protected function applyAttributeFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (! empty($filters['material'])) {
            $material = $filters['material'];
            $query->where(function ($q) use ($material) {
                $q->where('material', $material)->orWhere('material_en', $material);
            });
        }

        if (! empty($filters['color'])) {
            $color = $filters['color'];
            $query->where(function ($q) use ($color) {
                $q->where('color', $color)
                    ->orWhere('color_en', $color)
                    ->orWhereHas('variants', fn ($v) => $v->where('is_active', true)
                        ->where(fn ($x) => $x->where('color', $color)->orWhere('color_en', $color)));
            });
        }

        if (! empty($filters['size'])) {
            $size = $filters['size'];
            $query->where(function ($q) use ($size) {
                $q->where('size', $size)
                    ->orWhere('size_en', $size)
                    ->orWhereHas('variants', fn ($v) => $v->where('is_active', true)
                        ->where(fn ($x) => $x->where('size', $size)->orWhere('size_en', $size)));
            });
        }
    }

    protected function applySort(Builder $query, ?string $sort): void
    {
        if (! $sort) {
            $query->latest();

            return;
        }

        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }
}
