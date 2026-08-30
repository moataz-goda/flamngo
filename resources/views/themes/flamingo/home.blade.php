@extends('themes.flamingo.layout')

@section('title', __('Home'))

@section('content')
@if (!empty($banners) && $banners->isNotEmpty())
<section class="relative overflow-hidden" data-hero>
    <div class="relative">
        <div class="grid" data-banner-stage>
            @foreach ($banners as $index => $banner)
                <div
                    class="col-start-1 row-start-1 transition-opacity duration-500 ease-out {{ $index === 0 ? 'z-10 opacity-100' : 'z-0 opacity-0 pointer-events-none' }}"
                    data-banner-slide
                    @if ($index !== 0) aria-hidden="true" @endif
                >
                    <div class="mx-auto grid min-h-[28rem] max-w-7xl items-center gap-10 px-4 py-14 lg:min-h-[32rem] lg:grid-cols-2 lg:py-20">
                        <div class="relative z-10 flex h-full min-h-[12rem] flex-col justify-center">
                            <p class="reveal pill mb-4" data-hero-item>{{ $banner->t('subtitle') ?: ($shop->t('tagline') ?? __('Luxury gift shop')) }}</p>
                            <h1 class="font-display text-4xl font-extrabold leading-[1.25] text-[color:var(--plum)] md:text-6xl" data-hero-item>
                                {{ $banner->t('title') }}
                            </h1>
                            <div class="mt-8 flex min-h-[3.25rem] flex-wrap gap-3" data-hero-item>
                                @if ($banner->t('button_text') && $banner->button_url)
                                    <a href="{{ $banner->button_url }}" class="btn-primary" data-magnetic>{{ $banner->t('button_text') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="relative" data-hero-item>
                            <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-br from-[color:var(--orchid)]/20 via-transparent to-[color:var(--kraft)]/40 blur-2xl"></div>
                            <img src="{{ $banner->image_url ?? $shop->hero_url ?? asset('images/brands/flamingo/hero.jpeg') }}" alt="{{ $banner->t('title') }}" class="relative aspect-[5/4] w-full rounded-[2rem] object-cover shadow-2xl shadow-purple-900/10 ring-1 ring-white/70">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($banners->count() > 1)
            <div class="absolute inset-x-0 bottom-6 z-20 flex justify-center gap-2">
                @foreach ($banners as $index => $banner)
                    <button type="button" class="h-2.5 w-2.5 rounded-full bg-[color:var(--plum)]/30 data-[active]:bg-[color:var(--plum)]" data-banner-dot data-index="{{ $index }}" @if ($index === 0) data-active @endif aria-label="{{ __('Banner') }} {{ $index + 1 }}"></button>
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
<section class="hero-glow relative overflow-hidden" data-hero>
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 lg:grid-cols-2 lg:py-20">
        <div class="relative z-10">
            <p class="reveal pill mb-4" data-hero-item>{{ __('Luxury gift shop') }}</p>
            <h1 class="font-display text-4xl font-extrabold leading-[1.25] text-[color:var(--plum)] md:text-6xl" data-hero-item>
                {{ __('Gift with taste') }}<br>
                <span class="bg-gradient-to-l from-[color:var(--deep-purple)] to-[color:var(--orchid)] bg-clip-text text-transparent">{{ $shop->name ?? 'Flamingo' }}</span>
            </h1>
            <p class="mt-5 max-w-lg text-base leading-8 text-[color:var(--muted)]" data-hero-item>
                {{ $shop->t('tagline') ?? __('Carefully curated collections, elegant wrapping in warm violet tones, and easy reservation with your mobile number — no account needed.') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3" data-hero-item>
                <a href="#collections" class="btn-primary" data-magnetic>{{ __('Explore collections') }}</a>
                <a href="#how" class="btn-ghost" data-magnetic>{{ __('How reservations work') }}</a>
            </div>
        </div>
        <div class="relative" data-hero-item>
            <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-br from-[color:var(--orchid)]/20 via-transparent to-[color:var(--kraft)]/40 blur-2xl"></div>
            <img src="{{ $shop->hero_url ?? asset('images/brands/flamingo/hero.jpeg') }}" alt="{{ __('Gifts from') }} {{ $shop->name ?? 'Flamingo' }}" class="relative aspect-[5/4] w-full rounded-[2rem] object-cover shadow-2xl shadow-purple-900/10 ring-1 ring-white/70">
        </div>
    </div>
</section>
@endif

<section id="collections" class="mx-auto max-w-7xl px-4 py-16">
    <div class="reveal mb-10 text-center">
        <h2 class="section-title">{{ __('Gift collections') }}</h2>
        <p class="section-sub mx-auto">{{ __('Categories curated by the :shop team to help you find the perfect gift.', ['shop' => $shop->name ?? 'Flamingo']) }}</p>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($categories as $category)
            <a href="{{ shop_url('categories.show', $category->slug) }}" class="gift-card reveal group block" data-tilt data-cursor="view">
                <div class="img-zoom aspect-[16/10]">
                    @if ($category->image_url)
                        <img src="{{ $category->image_url }}" alt="{{ $category->t('name') }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center bg-gradient-to-br from-[color:var(--deep-purple)] to-[color:var(--orchid)] text-white">
                            <span class="font-display text-2xl font-bold">{{ $category->t('name') }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex items-center justify-between gap-3 p-5">
                    <div>
                        <h3 class="font-display text-xl font-extrabold text-[color:var(--plum)]">{{ $category->t('name') }}</h3>
                        <p class="mt-1 text-sm text-[color:var(--muted)]">{{ $category->products_count }} {{ __('gifts') }}</p>
                    </div>
                    <span class="rounded-full bg-[color:var(--cream)] px-3 py-1 text-xs font-bold text-[color:var(--violet)]">{{ __('Browse') }}</span>
                </div>
            </a>
        @empty
            <p class="col-span-full text-center text-[color:var(--muted)]">{{ __('No categories yet.') }}</p>
        @endforelse
    </div>
</section>

<section id="latest" class="bg-white/60 py-16">
    <div class="mx-auto max-w-7xl px-4">
        <div class="reveal mb-10 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="section-title">{{ __('Latest gifts') }}</h2>
                <p class="section-sub">{{ __('Newly arrived at :shop.', ['shop' => $shop->name ?? 'Flamingo']) }}</p>
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($latest as $product)
                <div class="reveal">@include('themes.flamingo.partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    </div>
</section>

<section id="offers" class="mx-auto max-w-7xl px-4 py-16">
    <div class="reveal mb-10">
        <h2 class="section-title">{{ __('Browse offers') }}</h2>
        <p class="section-sub">{{ __('Gifts at special prices for a limited time.') }}</p>
    </div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($offers as $product)
            <div class="reveal">@include('themes.flamingo.partials.product-card', ['product' => $product])</div>
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
            <div class="reveal rounded-[1.75rem] bg-white p-6 text-center ring-1 ring-[color:var(--blush)]/70">
                <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-full bg-gradient-to-br from-[color:var(--deep-purple)] to-[color:var(--orchid)] font-display text-lg font-bold text-white">{{ $step[0] }}</div>
                <h3 class="font-display text-xl font-extrabold text-[color:var(--plum)]">{{ $step[1] }}</h3>
                <p class="mt-2 text-sm leading-7 text-[color:var(--muted)]">{{ $step[2] }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
