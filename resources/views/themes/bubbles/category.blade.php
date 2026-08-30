@extends('themes.bubbles.layout')

@section('title', $category->t('name'))

@section('content')
<section class="relative overflow-hidden bg-gradient-to-l from-[color:var(--berry)] via-[color:var(--rose)] to-[#FF8FC7] text-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-end justify-between gap-6 px-4 py-14">
        <div class="reveal">
            <p class="text-sm text-white/70">{{ __('Collections') }}</p>
            <h1 class="mt-2 font-display text-4xl font-extrabold md:text-5xl">{{ $category->t('name') }}</h1>
            @if ($category->t('description'))
                <p class="mt-3 max-w-xl text-white/85">{{ $category->t('description') }}</p>
            @endif
        </div>
        <form method="GET" class="reveal">
            @foreach (request()->only(['brand', 'color', 'material', 'size']) as $key => $value)
                @if ($value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <select name="sort" onchange="this.form.submit()" class="filter-select filter-select-light rounded-full border-0 bg-white/20 py-2 text-sm text-white outline-none backdrop-blur">
                <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>{{ __('Latest') }}</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: low to high') }}</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: high to low') }}</option>
                <option value="name" @selected(request('sort') === 'name')>{{ __('Name') }}</option>
            </select>
        </form>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12">
    <div class="grid gap-8 lg:grid-cols-[220px_1fr]">
        <aside class="reveal hidden lg:block">
            <div class="sticky top-28 space-y-4">
                <div class="rounded-[1.5rem] bg-white/80 p-4 ring-1 ring-[color:var(--blush)] backdrop-blur">
                    <p class="mb-3 text-sm font-bold text-[color:var(--berry)]">{{ __('All collections') }}</p>
                    <ul class="space-y-1 text-sm">
                        @foreach ($navCategories as $nav)
                            <li>
                                <a href="{{ shop_url('categories.show', $nav->slug) }}"
                                   class="block rounded-xl px-3 py-2 transition {{ $nav->id === $category->id ? 'bg-[color:var(--blush)] font-bold text-[color:var(--berry)]' : 'text-[color:var(--muted)] hover:bg-[color:var(--cream)]' }}">
                                    {{ $nav->t('name') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if (!empty($attributeOptions))
                    <form method="GET" class="rounded-[1.5rem] bg-white/80 p-4 ring-1 ring-[color:var(--blush)] backdrop-blur space-y-3">
                        <p class="text-sm font-bold text-[color:var(--berry)]">{{ __('Filter') }}</p>
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
                                <select name="{{ $name }}" class="filter-select w-full rounded-xl border border-[color:var(--blush)] bg-[color:var(--cream)] py-2 text-sm">
                                    <option value="">{{ $label }}</option>
                                    @foreach ($options as $option)
                                        <option value="{{ $option }}" @selected(request($name) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @endif
                        @endforeach
                        <button class="btn-ghost w-full text-sm">{{ __('Apply') }}</button>
                        <a href="{{ shop_url('categories.show', $category->slug) }}" class="block text-center text-xs font-bold text-[color:var(--muted)]">{{ __('Clear filters') }}</a>
                    </form>
                @endif
            </div>
        </aside>

        <div>
            @if (!empty($attributeOptions))
                <form method="GET" class="reveal mb-6 flex flex-wrap gap-2 lg:hidden">
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
                            <select name="{{ $name }}" class="filter-select rounded-full border border-[color:var(--blush)] bg-white py-2 text-sm">
                                <option value="">{{ $label }}</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}" @selected(request($name) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        @endif
                    @endforeach
                    <button class="btn-ghost text-sm">{{ __('Filter') }}</button>
                </form>
            @endif

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    <div class="reveal">@include('themes.bubbles.partials.product-card', ['product' => $product])</div>
                @empty
                    <p class="col-span-full text-center text-[color:var(--muted)]">{{ __('No products in this category yet.') }}</p>
                @endforelse
            </div>
            <div class="mt-10">{{ $products->links() }}</div>
        </div>
    </div>
</section>
@endsection
