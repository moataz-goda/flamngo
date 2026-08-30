<?php

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'code',
    'group',
    'name',
    'name_en',
    'supports_approval',
    'sort_order',
])]
class Permission extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return [
            'supports_approval' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('mode')
            ->withTimestamps();
    }
}
