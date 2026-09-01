@extends('themes.flamingo.layout')

@section('title', __('Wishlist'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12">
    <div class="reveal mb-8">
        <h1 class="section-title">{{ __('Wishlist') }}</h1>
        <p class="section-sub">{{ __('Gifts you saved to come back to later.') }}</p>
    </div>

    <div class="grid grid-cols-2 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($products as $product)
            <div class="reveal">@include('themes.flamingo.partials.product-card', ['product' => $product])</div>
        @empty
            <p class="col-span-full text-center text-[color:var(--muted)]">{{ __('Your wishlist is empty.') }}</p>
        @endforelse
    </div>
</section>
@endsection
