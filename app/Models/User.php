<?php

namespace App\Models;

use App\Support\PermissionCatalog;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'role', 'shop_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_OWNER = 'owner';

    public const ROLE_STAFF = 'staff';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function assignedRole(): ?Role
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->first();
        }

        return $this->roles()->with('permissions')->first();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isOwner(): bool
    {
        return $this->isAdmin() && $this->role === self::ROLE_OWNER;
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() && $this->role === self::ROLE_STAFF;
    }

    public function belongsToCurrentShop(): bool
    {
        if (! current_shop()) {
            return false;
        }

        return (int) $this->shop_id === (int) current_shop()->id;
    }

    public function canManageShopSettings(): bool
    {
        return $this->isOwner();
    }

    public function canManageStaff(): bool
    {
        return $this->isOwner();
    }

    public function canPermission(string $code): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        if (! $this->isStaff()) {
            return false;
        }

        $aliases = PermissionCatalog::aliasesFor($code);
        $role = $this->assignedRole();

        if (! $role) {
            return false;
        }

        $permissions = $role->relationLoaded('permissions')
            ? $role->permissions
            : $role->permissions()->get();

        return $permissions->contains(fn (Permission $permission) => in_array($permission->code, $aliases, true));
    }

    public function permissionMode(string $code): ?string
    {
        if ($this->isOwner()) {
            return PermissionCatalog::MODE_AUTO;
        }

        if (! $this->isStaff()) {
            return null;
        }

        $role = $this->assignedRole();

        if (! $role) {
            return null;
        }

        $permissions = $role->relationLoaded('permissions')
            ? $role->permissions
            : $role->permissions()->get();

        $permission = $permissions->firstWhere('code', $code);

        if (! $permission) {
            return null;
        }

        $mode = $permission->pivot->mode ?? PermissionCatalog::MODE_AUTO;

        if (! $permission->supports_approval) {
            return PermissionCatalog::MODE_AUTO;
        }

        return $mode === PermissionCatalog::MODE_APPROVAL
            ? PermissionCatalog::MODE_APPROVAL
            : PermissionCatalog::MODE_AUTO;
    }

    public function syncAssignedRole(?int $roleId): void
    {
        if (! $roleId) {
            $this->roles()->detach();

            return;
        }

        $role = Role::query()
            ->where('shop_id', $this->shop_id)
            ->whereKey($roleId)
            ->firstOrFail();

        $this->roles()->sync([$role->id]);
    }
}
