<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categories,
        protected ProductService $products,
    ) {
    }

    public function show(string $slug, Request $request): View
    {
        $category = $this->categories->findActiveBySlug($slug);

        if (! $category) {
            abort(404);
        }

        $filters = $request->only(['sort', 'brand', 'color', 'material', 'size']);
        $products = $this->products->paginateByCategory($category->id, $filters);
        $attributeOptions = $this->products->attributeOptions($category->id);

        return view(theme_view('category'), compact('category', 'products', 'attributeOptions'));
    }
}
