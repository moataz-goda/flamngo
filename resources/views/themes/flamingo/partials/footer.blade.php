<footer id="contact" class="mt-20 border-t border-[color:var(--blush)]/60 bg-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-3">
        <div>
            <img src="{{ $shop->logo_url ?? asset('images/brands/flamingo/logo.jpeg') }}" alt="Flamingo" class="h-16 w-auto object-contain">
            <p class="mt-4 max-w-sm text-sm leading-7 text-[color:var(--muted)]">
                {{ __('Flamingo — a luxury gift shop with a warm violet touch. Choose your collection, reserve easily, and wait for our confirmation.') }}
            </p>
        </div>
        <div>
            <h3 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Quick links') }}</h3>
            <ul class="mt-4 space-y-2 text-sm text-[color:var(--muted)]">
                <li><a href="{{ route('home') }}#collections" class="hover:text-[color:var(--violet)]">{{ __('Collections') }}</a></li>
                <li><a href="{{ route('home') }}#offers" class="hover:text-[color:var(--violet)]">{{ __('Offers') }}</a></li>
                <li><a href="{{ route('reservation.track') }}" class="hover:text-[color:var(--violet)]">{{ __('Track reservation') }}</a></li>
            </ul>
        </div>
        <div>
            <h3 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Contact us') }}</h3>
            <ul class="mt-4 space-y-2 text-sm text-[color:var(--muted)]">
                <li>{{ __('Mobile') }}: {{ $shop->phone }}</li>
                <li>{{ __('Email') }}: {{ $shop->email }}</li>
                <li>{{ $shop->t('address') }}</li>
            </ul>
        </div>
    </div>
    <div class="gold-rule"></div>
    <div class="py-5 text-center text-xs text-[color:var(--muted)]">© {{ date('Y') }} Flamingo Gift Shop. {{ __('All rights reserved.') }}</div>
</footer>
