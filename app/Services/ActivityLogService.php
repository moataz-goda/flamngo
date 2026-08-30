<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogService
{
    public function log(string $action, ?object $subject = null, ?string $description = null, array $properties = [], ?User $user = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => ($user ?? auth()->user())?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject->id ?? null,
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }

    public function recent(int $limit = 20)
    {
        return ActivityLog::query()
            ->with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function paginate(int $perPage = 20)
    {
        return ActivityLog::query()
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }
}
