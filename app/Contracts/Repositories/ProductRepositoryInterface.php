<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findActiveBySlug(string $slug);

    public function paginateByCategory(int $categoryId, array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function latestGifts(int $limit = 8): Collection;

    public function onOffer(int $limit = 8): Collection;

    public function featured(int $limit = 8): Collection;

    public function search(string $term, array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function lockForUpdate(int $id);

    public function adminPaginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
