@extends('themes.bubbles.layout')

@section('title', $product->t('name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12">
    <div class="grid gap-10 lg:grid-cols-2">
        <div class="reveal space-y-3">
            <div class="img-zoom overflow-hidden rounded-[2rem] bg-white ring-1 ring-[color:var(--blush)]" data-cursor="view">
                <img id="main-image" src="{{ $product->cover_image ?? $shop->hero_url }}" alt="{{ $product->t('name') }}" class="aspect-square w-full object-cover">
            </div>
            @if ($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto">
                    @foreach ($product->images as $image)
                        <button type="button" onclick="document.getElementById('main-image').src='{{ $image->url }}'" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl ring-1 ring-[color:var(--blush)]">
                            <img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="reveal">
            @if ($product->category)
                <a href="{{ shop_url('categories.show', $product->category->slug) }}" class="pill">{{ $product->category->t('name') }}</a>
            @endif
            <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
                <h1 class="font-display text-4xl font-extrabold text-[color:var(--berry)]">{{ $product->t('name') }}</h1>
                <form action="{{ route('wishlist.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-[color:var(--rose)] ring-1 ring-[color:var(--blush)]">
                        ♥ {{ __('Wishlist') }}
                    </button>
                </form>
            </div>
            @if ($product->t('short_description'))
                <p class="mt-3 text-[color:var(--muted)]">{{ $product->t('short_description') }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2 text-sm text-[color:var(--muted)]">
                @if ($product->brand)<span class="pill">{{ __('Brand') }}: {{ $product->brand }}</span>@endif
                @if ($product->t('color'))<span class="pill">{{ __('Color') }}: {{ $product->t('color') }}</span>@endif
                @if ($product->t('material'))<span class="pill">{{ __('Material') }}: {{ $product->t('material') }}</span>@endif
                @if ($product->t('size'))<span class="pill">{{ __('Size') }}: {{ $product->t('size') }}</span>@endif
            </div>

            <div class="mt-6 flex items-baseline gap-3">
                <span class="text-3xl font-extrabold text-[color:var(--rose)]">{{ money($product->current_price) }}</span>
                @if ($product->is_on_sale)
                    <span class="text-lg text-[color:var(--muted)] line-through">{{ money($product->price) }}</span>
                @endif
            </div>

            <div class="mt-4">
                @if ($product->is_available)
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700">{{ __('Available') }} — {{ $product->available_quantity }} {{ __('units') }}</span>
                @else
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-700">{{ __('Out of stock') }}</span>
                @endif
            </div>

            @if ($product->t('description'))
                <div class="mt-6 leading-8 text-[color:var(--ink)]/80">{{ $product->t('description') }}</div>
            @endif

            @if ($product->is_available)
                <form action="{{ route('cart.store') }}" method="POST" class="mt-8 space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($product->hasVariants())
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[color:var(--berry)]">{{ __('Choose option') }}</label>
                            <select name="variant_id" required class="w-full max-w-sm rounded-full border border-[color:var(--blush)] px-4 py-2 outline-none focus:ring-2 focus:ring-pink-200">
                                <option value="">{{ __('Choose...') }}</option>
                                @foreach ($product->activeVariants as $variant)
                                    <option value="{{ $variant->id }}" @disabled(!$variant->is_available)>
                                        {{ $variant->label }} — {{ $variant->is_available ? $variant->available_quantity.' '.__('available') : __('Sold out') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="text-sm font-bold text-[color:var(--berry)]">{{ __('Quantity') }}</label>
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->available_quantity }}" class="w-24 rounded-full border border-[color:var(--blush)] px-4 py-2 text-center outline-none focus:ring-2 focus:ring-pink-200">
                        <button type="submit" class="btn-primary" data-magnetic>{{ __('Add to reservation') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if (isset($related) && $related->isNotEmpty())
        <div class="mt-16">
            <h2 class="section-title mb-6">{{ __('Related products') }}</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)
                    @include('themes.bubbles.partials.product-card', ['product' => $item])
                @endforeach
            </div>
        </div>
    @endif

    @if (isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
        <div class="mt-16">
            <h2 class="section-title mb-6">{{ __('Recently viewed') }}</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($recentlyViewed as $item)
                    @include('themes.bubbles.partials.product-card', ['product' => $item])
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
