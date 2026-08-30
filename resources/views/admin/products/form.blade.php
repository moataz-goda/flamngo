@extends('layouts.admin')

@section('title', $product ? __('Edit product') : __('Add product'))
@section('heading', $product ? __('Edit product') : __('Add product'))

@section('content')
<form action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="admin-card mx-auto max-w-4xl space-y-4">
    @csrf
    @if ($product) @method('PUT') @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-bold">{{ __('Name') }} (AR)</label>
            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="admin-input">
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-bold">{{ __('Name') }} (EN)</label>
            <input type="text" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}" class="admin-input" dir="ltr">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Category') }}</label>
            <select name="category_id" required class="admin-input">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->t('name') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">SKU</label>
            <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Brand') }}</label>
            <input type="text" name="brand" value="{{ old('brand', $product->brand ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Color') }} (AR)</label>
            <input type="text" name="color" value="{{ old('color', $product->color ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Color') }} (EN)</label>
            <input type="text" name="color_en" value="{{ old('color_en', $product->color_en ?? '') }}" class="admin-input" dir="ltr">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Material') }} (AR)</label>
            <input type="text" name="material" value="{{ old('material', $product->material ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Material') }} (EN)</label>
            <input type="text" name="material_en" value="{{ old('material_en', $product->material_en ?? '') }}" class="admin-input" dir="ltr">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Size') }} (AR)</label>
            <input type="text" name="size" value="{{ old('size', $product->size ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Size') }} (EN)</label>
            <input type="text" name="size_en" value="{{ old('size_en', $product->size_en ?? '') }}" class="admin-input" dir="ltr">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Price') }}</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Sale price') }}</label>
            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Stock quantity') }}</label>
            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required class="admin-input">
            @if ($product)
                <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('Currently reserved: :count — if variants exist, stock is managed from them', ['count' => $product->reserved_quantity]) }}</p>
            @endif
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Short description') }} (AR)</label>
            <input type="text" name="short_description" value="{{ old('short_description', $product->short_description ?? '') }}" class="admin-input">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Short description') }} (EN)</label>
            <input type="text" name="short_description_en" value="{{ old('short_description_en', $product->short_description_en ?? '') }}" class="admin-input" dir="ltr">
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Description') }} (AR)</label>
        <textarea name="description" rows="4" class="admin-input">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Description') }} (EN)</label>
        <textarea name="description_en" rows="4" class="admin-input" dir="ltr">{{ old('description_en', $product->description_en ?? '') }}</textarea>
    </div>

    <div class="rounded-2xl border border-[color:var(--blush)]/60 p-4">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-bold">{{ __('Variants (size/color)') }}</h3>
            <button type="button" id="add-variant" class="rounded-xl bg-[color:var(--plum)] px-3 py-1.5 text-xs font-bold text-white">+ {{ __('Add') }}</button>
        </div>
        <div id="variants" class="space-y-3">
            @php
                $variantRows = old('variants', $product?->variants?->map(fn ($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'size' => $v->size,
                    'color' => $v->color,
                    'stock_quantity' => $v->stock_quantity,
                    'price_override' => $v->price_override,
                    'is_active' => $v->is_active,
                ])->all() ?? []);
            @endphp
            @forelse ($variantRows as $i => $variant)
                <div class="variant-row grid gap-2 rounded-xl bg-[color:var(--cream)] p-3 sm:grid-cols-6">
                    <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] ?? '' }}">
                    <input type="text" name="variants[{{ $i }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="SKU" class="admin-input">
                    <input type="text" name="variants[{{ $i }}][size]" value="{{ $variant['size'] ?? '' }}" placeholder="{{ __('Size') }}" class="admin-input">
                    <input type="text" name="variants[{{ $i }}][color]" value="{{ $variant['color'] ?? '' }}" placeholder="{{ __('Color') }}" class="admin-input">
                    <input type="number" name="variants[{{ $i }}][stock_quantity]" value="{{ $variant['stock_quantity'] ?? 0 }}" placeholder="Stock" class="admin-input">
                    <input type="number" step="0.01" name="variants[{{ $i }}][price_override]" value="{{ $variant['price_override'] ?? '' }}" placeholder="Price override" class="admin-input">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked(($variant['is_active'] ?? true))> {{ __('Active') }}</label>
                </div>
            @empty
            @endforelse
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-bold">{{ __('Product images') }}</label>
        <input type="file" name="images[]" accept="image/*" multiple class="admin-input">
    </div>

    @if ($product && $product->images->count())
        <div class="flex flex-wrap gap-3">
            @foreach ($product->images as $image)
                <div class="relative">
                    <img src="{{ $image->url }}" class="h-24 w-24 rounded-xl object-cover" alt="">
                    <button form="delete-image-{{ $image->id }}" class="absolute -top-2 -start-2 grid h-6 w-6 place-items-center rounded-full bg-rose-600 text-xs text-white">×</button>
                </div>
            @endforeach
        </div>
    @endif

    <div class="flex gap-6 text-sm">
        <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))> {{ __('Featured') }}</label>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))> {{ __('Active') }}</label>
    </div>

    <button class="btn-primary">{{ __('Save') }}</button>
</form>

@if ($product && $product->images->count())
    @foreach ($product->images as $image)
        <form id="delete-image-{{ $image->id }}" action="{{ route('admin.product-images.destroy', $image) }}" method="POST" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endforeach
@endif

<script>
(() => {
    const wrap = document.getElementById('variants');
    const btn = document.getElementById('add-variant');
    let i = wrap.querySelectorAll('.variant-row').length;
    const activeLabel = @json(__('Active'));
    btn?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'variant-row grid gap-2 rounded-xl bg-[color:var(--cream)] p-3 sm:grid-cols-6';
        row.innerHTML = `
            <input type="hidden" name="variants[${i}][id]" value="">
            <input type="text" name="variants[${i}][sku]" placeholder="SKU" class="admin-input">
            <input type="text" name="variants[${i}][size]" placeholder="Size" class="admin-input">
            <input type="text" name="variants[${i}][color]" placeholder="Color" class="admin-input">
            <input type="number" name="variants[${i}][stock_quantity]" value="0" class="admin-input">
            <input type="number" step="0.01" name="variants[${i}][price_override]" placeholder="Price override" class="admin-input">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="variants[${i}][is_active]" value="1" checked> ${activeLabel}</label>
        `;
        wrap.appendChild(row);
        i++;
    });
})();
</script>
@endsection
