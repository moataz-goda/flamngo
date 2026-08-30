<div class="grid gap-6 sm:grid-cols-2">
    <div class="overflow-hidden rounded-2xl bg-[color:var(--cream)]">
        <img src="{{ $product->cover_image ?? $shop->hero_url ?? asset('images/brands/flamingo/hero.jpeg') }}" alt="{{ $product->t('name') }}" class="aspect-square w-full object-cover">
    </div>
    <div>
        <h3 class="font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ $product->t('name') }}</h3>
        @if ($product->t('short_description'))
            <p class="mt-2 text-sm text-[color:var(--muted)]">{{ $product->t('short_description') }}</p>
        @endif
        <p class="mt-4 text-xl font-extrabold text-[color:var(--deep-purple)]">{{ money($product->current_price) }}</p>

        @if ($product->is_available)
            <form action="{{ route('cart.store') }}" method="POST" class="mt-6 space-y-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @if ($product->hasVariants())
                    <div>
                        <label class="mb-1 block text-sm font-bold">{{ __('Option') }}</label>
                        <select name="variant_id" required class="w-full rounded-full border border-[color:var(--blush)] px-4 py-2 text-sm">
                            <option value="">{{ __('Choose...') }}</option>
                            @foreach ($product->activeVariants as $variant)
                                <option value="{{ $variant->id }}" @disabled(!$variant->is_available)>
                                    {{ $variant->label }} @if (!$variant->is_available) ({{ __('Sold out') }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex flex-wrap items-center gap-3">
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->available_quantity }}" class="w-24 rounded-full border border-[color:var(--blush)] px-4 py-2 text-center">
                    <button type="submit" class="btn-primary">{{ __('Add to reservation') }}</button>
                </div>
            </form>
        @else
            <p class="mt-4 text-sm font-bold text-rose-600">{{ __('Out of stock') }}</p>
        @endif

        <a href="{{ shop_url('products.show', $product->slug) }}" class="mt-4 inline-block text-sm font-bold text-[color:var(--violet)]">{{ __('View details') }}</a>
    </div>
</div>
