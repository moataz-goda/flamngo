<?php

namespace App\Providers;

use App\Services\ApprovalRequestService;
use App\Services\CartService;
use App\Services\CategoryService;
use App\Services\WishlistService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useTailwind();

        View::composer(['themes.*'], function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('wishlistCount', app(WishlistService::class)->count());
            $view->with('navCategories', app(CategoryService::class)->activeOrdered());
        });

        View::composer('layouts.admin', function ($view) {
            $user = auth()->user();
            if ($user) {
                $user->loadMissing('roles.permissions');
            }

            $approvals = app(ApprovalRequestService::class);

            $view->with(
                'pendingApprovals',
                ($user?->isOwner() ?? false) ? $approvals->pendingCount() : 0
            );
            $view->with(
                'pendingMyRequests',
                $user ? $approvals->pendingCountFor($user) : 0
            );
        });
    }
}
