<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\DashboardAnalyticsService;
use App\Services\ProductService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReservationService $reservations,
        protected CategoryService $categories,
        protected ProductService $products,
        protected DashboardAnalyticsService $analytics,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $period = (int) $request->integer('period', 30);
        if (! in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $canReports = $user?->isOwner() || $user?->canPermission('reports.view');
        $canProducts = $user?->isOwner() || $user?->canPermission('products.view');

        $analytics = ($canReports || $canProducts)
            ? $this->analytics->forPeriod($period)
            : null;

        return view('admin.dashboard', [
            'stats' => $this->reservations->stats(),
            'recent' => $this->reservations->recent(6),
            'categoriesCount' => $this->categories->all()->count(),
            'productsCount' => \App\Models\Product::query()->count(),
            'lowStock' => $this->products->lowStock(3, 10),
            'stockAlerts' => $this->products->lowStock(3, 10),
            'period' => $period,
            'analytics' => $analytics,
            'canReports' => (bool) $canReports,
            'canProducts' => (bool) $canProducts,
        ]);
    }
}
