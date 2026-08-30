@extends('layouts.admin')

@section('title', $banner ? __('Edit banner') : __('Add banner'))
@section('heading', $banner ? __('Edit banner') : __('Add banner'))

@section('content')
<form action="{{ $banner ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="admin-card mx-auto max-w-2xl space-y-4">
    @csrf
    @if ($banner) @method('PUT') @endif

    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Title') }} (AR)</label>
        <input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}" required class="admin-input">
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Title') }} (EN)</label>
        <input type="text" name="title_en" value="{{ old('title_en', $banner->title_en ?? '') }}" class="admin-input" dir="ltr">
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Subtitle') }} (AR)</label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle ?? '') }}" class="admin-input">
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Subtitle') }} (EN)</label>
        <input type="text" name="subtitle_en" value="{{ old('subtitle_en', $banner->subtitle_en ?? '') }}" class="admin-input" dir="ltr">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Button text') }} (AR)</label>
            <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Button text') }} (EN)</label>
            <input type="text" name="button_text_en" value="{{ old('button_text_en', $banner->button_text_en ?? '') }}" class="admin-input" dir="ltr">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Button URL') }}</label>
            <input type="text" name="button_url" value="{{ old('button_url', $banner->button_url ?? '') }}" class="admin-input" dir="ltr">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Sort order') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0" class="admin-input">
            <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('Lower numbers appear first in the homepage carousel.') }}</p>
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm font-bold">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true))>
                {{ __('Active') }}
            </label>
        </div>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Image') }}</label>
        @if ($banner?->image_url)
            <img src="{{ $banner->image_url }}" alt="" class="mb-3 h-28 w-auto rounded-xl object-cover">
        @endif
        <input type="file" name="image" accept="image/*" class="admin-input" @if (!$banner) required @endif>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('admin.banners.index') }}" class="btn-ghost">{{ __('Back') }}</a>
    </div>
</form>
@endsection
