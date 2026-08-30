@extends('layouts.admin')

@section('title', __('Products'))
@section('heading', __('Products'))

@section('content')
@php($canManage = auth()->user()?->canPermission('products.manage'))
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search...') }}" class="admin-input w-48">
        <select name="category_id" class="admin-input w-44">
            <option value="">{{ __('All categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button class="btn-ghost">{{ __('Filter') }}</button>
    </form>
    @if ($canManage)
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.import') }}" class="btn-ghost">{{ __('Import Excel') }}</a>
            <a href="{{ route('admin.products.create') }}" class="btn-primary">{{ __('Add product') }}</a>
        </div>
    @endif
</div>

<div class="admin-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b text-right text-[color:var(--muted)]">
                <th class="px-3 py-3 font-medium">{{ __('Product') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Category') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Price') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Stock') }}</th>
                <th class="px-3 py-3 font-medium">{{ __('Available') }}</th>
                @if ($canManage)
                    <th class="px-3 py-3 font-medium">{{ __('Actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="border-b border-stone-100">
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->cover_image ?? asset('images/brand/hero.jpeg') }}" class="h-12 w-12 rounded-xl object-cover" alt="">
                            <span class="font-bold">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-3">{{ $product->category?->name }}</td>
                    <td class="px-3 py-3">{{ money($product->current_price) }}</td>
                    <td class="px-3 py-3">{{ $product->stock_quantity }} / {{ __('Reserved') }} {{ $product->reserved_quantity }}</td>
                    <td class="px-3 py-3 font-bold">{{ $product->available_quantity }}</td>
                    @if ($canManage)
                        <td class="px-3 py-3">
                            <div class="flex gap-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-[color:var(--violet)]">{{ __('Edit') }}</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('Delete product?') }}')">
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
    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection
