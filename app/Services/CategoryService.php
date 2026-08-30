<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categories)
    {
    }

    public function activeWithCounts()
    {
        return $this->categories->activeWithProductCounts();
    }

    public function activeOrdered()
    {
        return $this->categories->activeOrdered();
    }

    public function findActiveBySlug(string $slug)
    {
        return $this->categories->findActiveBySlug($slug);
    }

    public function all()
    {
        return $this->categories->all();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->categories->paginate($perPage);
    }

    public function find(int $id)
    {
        return $this->categories->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null)
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($image) {
            $data['image'] = $image->store('categories', 'public');
        }

        return $this->categories->create($data);
    }

    public function update(int $id, array $data, ?UploadedFile $image = null)
    {
        $category = $this->categories->findOrFail($id);

        if (! empty($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $id);
        }

        if ($image) {
            if ($category->image && ! str_starts_with($category->image, '/')) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $image->store('categories', 'public');
        }

        return $this->categories->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $category = $this->categories->findOrFail($id);

        if ($category->image && ! str_starts_with($category->image, '/')) {
            Storage::disk('public')->delete($category->image);
        }

        return $this->categories->delete($id);
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = $this->makeSlug($name);
        $slug = $base;
        $i = 1;

        while (
            $this->categories->findBySlug($slug)
            && ($ignoreId === null || $this->categories->findBySlug($slug)?->id !== $ignoreId)
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function makeSlug(string $name): string
    {
        $slug = Str::slug($name);

        if ($slug !== '') {
            return $slug;
        }

        $slug = preg_replace('/\s+/u', '-', trim($name)) ?? '';
        $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug) ?? '';

        return $slug !== '' ? mb_strtolower($slug) : Str::random(8);
    }
}
