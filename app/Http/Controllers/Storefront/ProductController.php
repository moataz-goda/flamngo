<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\RecentlyViewedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $products,
        protected RecentlyViewedService $recentlyViewed,
    ) {
    }

    public function show(string $slug): View
    {
        $product = $this->products->findActiveBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $this->recentlyViewed->push($product->id);

        return view(theme_view('product'), [
            'product' => $product,
            'related' => $this->products->related($product, 4),
            'recentlyViewed' => $this->recentlyViewed->products($product->id, 4),
        ]);
    }

    public function search(Request $request): View
    {
        $term = $request->string('q')->trim()->toString();
        $filters = $request->only(['brand', 'color', 'material', 'size', 'sort']);

        $products = $this->products->search($term, $filters);
        $attributeOptions = $this->products->attributeOptions();

        return view(theme_view('search'), compact('term', 'products', 'attributeOptions'));
    }

    public function quickView(string $slug): JsonResponse|View
    {
        $product = $this->products->findActiveBySlug($slug);

        if (! $product) {
            abort(404);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => money($product->current_price),
                'image' => $product->cover_image,
                'url' => shop_url('products.show', $product->slug),
                'available' => $product->is_available,
                'variants' => $product->activeVariants->map(fn ($v) => [
                    'id' => $v->id,
                    'label' => $v->label,
                    'available' => $v->available_quantity,
                ]),
            ]);
        }

        return view(theme_view('partials.quick-view'), compact('product'));
    }
}
