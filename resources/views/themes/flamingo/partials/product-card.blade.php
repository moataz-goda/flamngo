@props(['product'])

<article class="gift-card group relative" data-tilt>
    <form action="{{ route('wishlist.toggle') }}" method="POST" class="absolute end-3 top-3 z-10">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-[color:var(--violet)] shadow-sm ring-1 ring-[color:var(--blush)]" aria-label="{{ __('Wishlist') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 0 0 6.364L12 20.364l7.682-7.682a4.5 4.5 0 0 0-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0 0-6.364 0Z"/></svg>
        </button>
    </form>

    <a href="{{ shop_url('products.show', $product->slug) }}" class="block" data-cursor="view">
        <div class="img-zoom aspect-[4/5] bg-[color:var(--cream)]">
            @if ($product->cover_image)
                <img src="{{ $product->cover_image }}" alt="{{ $product->t('name') }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center bg-gradient-to-br from-[color:var(--blush)] to-[color:var(--kraft)] text-[color:var(--deep-purple)]">
                    <span class="font-display text-xl font-bold">Flamingo</span>
                </div>
            @endif
        </div>
        <div class="space-y-2 p-4">
            @if ($product->category)
                <span class="pill">{{ $product->category->t('name') }}</span>
            @endif
            <h3 class="font-display text-lg font-extrabold text-[color:var(--plum)] transition group-hover:text-[color:var(--violet)]">{{ $product->t('name') }}</h3>
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-baseline gap-2">
                    <span class="text-base font-bold text-[color:var(--deep-purple)]">{{ money($product->current_price) }}</span>
                    @if ($product->is_on_sale)
                        <span class="text-xs text-[color:var(--muted)] line-through">{{ money($product->price) }}</span>
                    @endif
                </div>
                @if ($product->is_available)
                    <span class="text-xs font-medium text-emerald-700">{{ __('Available') }} {{ $product->available_quantity }}</span>
                @else
                    <span class="text-xs font-medium text-rose-600">{{ __('Out of stock') }}</span>
                @endif
            </div>
        </div>
    </a>
    <div class="px-4 pb-4">
        <button type="button" data-quick-view="{{ $product->slug }}" class="w-full rounded-full bg-[color:var(--cream)] px-3 py-2 text-xs font-bold text-[color:var(--violet)] ring-1 ring-[color:var(--blush)] transition hover:bg-white">
            {{ __('Quick view') }}
        </button>
    </div>
</article>
