<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyApprovalRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'all';

        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'all';
        }

        $query = ApprovalRequest::query()
            ->where('requester_id', $request->user()->id)
            ->with(['reviewer'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $pendingCount = ApprovalRequest::query()
            ->where('requester_id', $request->user()->id)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->count();

        return view('admin.my-requests.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function show(Request $request, int $approval): View
    {
        $approval = ApprovalRequest::query()
            ->where('requester_id', $request->user()->id)
            ->with(['requester', 'reviewer', 'subject'])
            ->findOrFail($approval);

        return view('admin.my-requests.show', [
            'approval' => $approval,
        ]);
    }
}
