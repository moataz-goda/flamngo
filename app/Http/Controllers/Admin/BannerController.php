<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ApprovalRequestService;
use App\Services\AuthorizationService;
use App\Services\BannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(
        protected BannerService $banners,
        protected AuthorizationService $authorizer,
        protected ApprovalRequestService $approvals,
    ) {
    }

    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => $this->banners->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', ['banner' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $imagePath = $this->approvals->stashUploads($request->file('image'));

        return $this->authorizer->runOrQueue(
            $request->user(),
            'banners.manage',
            'banner.create',
            ['data' => $data, 'image_path' => $imagePath],
            fn () => $this->banners->create($data, $request->file('image')),
            Banner::class,
            null,
            __('Banner created.'),
            route('admin.banners.index'),
        );
    }

    public function edit(int $banner): View
    {
        return view('admin.banners.form', [
            'banner' => $this->banners->find($banner),
        ]);
    }

    public function update(Request $request, int $banner): RedirectResponse
    {
        $data = $this->validated($request);
        $imagePath = $this->approvals->stashUploads($request->file('image'));

        return $this->authorizer->runOrQueue(
            $request->user(),
            'banners.manage',
            'banner.update',
            ['id' => $banner, 'data' => $data, 'image_path' => $imagePath],
            fn () => $this->banners->update($banner, $data, $request->file('image')),
            Banner::class,
            $banner,
            __('Banner updated.'),
            route('admin.banners.index'),
        );
    }

    public function destroy(int $banner): RedirectResponse
    {
        return $this->authorizer->runOrQueue(
            request()->user(),
            'banners.manage',
            'banner.delete',
            ['id' => $banner],
            fn () => $this->banners->delete($banner),
            Banner::class,
            $banner,
            __('Banner deleted.'),
            route('admin.banners.index'),
        );
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'title_en' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_text_en' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        unset($data['image']);

        return $data;
    }
}
