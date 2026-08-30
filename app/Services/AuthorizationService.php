<?php

namespace App\Services;

use App\Models\User;
use App\Support\PermissionCatalog;
use Closure;
use Illuminate\Http\RedirectResponse;

class AuthorizationService
{
    public function __construct(protected ApprovalRequestService $approvals)
    {
    }

    public function needsApproval(User $user, string $permissionCode): bool
    {
        return ! $user->isOwner()
            && $user->permissionMode($permissionCode) === PermissionCatalog::MODE_APPROVAL;
    }

    /**
     * Run action immediately, or queue for owner approval when required.
     *
     * @param  Closure(): mixed  $execute
     * @param  array<string, mixed>  $payload
     */
    public function runOrQueue(
        User $user,
        string $permissionCode,
        string $action,
        array $payload,
        Closure $execute,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $successMessage = null,
        ?string $redirectTo = null,
    ): RedirectResponse {
        if (! $user->canPermission($permissionCode)) {
            abort(403, __('You do not have permission to perform this action.'));
        }

        if ($this->needsApproval($user, $permissionCode)) {
            $this->approvals->queue(
                $user,
                $permissionCode,
                $action,
                $payload,
                $subjectType,
                $subjectId,
            );

            $redirect = $redirectTo
                ? redirect()->to($redirectTo)
                : back();

            return $redirect->with('success', __('Request sent for owner approval.'));
        }

        $execute();

        $redirect = $redirectTo
            ? redirect()->to($redirectTo)
            : back();

        return $redirect->with('success', $successMessage ?? __('Saved successfully.'));
    }
}
