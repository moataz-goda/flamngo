<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(protected ActivityLogService $activity)
    {
    }

    public function index(): View
    {
        return view('admin.activity.index', [
            'logs' => $this->activity->paginate(25),
        ]);
    }
}
