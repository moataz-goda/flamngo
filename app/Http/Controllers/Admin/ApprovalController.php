<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(protected ApprovalRequestService $approvals)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'pending';

        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $query = ApprovalRequest::query()->with(['requester', 'reviewer'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.approvals.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'pendingCount' => $this->approvals->pendingCount(),
        ]);
    }

    public function show(int $approval): View
    {
        $approval = ApprovalRequest::query()
            ->with(['requester', 'reviewer', 'subject'])
            ->findOrFail($approval);

        return view('admin.approvals.show', [
            'approval' => $approval,
        ]);
    }

    public function approve(Request $request, int $approval): RedirectResponse
    {
        $approval = ApprovalRequest::query()->findOrFail($approval);
        $note = $request->string('review_note')->toString() ?: null;

        try {
            $this->approvals->approve($approval, $request->user(), $note);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.approvals.index')->with('success', __('Approval request approved.'));
    }

    public function reject(Request $request, int $approval): RedirectResponse
    {
        $approval = ApprovalRequest::query()->findOrFail($approval);
        $note = $request->string('review_note')->toString() ?: null;

        try {
            $this->approvals->reject($approval, $request->user(), $note);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.approvals.index')->with('success', __('Approval request rejected.'));
    }
}
