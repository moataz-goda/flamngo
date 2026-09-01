@extends('themes.flamingo.layout')

@section('title', $category->t('name'))

@section('content')
<section class="relative overflow-hidden bg-gradient-to-l from-[color:var(--plum)] to-[color:var(--violet)] text-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-end justify-between gap-6 px-4 py-14">
        <div class="reveal">
            <p class="text-sm text-white/70">{{ __('Collections') }}</p>
            <h1 class="mt-2 font-display text-4xl font-extrabold md:text-5xl">{{ $category->t('name') }}</h1>
            @if ($category->t('description'))
                <p class="mt-3 max-w-xl text-white/80">{{ $category->t('description') }}</p>
            @endif
        </div>
        <form method="GET" class="reveal">
            @foreach (request()->only(['brand', 'color', 'material', 'size']) as $key => $value)
                @if ($value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <select name="sort" onchange="this.form.submit()" class="filter-select filter-select-light rounded-full border-0 bg-white/15 py-2 text-sm text-white outline-none backdrop-blur">
                <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>{{ __('Latest') }}</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: low to high') }}</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: high to low') }}</option>
                <option value="name" @selected(request('sort') === 'name')>{{ __('Name') }}</option>
            </select>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12">
    @if (!empty($attributeOptions))
        <form method="GET" class="reveal mb-8 flex flex-wrap gap-3 rounded-[1.5rem] bg-white p-4 ring-1 ring-[color:var(--blush)]/70">
            @if (request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @foreach ([
                'brand' => [__('Brand'), $attributeOptions['brands'] ?? []],
                'color' => [__('Color'), $attributeOptions['colors'] ?? []],
                'material' => [__('Material'), $attributeOptions['materials'] ?? []],
                'size' => [__('Size'), $attributeOptions['sizes'] ?? []],
            ] as $name => [$label, $options])
                @if (count($options))
                    <select name="{{ $name }}" class="filter-select rounded-full border border-[color:var(--blush)] bg-[color:var(--cream)] py-2 text-sm">
                        <option value="">{{ $label }}</option>
                        @foreach ($options as $option)
                            <option value="{{ $option }}" @selected(request($name) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                @endif
            @endforeach
            <button class="btn-ghost text-sm">{{ __('Filter') }}</button>
            <a href="{{ shop_url('categories.show', $category->slug) }}" class="rounded-full px-4 py-2 text-sm font-bold text-[color:var(--muted)]">{{ __('Clear') }}</a>
        </form>
    @endif

    <div class="grid grid-cols-2 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($products as $product)
            <div class="reveal">@include('themes.flamingo.partials.product-card', ['product' => $product])</div>
        @empty
            <p class="col-span-full text-center text-[color:var(--muted)]">{{ __('No products in this category yet.') }}</p>
        @endforelse
    </div>
    <div class="mt-10">{{ $products->links() }}</div>
</section>
@endsection
