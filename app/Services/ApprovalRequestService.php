<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApprovalRequestService
{
    public function __construct(
        protected ProductService $products,
        protected CategoryService $categories,
        protected BannerService $banners,
        protected ReservationService $reservations,
        protected ActivityLogService $activity,
    ) {
    }

    public function pendingCount(): int
    {
        return ApprovalRequest::query()
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->count();
    }

    public function pendingCountFor(User $user): int
    {
        return ApprovalRequest::query()
            ->where('requester_id', $user->id)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->count();
    }

    public function paginate(int $perPage = 20)
    {
        return ApprovalRequest::query()
            ->with(['requester', 'reviewer'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function queue(
        User $requester,
        string $permissionCode,
        string $action,
        array $payload,
        ?string $subjectType = null,
        ?int $subjectId = null,
    ): ApprovalRequest {
        return ApprovalRequest::query()->create([
            'shop_id' => current_shop()?->id ?? $requester->shop_id,
            'requester_id' => $requester->id,
            'permission_code' => $permissionCode,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload' => $payload,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);
    }

    public function approve(ApprovalRequest $request, User $reviewer, ?string $note = null): ApprovalRequest
    {
        if (! $request->isPending()) {
            throw new \RuntimeException(__('This approval request was already reviewed.'));
        }

        $this->replay($request);

        $request->update([
            'status' => ApprovalRequest::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        $this->activity->log(
            'approval.approved',
            $request,
            __('Approved request :action', ['action' => __($request->action)]),
            ['approval_id' => $request->id]
        );

        $this->cleanupTempFiles($request->payload ?? []);

        return $request->fresh(['requester', 'reviewer']);
    }

    public function reject(ApprovalRequest $request, User $reviewer, ?string $note = null): ApprovalRequest
    {
        if (! $request->isPending()) {
            throw new \RuntimeException(__('This approval request was already reviewed.'));
        }

        $request->update([
            'status' => ApprovalRequest::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        $this->activity->log(
            'approval.rejected',
            $request,
            __('Rejected request :action', ['action' => __($request->action)]),
            ['approval_id' => $request->id]
        );

        $this->cleanupTempFiles($request->payload ?? []);

        return $request->fresh(['requester', 'reviewer']);
    }

    /**
     * Store uploaded files under a temp folder for later replay.
     *
     * @param  list<UploadedFile>|UploadedFile|null  $files
     * @return list<string>|string|null
     */
    public function stashUploads(UploadedFile|array|null $files): array|string|null
    {
        if ($files === null) {
            return null;
        }

        if ($files instanceof UploadedFile) {
            return $files->store('approval-temp/'.Str::uuid(), 'public');
        }

        $paths = [];
        $folder = 'approval-temp/'.Str::uuid();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store($folder, 'public');
            }
        }

        return $paths;
    }

    protected function replay(ApprovalRequest $request): void
    {
        $payload = $request->payload ?? [];

        match ($request->action) {
            'category.create' => $this->categories->create(
                $payload['data'] ?? [],
                $this->fileFromPath($payload['image_path'] ?? null)
            ),
            'category.update' => $this->categories->update(
                (int) ($payload['id'] ?? $request->subject_id),
                $payload['data'] ?? [],
                $this->fileFromPath($payload['image_path'] ?? null)
            ),
            'category.delete' => $this->categories->delete((int) ($payload['id'] ?? $request->subject_id)),
            'product.create' => $this->products->create(
                $payload['data'] ?? [],
                $this->filesFromPaths($payload['image_paths'] ?? []),
                $payload['variants'] ?? []
            ),
            'product.update' => $this->products->update(
                (int) ($payload['id'] ?? $request->subject_id),
                $payload['data'] ?? [],
                $this->filesFromPaths($payload['image_paths'] ?? []),
                $payload['variants'] ?? []
            ),
            'product.delete' => $this->products->delete((int) ($payload['id'] ?? $request->subject_id)),
            'product.delete_image' => $this->products->deleteImage((int) ($payload['image_id'] ?? 0)),
            'product.import' => $this->products->importExcel(
                Storage::disk('public')->path($payload['excel_path'] ?? $payload['csv_path'] ?? '')
            ),
            'banner.create' => $this->banners->create(
                $payload['data'] ?? [],
                $this->fileFromPath($payload['image_path'] ?? null)
            ),
            'banner.update' => $this->banners->update(
                (int) ($payload['id'] ?? $request->subject_id),
                $payload['data'] ?? [],
                $this->fileFromPath($payload['image_path'] ?? null)
            ),
            'banner.delete' => $this->banners->delete((int) ($payload['id'] ?? $request->subject_id)),
            'reservation.accept' => $this->reservations->accept(
                (int) ($payload['reservation_id'] ?? $request->subject_id),
                $payload['admin_note'] ?? null
            ),
            'reservation.reject' => $this->reservations->reject(
                (int) ($payload['reservation_id'] ?? $request->subject_id),
                $payload['admin_note'] ?? null
            ),
            'reservation.update_items' => $this->reservations->updateItems(
                (int) ($payload['reservation_id'] ?? $request->subject_id),
                $payload['items'] ?? []
            ),
            default => throw new \RuntimeException(__('Unknown approval action.')),
        };
    }

    protected function fileFromPath(?string $path): ?UploadedFile
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);

        return new UploadedFile($absolute, basename($path), null, null, true);
    }

    /**
     * @param  list<string>  $paths
     * @return list<UploadedFile>
     */
    protected function filesFromPaths(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $file = $this->fileFromPath($path);
            if ($file) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function cleanupTempFiles(array $payload): void
    {
        $paths = [];

        if (! empty($payload['image_path'])) {
            $paths[] = $payload['image_path'];
        }

        if (! empty($payload['excel_path'])) {
            $paths[] = $payload['excel_path'];
        }

        if (! empty($payload['csv_path'])) {
            $paths[] = $payload['csv_path'];
        }

        foreach ($payload['image_paths'] ?? [] as $path) {
            $paths[] = $path;
        }

        foreach (array_filter($paths) as $path) {
            if (is_string($path) && str_starts_with($path, 'approval-temp/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
