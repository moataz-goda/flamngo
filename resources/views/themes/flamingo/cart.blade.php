@extends('themes.flamingo.layout')

@section('title', __('Reservation cart'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12">
    <div class="reveal mb-8">
        <h1 class="section-title">{{ __('Reservation cart') }}</h1>
        <p class="section-sub">{{ __('Review your gifts, then complete the reservation details.') }}</p>
    </div>

    @if ($items->isEmpty())
        <div class="reveal rounded-[2rem] bg-white p-10 text-center ring-1 ring-[color:var(--blush)]">
            <p class="text-[color:var(--muted)]">{{ __('Your cart is empty.') }}</p>
            <a href="{{ shop_url('home') }}#collections" class="btn-primary mt-6" data-magnetic>{{ __('Browse collections') }}</a>
        </div>
    @else
        <div class="reveal space-y-4">
            @foreach ($items as $item)
                <div class="flex flex-wrap items-center gap-4 rounded-[1.5rem] bg-white p-4 ring-1 ring-[color:var(--blush)]/70">
                    <img src="{{ $item['product']->cover_image ?? $shop->hero_url ?? asset('images/brands/flamingo/hero.jpeg') }}" alt="" class="h-20 w-20 rounded-2xl object-cover">
                    <div class="min-w-0 flex-1">
                        <a href="{{ shop_url('products.show', $item['product']->slug) }}" class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ $item['product']->t('name') }}</a>
                        @if (!empty($item['variant']))
                            <p class="text-sm text-[color:var(--violet)]">{{ $item['variant']->label }}</p>
                        @endif
                        <p class="text-sm text-[color:var(--muted)]">{{ money($item['unit_price']) }}</p>
                    </div>
                    <form action="{{ route('cart.update', $item['key']) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['variant']?->available_quantity ?? $item['product']->available_quantity }}" class="w-20 rounded-full border border-[color:var(--blush)] px-3 py-1.5 text-center text-sm">
                        <button class="text-sm font-bold text-[color:var(--violet)]">{{ __('Update') }}</button>
                    </form>
                    <div class="font-bold text-[color:var(--deep-purple)]">{{ money($item['line_total']) }}</div>
                    <form action="{{ route('cart.destroy', $item['key']) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-rose-600">{{ __('Delete') }}</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="reveal mt-8 flex flex-wrap items-center justify-between gap-4 rounded-[1.5rem] bg-gradient-to-l from-[color:var(--plum)] to-[color:var(--violet)] p-6 text-white">
            <div>
                <p class="text-white/70">{{ __('Total') }}</p>
                <p class="font-display text-3xl font-extrabold">{{ money($total) }}</p>
            </div>
            <a href="{{ shop_url('reservation.create') }}" class="rounded-full bg-white px-6 py-3 text-sm font-bold text-[color:var(--deep-purple)]" data-magnetic>{{ __('Continue reservation') }}</a>
        </div>
    @endif
</section>
@endsection
