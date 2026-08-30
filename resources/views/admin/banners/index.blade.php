@extends('layouts.admin')

@section('title', __('Banners'))
@section('heading', __('Banners'))

@section('content')
@php($canManage = auth()->user()?->canPermission('banners.manage'))
<p class="mb-4 max-w-2xl text-sm leading-7 text-[color:var(--muted)]">
    {{ __('Banners appear as rotating slides on the homepage. Use them for seasonal offers, featured collections, and call-to-action buttons. Lower sort order numbers appear first.') }}
</p>
@if ($canManage)
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.banners.create') }}" class="btn-primary">{{ __('Add banner') }}</a>
    </div>
@endif

<div class="admin-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b text-right text-[color:var(--muted)]">
                <th class="px-3 py-3 font-medium">{{ __('Image') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Title') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Sort order') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Status') }}</th>
                @if ($canManage)
                    <th class="px-3 py-3 font-medium">{{ __('Actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($banners as $banner)
                <tr class="border-b border-stone-100">
                    <td class="px-3 py-3">
                        <img src="{{ $banner->image_url }}" alt="" class="h-14 w-24 rounded-xl object-cover">
                    </td>
                    <td class="px-3 py-3">
                        <p class="font-bold">{{ $banner->title }}</p>
                        @if ($banner->subtitle)
                            <p class="text-xs text-[color:var(--muted)]">{{ $banner->subtitle }}</p>
                        @endif
                    </td>
                    <td class="px-3 py-3">{{ $banner->sort_order }}</td>
                    <td class="px-3 py-3">
                        <span class="pill">{{ $banner->is_active ? __('Active') : __('Inactive') }}</span>
                    </td>
                    @if ($canManage)
                        <td class="px-3 py-3">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="font-bold text-[color:var(--violet)]">{{ __('Edit') }}</a>
                                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('{{ __('Delete banner?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="font-bold text-rose-600">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canManage ? 5 : 4 }}" class="px-3 py-8 text-center text-[color:var(--muted)]">{{ __('No banners yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
