<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\BannerService;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected CategoryService $categories,
        protected ProductService $products,
        protected BannerService $banners,
    ) {
    }

    public function __invoke(): View
    {
        return view(theme_view('home'), [
            'categories' => $this->categories->activeWithCounts(),
            'latest' => $this->products->latestGifts(8),
            'offers' => $this->products->onOffer(8),
            'featured' => $this->products->featured(4),
            'banners' => $this->banners->activeOrdered(),
        ]);
    }
}
