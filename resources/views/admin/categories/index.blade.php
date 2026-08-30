@extends('layouts.admin')

@section('title', __('Categories'))
@section('heading', __('Categories'))

@section('content')
@php($canManage = auth()->user()?->canPermission('categories.manage'))
@if ($canManage)
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">{{ __('Add category') }}</a>
    </div>
@endif

<div class="admin-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b text-right text-[color:var(--muted)]">
                <th class="px-3 py-3 font-medium">{{ __('Image') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Sort order') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Status') }}</th>
                @if ($canManage)
                    <th class="px-3 py-3 font-medium">{{ __('Actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr class="border-b border-stone-100">
                    <td class="px-3 py-3">
                        @if ($category->image_url)
                            <img src="{{ $category->image_url }}" class="h-12 w-12 rounded-xl object-cover" alt="">
                        @else
                            <div class="h-12 w-12 rounded-xl bg-purple-100"></div>
                        @endif
                    </td>
                    <td class="px-3 py-3 font-bold">{{ $category->name }}</td>
                    <td class="px-3 py-3">{{ $category->sort_order }}</td>
                    <td class="px-3 py-3">{{ $category->is_active ? __('Active') : __('Inactive') }}</td>
                    @if ($canManage)
                        <td class="px-3 py-3">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="font-bold text-[color:var(--violet)]">{{ __('Edit') }}</a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ __('Delete category?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="font-bold text-rose-600">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $categories->links() }}</div>
</div>
@endsection
