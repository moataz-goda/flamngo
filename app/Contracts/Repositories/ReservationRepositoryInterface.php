<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReservationRepositoryInterface extends RepositoryInterface
{
    public function findByReference(string $reference);

    public function findByReferenceAndPhone(string $reference, string $phone);

    public function filterByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator;

    public function pendingCount(): int;

    public function recent(int $limit = 5): Collection;

    public function stats(): array;
}
