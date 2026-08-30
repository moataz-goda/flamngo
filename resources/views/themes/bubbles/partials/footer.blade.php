<footer id="contact" class="mt-20 border-t border-[color:var(--blush)]/70 bg-white/80">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-3">
        <div>
            <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }}" class="h-14 w-auto rounded-xl object-contain">
            <p class="mt-4 max-w-sm text-sm leading-7 text-[color:var(--muted)]">
                {{ $shop->t('tagline') }} — {{ __('A luxury gift experience inspired by pink bubbles and elegant glass touches.') }}
            </p>
        </div>
        <div>
            <h3 class="font-display text-lg font-extrabold text-[color:var(--berry)]">{{ __('Quick links') }}</h3>
            <ul class="mt-4 space-y-2 text-sm text-[color:var(--muted)]">
                <li><a href="{{ route('home') }}#collections" class="hover:text-[color:var(--rose)]">{{ __('Collections') }}</a></li>
                <li><a href="{{ route('home') }}#offers" class="hover:text-[color:var(--rose)]">{{ __('Offers') }}</a></li>
                <li><a href="{{ route('reservation.track') }}" class="hover:text-[color:var(--rose)]">{{ __('Track reservation') }}</a></li>
            </ul>
        </div>
        <div>
            <h3 class="font-display text-lg font-extrabold text-[color:var(--berry)]">{{ __('Contact us') }}</h3>
            <ul class="mt-4 space-y-2 text-sm text-[color:var(--muted)]">
                <li>{{ __('Mobile') }}: {{ $shop->phone }}</li>
                <li>{{ __('Email') }}: {{ $shop->email }}</li>
                <li>{{ $shop->t('address') }}</li>
            </ul>
        </div>
    </div>
    <div class="py-5 text-center text-xs text-[color:var(--muted)]">© {{ date('Y') }} {{ $shop->name }}. {{ __('All rights reserved.') }}</div>
</footer>
