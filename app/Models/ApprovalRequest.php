<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use App\Support\ApprovalChangeSummary;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'shop_id',
    'requester_id',
    'permission_code',
    'action',
    'subject_type',
    'subject_id',
    'payload',
    'status',
    'reviewed_by',
    'reviewed_at',
    'review_note',
])]
class ApprovalRequest extends Model
{
    use BelongsToShop;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_REJECTED => __('Rejected'),
            default => __('Pending approval'),
        };
    }

    public function getActionLabelAttribute(): string
    {
        return __($this->action);
    }

    public function getPermissionLabelAttribute(): string
    {
        $permission = Permission::query()->where('code', $this->permission_code)->first();

        if ($permission) {
            return (string) $permission->t('name');
        }

        return PermissionCatalog::labelFor((string) $this->permission_code);
    }

    /**
     * @return list<array{label: string, value: string, previous?: string|null}>
     */
    public function getChangeRowsAttribute(): array
    {
        return (new ApprovalChangeSummary)->rows($this);
    }
}
