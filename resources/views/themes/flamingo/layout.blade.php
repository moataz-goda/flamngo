<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ locale_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $shop->name ?? 'Flamingo Gift Shop') — {{ __('Gift Shop') }}</title>
    <meta name="description" content="@yield('meta_description', $shop->t('tagline') ?? __('Gift shop'))">
    <link rel="icon" href="{{ $shop->logo_url ?? asset('images/brands/flamingo/logo.jpeg') }}">
    @vite(['resources/css/themes/flamingo.css', 'resources/js/app.js'])
    @if ($shop)
        <style>
            :root {
                @foreach ($shop->cssColorVariables() as $var => $value)
                    {{ $var }}: {{ $value }};
                @endforeach
            }
        </style>
    @endif
</head>
<body class="min-h-screen overflow-x-hidden">
    @include('themes.flamingo.partials.header')

    @if (session('success'))
        <div class="mx-auto max-w-7xl px-4 pt-4">
            <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="mx-auto max-w-7xl px-4 pt-4">
            <div class="rounded-2xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ session('error') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('themes.flamingo.partials.footer')

    <div id="quick-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" aria-hidden="true">
        <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] bg-white p-6 shadow-2xl">
            <button type="button" id="quick-view-close" class="absolute end-4 top-4 rounded-full bg-[color:var(--cream)] px-3 py-1 text-sm font-bold text-[color:var(--plum)]">{{ __('Close') }}</button>
            <div id="quick-view-content" class="pt-6"></div>
        </div>
    </div>
    <script>
        (function () {
            const modal = document.getElementById('quick-view-modal');
            const content = document.getElementById('quick-view-content');
            const closeBtn = document.getElementById('quick-view-close');
            if (!modal) return;

            const open = (html) => {
                content.innerHTML = html;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
            };
            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                content.innerHTML = '';
            };

            closeBtn?.addEventListener('click', close);
            modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-quick-view]');
                if (!btn) return;
                e.preventDefault();
                const slug = btn.getAttribute('data-quick-view');
                try {
                    const res = await fetch(`/products/${encodeURIComponent(slug)}/quick-view`, {
                        headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('failed');
                    open(await res.text());
                } catch (err) {
                    window.location.href = `/products/${encodeURIComponent(slug)}`;
                }
            });
        })();
    </script>
</body>
</html>
