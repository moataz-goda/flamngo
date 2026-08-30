@extends('themes.bubbles.layout')

@section('title', __('Home'))

@section('content')
@if (!empty($banners) && $banners->isNotEmpty())
<section class="bubble-hero relative" data-hero>
    <canvas data-bubble-canvas></canvas>
    <div class="relative z-10">
        <div class="grid" data-banner-stage>
            @foreach ($banners as $index => $banner)
                <div
                    class="col-start-1 row-start-1 transition-opacity duration-500 ease-out {{ $index === 0 ? 'z-10 opacity-100' : 'z-0 opacity-0 pointer-events-none' }}"
                    data-banner-slide
                    @if ($index !== 0) aria-hidden="true" @endif
                >
                    <div class="mx-auto grid min-h-[30rem] max-w-7xl items-center gap-10 px-4 py-16 lg:min-h-[34rem] lg:grid-cols-2 lg:py-24">
                        <div class="glass-panel flex h-full min-h-[14rem] flex-col justify-center rounded-[2.25rem] p-8 md:p-10">
                            <p class="pill mb-4" data-hero-item>{{ $banner->t('subtitle') ?: ($shop->t('tagline') ?? __('Luxury gift experience')) }}</p>
                            <h1 class="font-display text-4xl font-extrabold leading-[1.2] text-[color:var(--berry)] md:text-6xl" data-hero-item>
                                {{ $banner->t('title') }}
                            </h1>
                            <div class="mt-8 flex min-h-[3.25rem] flex-wrap gap-3" data-hero-item>
                                @if ($banner->t('button_text') && $banner->button_url)
                                    <a href="{{ $banner->button_url }}" class="btn-primary" data-magnetic>{{ $banner->t('button_text') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="relative" data-hero-item>
                            <div class="absolute -inset-8 rounded-[2.5rem] opacity-70 blur-2xl" style="background: var(--iridescent);"></div>
                            <img src="{{ $banner->image_url ?? $shop->hero_url ?? asset('images/brands/bubbles/hero.jpeg') }}" alt="{{ $banner->t('title') }}" class="relative aspect-[5/4] w-full rounded-[2rem] object-cover shadow-2xl ring-1 ring-white/80">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($banners->count() > 1)
            <div class="absolute inset-x-0 bottom-8 z-20 flex justify-center gap-2">
                @foreach ($banners as $index => $banner)
                    <button type="button" class="h-2.5 w-2.5 rounded-full bg-[color:var(--berry)]/30 data-[active]:bg-[color:var(--berry)]" data-banner-dot data-index="{{ $index }}" @if ($index === 0) data-active @endif aria-label="{{ __('Banner') }} {{ $index + 1 }}"></button>
                @endforeach
            </div>
            <script>
                (function () {
                    const slides = document.querySelectorAll('[data-banner-slide]');
                    const dots = document.querySelectorAll('[data-banner-dot]');
                    if (slides.length < 2) return;
                    let i = 0;
                    const show = (n) => {
                        i = ((n % slides.length) + slides.length) % slides.length;
                        slides.forEach((s, idx) => {
                            const on = idx === i;
                            s.classList.toggle('opacity-100', on);
                            s.classList.toggle('z-10', on);
                            s.classList.toggle('opacity-0', !on);
                            s.classList.toggle('z-0', !on);
                            s.classList.toggle('pointer-events-none', !on);
                            s.setAttribute('aria-hidden', on ? 'false' : 'true');
                        });
                        dots.forEach((d, idx) => {
                            if (idx === i) d.setAttribute('data-active', '');
                            else d.removeAttribute('data-active');
                        });
                    };
                    dots.forEach((d) => d.addEventListener('click', () => show(Number(d.dataset.index))));
                    setInterval(() => show(i + 1), 5000);
                })();
            </script>
        @endif
    </div>
</section>
@else
<section class="bubble-hero relative" data-hero>
    <canvas data-bubble-canvas></canvas>
    <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 lg:grid-cols-2 lg:py-24">
        <div class="glass-panel rounded-[2.25rem] p-8 md:p-10">
            <p class="pill mb-4" data-hero-item>{{ __('Luxury gift experience') }}</p>
            <h1 class="font-display text-4xl font-extrabold leading-[1.2] text-[color:var(--berry)] md:text-6xl" data-hero-item>
                {{ __('Gifts that float') }}<br>
                <span class="bg-gradient-to-l from-[color:var(--berry)] via-[color:var(--rose)] to-[#C9A7FF] bg-clip-text text-transparent">{{ __('like joy') }}</span>
            </h1>
            <p class="mt-5 max-w-lg text-base leading-8 text-[color:var(--muted)]" data-hero-item>
                {{ $shop->t('tagline') ?? __('Bubbles — a gift shop inspired by pink bubble colors, with a luxury design and easy reservation by mobile number.') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3" data-hero-item>
                <a href="#collections" class="btn-primary" data-magnetic>{{ __('Discover collections') }}</a>
                <a href="#how" class="btn-ghost" data-magnetic>{{ __('How reservations work') }}</a>
            </div>
        </div>
        <div class="relative" data-hero-item>
            <div class="absolute -inset-8 rounded-[2.5rem] opacity-70 blur-2xl" style="background: var(--iridescent);"></div>
            <img src="{{ $shop->hero_url ?? asset('images/brands/bubbles/hero.jpeg') }}" alt="{{ $shop->name }}" class="relative aspect-[5/4] w-full rounded-[2rem] object-cover shadow-2xl ring-1 ring-white/80">
        </div>
    </div>
</section>
@endif

<section class="trust-strip border-y border-[color:var(--blush)]/50 py-4">
    <div class="marquee">
        <div class="marquee-track text-sm font-bold text-[color:var(--berry)]">
            @foreach ([...$categories, ...$categories] as $cat)
                <span>✦ {{ $cat->t('name') }}</span>
            @endforeach
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="bubble-card reveal p-6 text-center">
            <p class="font-display text-3xl font-extrabold text-[color:var(--rose)]" data-counter="{{ $categories->count() }}">0</p>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('Gift collections count') }}</p>
        </div>
        <div class="bubble-card reveal p-6 text-center">
            <p class="font-display text-3xl font-extrabold text-[color:var(--rose)]" data-counter="{{ $latest->count() + $offers->count() }}">0</p>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('Selected gifts') }}</p>
        </div>
        <div class="bubble-card reveal p-6 text-center">
            <p class="font-display text-3xl font-extrabold text-[color:var(--rose)]" data-counter="100">0</p>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('% attention to detail') }}</p>
        </div>
    </div>
</section>

<section id="collections" class="mx-auto max-w-7xl px-4 py-16">
    <div class="reveal mb-10 text-center">
        <h2 class="section-title">{{ __(':shop collections', ['shop' => $shop->name ?? 'Bubbles']) }}</h2>
        <p class="section-sub mx-auto">{{ __('Luxury categories in a distinctive feminine layout.') }}</p>
    </div>
    <div class="grid auto-rows-[180px] gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:auto-rows-[200px]">
        @forelse ($categories as $index => $category)
            <a href="{{ shop_url('categories.show', $category->slug) }}"
               class="bubble-card reveal group relative block overflow-hidden {{ $index === 0 ? 'bento-featured' : '' }}"
               data-tilt data-cursor="view">
                <div class="absolute inset-0">
                    @if ($category->image_url)
                        <img src="{{ $category->image_url }}" alt="{{ $category->t('name') }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-110">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-[color:var(--berry)] to-[color:var(--rose)]"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                </div>
                <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                    <h3 class="font-display text-xl font-extrabold md:text-2xl">{{ $category->t('name') }}</h3>
                    <p class="mt-1 text-sm text-white/80">{{ $category->products_count }} {{ __('gifts') }}</p>
                </div>
            </a>
        @empty
            <p class="col-span-full text-center text-[color:var(--muted)]">{{ __('No categories yet.') }}</p>
        @endforelse
    </div>
</section>

<section id="latest" class="bg-white/50 py-16">
    <div class="mx-auto max-w-7xl px-4">
        <div class="reveal mb-10">
            <h2 class="section-title">{{ __('Latest gifts') }}</h2>
            <p class="section-sub">{{ __('Newly arrived at :shop.', ['shop' => $shop->name ?? 'Bubbles']) }}</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($latest as $product)
                <div class="reveal">@include('themes.bubbles.partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    </div>
</section>

<section id="offers" class="mx-auto max-w-7xl px-4 py-16">
    <div class="reveal mb-10">
        <h2 class="section-title">{{ __('Special offers') }}</h2>
        <p class="section-sub">{{ __('Gifts at special prices for a limited time.') }}</p>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($offers as $product)
            <div class="reveal">@include('themes.bubbles.partials.product-card', ['product' => $product])</div>
        @empty
            <p class="col-span-full text-[color:var(--muted)]">{{ __('No offers at the moment.') }}</p>
        @endforelse
    </div>
</section>

<section id="how" class="mx-auto max-w-7xl px-4 py-16">
    <div class="reveal mb-10 text-center">
        <h2 class="section-title">{{ __('How reservations work') }}</h2>
        <p class="section-sub mx-auto">{{ __('Three simple steps with no account required.') }}</p>
    </div>
    <div class="grid gap-6 md:grid-cols-3">
        @foreach ([
            ['1', __('Choose your gift'), __('Browse collections and add what you like to the reservation cart.')],
            ['2', __('Enter your details'), __('Name and mobile number are required to complete the order.')],
            ['3', __('Wait for confirmation'), __('The shop team reviews your request and accepts or rejects it.')],
        ] as $step)
            <div class="bubble-card reveal p-6 text-center">
                <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-full bg-gradient-to-br from-[color:var(--berry)] to-[color:var(--rose)] font-display text-lg font-bold text-white">{{ $step[0] }}</div>
                <h3 class="font-display text-xl font-extrabold text-[color:var(--berry)]">{{ $step[1] }}</h3>
                <p class="mt-2 text-sm leading-7 text-[color:var(--muted)]">{{ $step[2] }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
