<!DOCTYPE html>
<html lang="{{ str_starts_with(app()->getLocale(), 'ar') ? 'ar' : 'en' }}" dir="{{ locale_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Admin login') }} — {{ $shop->name ?? 'Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hero-glow flex min-h-screen items-center justify-center px-4">
    <div class="w-full max-w-md rounded-[2rem] bg-white/90 p-8 shadow-xl shadow-purple-900/10 ring-1 ring-[color:var(--blush)] backdrop-blur">
        <div class="mb-6 text-center">
            <img src="{{ $shop->logo_url ?? asset('images/brands/flamingo/logo.jpeg') }}" alt="{{ $shop->name ?? 'Shop' }}" class="mx-auto h-16 object-contain">
            <h1 class="mt-4 font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ __('Admin panel') }} {{ $shop->name ?? '' }}</h1>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('Admin access only') }}</p>
        </div>

        <form method="POST" action="{{ url()->current() }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Email address') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="admin-input" dir="ltr">
                @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Password') }}</label>
                <input type="password" name="password" required class="admin-input" dir="ltr">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> {{ __('Remember me') }}
            </label>
            <button class="btn-primary w-full">{{ __('Log in') }}</button>
        </form>
    </div>
</body>
</html>
