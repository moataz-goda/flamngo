<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function activeOrdered(): Collection
    {
        return $this->model->newQuery()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function activeWithProductCounts(): Collection
    {
        return $this->model->newQuery()
            ->where('is_active', true)
            ->withCount(['products as products_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findActiveBySlug(string $slug)
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
