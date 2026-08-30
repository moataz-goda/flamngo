<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function activeOrdered(): Collection;

    public function activeWithProductCounts(): Collection;

    public function findActiveBySlug(string $slug);
}
