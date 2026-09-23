<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\MyApprovalRequestController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\ReservationExportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ShopSettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\LocaleController;
use App\Http\Controllers\Storefront\PageController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ReservationController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{slug}/quick-view', [ProductController::class, 'quickView'])->name('products.quick-view');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/locale', LocaleController::class)->name('locale.switch');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{lineKey}', [CartController::class, 'update'])->where('lineKey', '.*')->name('cart.update');
Route::delete('/cart/{lineKey}', [CartController::class, 'destroy'])->where('lineKey', '.*')->name('cart.destroy');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

Route::get('/reservation', [ReservationController::class, 'create'])->name('reservation.create');
Route::post('/reservation', [ReservationController::class, 'store'])->name('reservation.store');
Route::get('/reservation/success/{reference}', [ReservationController::class, 'success'])->name('reservation.success');
Route::get('/reservation/track', [ReservationController::class, 'trackForm'])->name('reservation.track');
Route::post('/reservation/track', [ReservationController::class, 'track'])->name('reservation.track.submit');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('permission:categories.view')->group(function () {
            Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        });
        Route::middleware('permission:categories.manage')->group(function () {
            Route::get('categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
            Route::get('categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
            Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        });

        Route::middleware('permission:products.view')->group(function () {
            Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
        });
        Route::middleware('permission:products.manage')->group(function () {
            Route::get('products/create', [AdminProductController::class, 'create'])->name('products.create');
            Route::get('products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
            Route::get('products-import', [AdminProductController::class, 'importForm'])->name('products.import');
            Route::get('products-import/sample', [AdminProductController::class, 'sampleExcel'])->name('products.import.sample');
            Route::post('products', [AdminProductController::class, 'store'])->name('products.store');
            Route::put('products/{product}', [AdminProductController::class, 'update'])->name('products.update');
            Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
            Route::post('products-import', [AdminProductController::class, 'import'])->name('products.import.store');
            Route::delete('product-images/{image}', [AdminProductController::class, 'destroyImage'])->name('product-images.destroy');
        });

        Route::middleware('permission:banners.view')->group(function () {
            Route::get('banners', [AdminBannerController::class, 'index'])->name('banners.index');
        });
        Route::middleware('permission:banners.manage')->group(function () {
            Route::get('banners/create', [AdminBannerController::class, 'create'])->name('banners.create');
            Route::get('banners/{banner}/edit', [AdminBannerController::class, 'edit'])->name('banners.edit');
            Route::post('banners', [AdminBannerController::class, 'store'])->name('banners.store');
            Route::put('banners/{banner}', [AdminBannerController::class, 'update'])->name('banners.update');
            Route::delete('banners/{banner}', [AdminBannerController::class, 'destroy'])->name('banners.destroy');
        });

        Route::middleware('permission:reservations.view')->group(function () {
            Route::get('reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
            Route::get('reservations/export', ReservationExportController::class)->name('reservations.export');
            Route::get('reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
        });
        Route::middleware('permission:reservations.decide')->group(function () {
            Route::put('reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
            Route::post('reservations/{reservation}/discount', [AdminReservationController::class, 'applyDiscount'])->name('reservations.discount');
            Route::post('reservations/{reservation}/accept', [AdminReservationController::class, 'accept'])->name('reservations.accept');
            Route::post('reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');
        });

        Route::middleware('permission:reports.view')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        });

        Route::middleware('permission:activity.view')->group(function () {
            Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
        });

        Route::get('my-requests', [MyApprovalRequestController::class, 'index'])->name('my-requests.index');
        Route::get('my-requests/{approval}', [MyApprovalRequestController::class, 'show'])->name('my-requests.show');

        Route::middleware('owner')->group(function () {
            Route::get('settings', [ShopSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [ShopSettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/colors/restore', [ShopSettingsController::class, 'restoreColors'])->name('settings.colors.restore');
            Route::get('governorates', [GovernorateController::class, 'edit'])->name('governorates.edit');
            Route::post('governorates', [GovernorateController::class, 'store'])->name('governorates.store');
            Route::put('governorates', [GovernorateController::class, 'update'])->name('governorates.update');
            Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
            Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
            Route::get('approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
            Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
            Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        });
    });
});

require __DIR__.'/auth.php';
