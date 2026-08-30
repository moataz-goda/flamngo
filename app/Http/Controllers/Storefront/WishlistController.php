<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(protected WishlistService $wishlist)
    {
    }

    public function index(): View
    {
        return view(theme_view('wishlist'), [
            'products' => $this->wishlist->products(),
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $added = $this->wishlist->toggle((int) $data['product_id']);

        return back()->with('success', $added ? __('Added to wishlist.') : __('Removed from wishlist.'));
    }
}
