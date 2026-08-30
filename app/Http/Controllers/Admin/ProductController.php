<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Services\ApprovalRequestService;
use App\Services\AuthorizationService;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $products,
        protected CategoryService $categories,
        protected AuthorizationService $authorizer,
        protected ApprovalRequestService $approvals,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.products.index', [
            'products' => $this->products->adminPaginate($request->only(['category_id', 'is_active', 'q'])),
            'categories' => $this->categories->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => null,
            'categories' => $this->categories->all(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->productPayload();
        $variants = $request->variantsPayload();
        $imagePaths = $this->approvals->stashUploads($request->uploadedImages()) ?? [];

        return $this->authorizer->runOrQueue(
            $request->user(),
            'products.manage',
            'product.create',
            ['data' => $data, 'variants' => $variants, 'image_paths' => $imagePaths],
            fn () => $this->products->create($data, $request->uploadedImages(), $variants),
            Product::class,
            null,
            __('Product created.'),
            route('admin.products.index'),
        );
    }

    public function edit(int $product): View
    {
        return view('admin.products.form', [
            'product' => $this->products->find($product),
            'categories' => $this->categories->all(),
        ]);
    }

    public function update(ProductRequest $request, int $product): RedirectResponse
    {
        $data = $request->productPayload();
        $variants = $request->variantsPayload();
        $imagePaths = $this->approvals->stashUploads($request->uploadedImages()) ?? [];

        return $this->authorizer->runOrQueue(
            $request->user(),
            'products.manage',
            'product.update',
            ['id' => $product, 'data' => $data, 'variants' => $variants, 'image_paths' => $imagePaths],
            fn () => $this->products->update($product, $data, $request->uploadedImages(), $variants),
            Product::class,
            $product,
            __('Product updated.'),
            route('admin.products.index'),
        );
    }

    public function destroy(int $product): RedirectResponse
    {
        return $this->authorizer->runOrQueue(
            request()->user(),
            'products.manage',
            'product.delete',
            ['id' => $product],
            fn () => $this->products->delete($product),
            Product::class,
            $product,
            __('Product deleted.'),
            route('admin.products.index'),
        );
    }

    public function destroyImage(int $image): RedirectResponse
    {
        return $this->authorizer->runOrQueue(
            request()->user(),
            'products.manage',
            'product.delete_image',
            ['image_id' => $image],
            fn () => $this->products->deleteImage($image),
            null,
            null,
            __('Image deleted.'),
        );
    }

    public function importForm(): View
    {
        return view('admin.products.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        $user = $request->user();
        $excelPath = $this->authorizer->needsApproval($user, 'products.manage')
            ? $this->approvals->stashUploads($request->file('excel'))
            : null;

        return $this->authorizer->runOrQueue(
            $user,
            'products.manage',
            'product.import',
            ['excel_path' => $excelPath],
            function () use ($request) {
                $result = $this->products->importExcel($request->file('excel')->getRealPath());

                if ($result['errors']) {
                    session()->flash('error', implode("\n", array_slice($result['errors'], 0, 5)));
                }
            },
            null,
            null,
            __('Excel import completed.'),
            route('admin.products.index'),
        );
    }

    public function sampleExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $headers = ['name', 'category_id', 'price', 'sale_price', 'stock_quantity', 'sku', 'brand', 'color', 'material', 'size', 'short_description', 'description', 'is_featured', 'is_active'];
        $rows = [[
            'Sample Watch', 1, 999, 899, 10, 'SKU-001', 'Casio', 'Black', 'Steel', '42mm', 'Nice watch', 'Full description', 0, 1,
        ]];

        $path = (new \App\Support\SimpleXlsxWriter)->writeTemp($headers, $rows, __('Products'));

        return response()->download($path, 'products-sample.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
