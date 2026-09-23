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
    'governorate_id',
    'shipping_cost',
    'address',
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
            'shipping_cost' => 'decimal:2',
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

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function subtotal(): float
    {
        return (float) $this->items->sum('line_total');
    }

    public function discountAmount(): float
    {
        $subtotal = $this->subtotal();
        $value = (float) ($this->discount_value ?? 0);

        $amount = match ($this->discount_type) {
            'percentage' => $subtotal * $value / 100,
            'fixed' => $value,
            default => 0.0,
        };

        return min(max(0, $amount), $subtotal);
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
