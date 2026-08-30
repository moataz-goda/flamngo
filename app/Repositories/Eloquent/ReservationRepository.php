<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ReservationRepositoryInterface;
use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReservationRepository extends BaseRepository implements ReservationRepositoryInterface
{
    public function __construct(Reservation $model)
    {
        parent::__construct($model);
    }

    public function findByReference(string $reference)
    {
        return $this->model->newQuery()
            ->with('items.product.images')
            ->where('reference', $reference)
            ->first();
    }

    public function findByReferenceAndPhone(string $reference, string $phone)
    {
        return $this->model->newQuery()
            ->with('items')
            ->where('reference', $reference)
            ->where('phone', $phone)
            ->first();
    }

    public function filterByStatus(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['items.product.category'])
            ->withCount('items')
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function pendingCount(): int
    {
        return $this->model->newQuery()->where('status', Reservation::STATUS_PENDING)->count();
    }

    public function recent(int $limit = 5): Collection
    {
        return $this->model->newQuery()->withCount('items')->latest()->take($limit)->get();
    }

    public function stats(): array
    {
        return [
            'total' => $this->model->newQuery()->count(),
            'pending' => $this->model->newQuery()->where('status', Reservation::STATUS_PENDING)->count(),
            'accepted' => $this->model->newQuery()->where('status', Reservation::STATUS_ACCEPTED)->count(),
            'rejected' => $this->model->newQuery()->where('status', Reservation::STATUS_REJECTED)->count(),
        ];
    }
}
