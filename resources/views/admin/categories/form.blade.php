@extends('layouts.admin')

@section('title', $category ? __('Edit category') : __('Add category'))
@section('heading', $category ? __('Edit category') : __('Add category'))

@section('content')
<form action="{{ $category ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="admin-card mx-auto max-w-2xl space-y-4">
    @csrf
    @if ($category) @method('PUT') @endif

    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Name') }} (AR)</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required class="admin-input">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Name') }} (EN)</label>
        <input type="text" name="name_en" value="{{ old('name_en', $category->name_en ?? '') }}" class="admin-input" dir="ltr">
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Description') }} (AR)</label>
        <textarea name="description" rows="3" class="admin-input">{{ old('description', $category->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Description') }} (EN)</label>
        <textarea name="description_en" rows="3" class="admin-input" dir="ltr">{{ old('description_en', $category->description_en ?? '') }}</textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Sort order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Icon (optional)') }}</label>
            <input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}" class="admin-input">
        </div>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Image') }}</label>
        <input type="file" name="image" accept="image/*" class="admin-input">
        @if ($category?->image_url)
            <img src="{{ $category->image_url }}" class="mt-3 h-24 w-24 rounded-xl object-cover" alt="">
        @endif
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
        {{ __('Active') }}
    </label>
    <button class="btn-primary">{{ __('Save') }}</button>
</form>
@endsection
