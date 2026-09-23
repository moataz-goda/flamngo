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
                <li>{{ __('Mobile') }}: <a href="tel:+201290051550" dir="ltr" class="hover:text-[color:var(--violet)]">01290051550</a></li>
                <li>{{ $shop->t('address') }}</li>
            </ul>

            <div class="mt-5">
                <p class="text-sm font-bold text-[color:var(--plum)]">{{ __('Follow us') }}</p>
                <div class="mt-2 flex items-center gap-3">
                    <a href="https://www.facebook.com/share/1E6WTp9wSv/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Facebook') }}" class="grid h-10 w-10 place-items-center rounded-full bg-[color:var(--plum)] text-white hover:bg-[color:var(--violet)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.2h-3.1V7.8c0-.9.3-1.5 1.6-1.5h1.6V3.4c-.3 0-1.2-.1-2.4-.1-2.4 0-4 1.4-4 4.1v2.4H7.6V13h2.7v8h3.2z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@flamingo.giftshop?_r=1&_t=ZS-99knpQL0DhQ" target="_blank" rel="noopener noreferrer" aria-label="{{ __('TikTok') }}" class="grid h-10 w-10 place-items-center rounded-full bg-[color:var(--plum)] text-white hover:bg-[color:var(--violet)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 3c.3 2.3 1.6 3.7 3.9 3.9v3c-1.4.1-2.6-.3-3.9-1.1v5.6c0 7.1-7.7 9.3-10.8 4.2-2-3.3-.8-9 5.5-9.2v3.2c-.5.1-1 .2-1.4.4-1.4.5-2.2 1.7-2 3.1.4 3 5.9 3.9 5.4-2V3h3.3z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="gold-rule"></div>
    <div class="py-5 text-center text-xs text-[color:var(--muted)]">© {{ date('Y') }} Flamingo Gift Shop. {{ __('All rights reserved.') }}</div>
</footer>
