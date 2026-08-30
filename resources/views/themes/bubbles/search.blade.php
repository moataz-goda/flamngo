@extends('themes.bubbles.layout')

@section('title', __('Search results'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12">
    <div class="reveal mb-8">
        <h1 class="section-title">{{ __('Search results') }}</h1>
        <p class="section-sub">{{ $term ? __('About: :term', ['term' => $term]) : __('Latest gifts') }}</p>
        <form action="{{ route('search') }}" method="GET" class="mt-6 max-w-md">
            <input type="search" name="q" value="{{ $term }}" placeholder="{{ __('Search gifts...') }}" class="search-pill">
        </form>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($products as $product)
            <div class="reveal">@include('themes.bubbles.partials.product-card', ['product' => $product])</div>
        @empty
            <p class="col-span-full text-[color:var(--muted)]">{{ __('No results.') }}</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
</section>
@endsection
