<header class="flamingo-header">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-3 lg:flex-nowrap lg:gap-6">
        <a href="{{ shop_url('home') }}" class="shrink-0">
            <img src="{{ $shop->logo_url ?? asset('images/brands/flamingo/logo.jpeg') }}" alt="{{ $shop->name ?? 'Flamingo Gift Shop' }}" class="brand-logo h-14 w-auto object-contain md:h-16">
        </a>

        <nav class="order-3 flex w-full items-center justify-center gap-4 overflow-x-auto text-sm font-bold lg:order-2 lg:w-auto lg:gap-5">
            <a href="{{ shop_url('home') }}" class="nav-link whitespace-nowrap transition">{{ __('Home') }}</a>
            <a href="{{ shop_url('home') }}#offers" class="nav-link whitespace-nowrap transition">{{ __('Offers') }}</a>
            <a href="{{ shop_url('home') }}#collections" class="nav-link whitespace-nowrap transition">{{ __('Collections') }}</a>
            <a href="{{ shop_url('about') }}" class="nav-link whitespace-nowrap transition">{{ __('About') }}</a>
            <a href="{{ shop_url('contact') }}" class="nav-link whitespace-nowrap transition">{{ __('Contact') }}</a>
            <a href="{{ shop_url('wishlist.index') }}" class="nav-link whitespace-nowrap transition">{{ __('Wishlist') }}</a>
            <a href="{{ shop_url('reservation.track') }}" class="nav-link whitespace-nowrap transition">{{ __('Track reservation') }}</a>
        </nav>

        <div class="ms-auto order-2 flex items-center gap-2 lg:order-3 lg:gap-3">
            <div class="flex items-center gap-1 text-xs font-bold">
                <form action="{{ url('/locale') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="lang" value="ar">
                    <button type="submit" class="locale-btn rounded-full px-2 py-1 {{ app()->getLocale() === 'ar' ? 'is-active' : '' }}">AR</button>
                </form>
                <form action="{{ url('/locale') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="lang" value="en">
                    <button type="submit" class="locale-btn rounded-full px-2 py-1 {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</button>
                </form>
            </div>

            <form action="{{ shop_url('search') }}" method="GET" class="relative hidden w-56 md:block lg:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search gifts...') }}" class="search-pill">
                <button type="submit" class="absolute start-3.5 top-1/2 -translate-y-1/2 text-white/70" aria-label="{{ __('Search') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                </button>
            </form>

            <a href="{{ shop_url('wishlist.index') }}" class="header-icon relative inline-flex h-11 w-11 items-center justify-center rounded-full" aria-label="{{ __('Wishlist') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 0 0 6.364L12 20.364l7.682-7.682a4.5 4.5 0 0 0-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0 0-6.364 0Z"/></svg>
                @if (($wishlistCount ?? 0) > 0)
                    <span class="absolute -top-1 -end-1 grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 text-[10px] font-bold text-[color:var(--plum)]">{{ $wishlistCount }}</span>
                @endif
            </a>

            <a href="{{ shop_url('cart.index') }}" class="header-icon relative inline-flex h-11 w-11 items-center justify-center rounded-full" aria-label="{{ __('Reservation cart') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13l-1.6 8h13.2M7 13l-2.6-8M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                @if (($cartCount ?? 0) > 0)
                    <span class="absolute -top-1 -end-1 grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 text-[10px] font-bold text-[color:var(--plum)]">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</header>
