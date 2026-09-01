<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id',
    'reference',
    'customer_name',
    'phone',
    'note',
    'status',
    'admin_note',
    'total',
    'discount_type',
    'discount_value',
    'decided_at',
    'decided_by',
])]
class Reservation extends Model
{
    use BelongsToShop;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTED => __('Accepted'),
            self::STATUS_REJECTED => __('Rejected'),
            default => __('Pending'),
        };
    }
}
