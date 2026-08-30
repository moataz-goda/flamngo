<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    public function activeOrdered()
    {
        return Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function all()
    {
        return Banner::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    public function find(int $id): Banner
    {
        return Banner::query()->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null): Banner
    {
        if ($image) {
            $data['image'] = $image->store('banners', 'public');
        }

        $data['sort_order'] = $data['sort_order'] ?? ((int) Banner::query()->max('sort_order') + 1);

        return Banner::query()->create($data);
    }

    public function update(int $id, array $data, ?UploadedFile $image = null): Banner
    {
        $banner = $this->find($id);

        if ($image) {
            if ($banner->image && ! str_starts_with($banner->image, '/') && ! str_starts_with($banner->image, 'http')) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $image->store('banners', 'public');
        }

        $banner->update($data);

        return $banner->fresh();
    }

    public function delete(int $id): void
    {
        $banner = $this->find($id);

        if ($banner->image && ! str_starts_with($banner->image, '/') && ! str_starts_with($banner->image, 'http')) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
    }
}
