<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\AuthorizationService;
use App\Services\ApprovalRequestService;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categories,
        protected AuthorizationService $authorizer,
        protected ApprovalRequestService $approvals,
    ) {
    }

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => $this->categories->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['image'])->all();
        $imagePath = $this->approvals->stashUploads($request->file('image'));

        return $this->authorizer->runOrQueue(
            $request->user(),
            'categories.manage',
            'category.create',
            ['data' => $data, 'image_path' => $imagePath],
            fn () => $this->categories->create($data, $request->file('image')),
            Category::class,
            null,
            __('Category created.'),
            route('admin.categories.index'),
        );
    }

    public function edit(int $category): View
    {
        return view('admin.categories.form', [
            'category' => $this->categories->find($category),
        ]);
    }

    public function update(CategoryRequest $request, int $category): RedirectResponse
    {
        $data = collect($request->validated())->except(['image'])->all();
        $imagePath = $this->approvals->stashUploads($request->file('image'));

        return $this->authorizer->runOrQueue(
            $request->user(),
            'categories.manage',
            'category.update',
            ['id' => $category, 'data' => $data, 'image_path' => $imagePath],
            fn () => $this->categories->update($category, $data, $request->file('image')),
            Category::class,
            $category,
            __('Category updated.'),
            route('admin.categories.index'),
        );
    }

    public function destroy(int $category): RedirectResponse
    {
        return $this->authorizer->runOrQueue(
            request()->user(),
            'categories.manage',
            'category.delete',
            ['id' => $category],
            fn () => $this->categories->delete($category),
            Category::class,
            $category,
            __('Category deleted.'),
            route('admin.categories.index'),
        );
    }
}
