<!DOCTYPE html>
<html lang="{{ str_starts_with(app()->getLocale(), 'ar') ? 'ar' : 'en' }}" dir="{{ locale_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) — {{ $shop->name ?? 'Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[color:var(--cream)]">
    @php($user = auth()->user())
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-l border-[color:var(--blush)]/60 bg-[color:var(--plum)] text-white md:block">
            <div class="border-b border-white/10 p-5">
                <img src="{{ $shop->logo_url ?? asset('images/brands/flamingo/logo.jpeg') }}" alt="{{ $shop->name ?? 'Shop' }}" class="h-12 w-auto rounded-lg bg-white object-contain p-1">
                <p class="mt-3 text-sm text-white/70">{{ __('Admin panel') }} {{ $shop->name ?? __('Shop') }}</p>
            </div>
            <nav class="space-y-1 p-4 text-sm font-bold">
                <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/15' : '' }}">{{ __('Overview') }}</a>
                @if ($user?->canPermission('categories.view'))
                    <a href="{{ route('admin.categories.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.categories.*') ? 'bg-white/15' : '' }}">{{ __('Categories') }}</a>
                @endif
                @if ($user?->canPermission('products.view'))
                    <a href="{{ route('admin.products.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.products.*') ? 'bg-white/15' : '' }}">{{ __('Products') }}</a>
                @endif
                @if ($user?->canPermission('banners.view'))
                    <a href="{{ route('admin.banners.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.banners.*') ? 'bg-white/15' : '' }}">{{ __('Banners') }}</a>
                @endif
                @if ($user?->canPermission('reservations.view'))
                    <a href="{{ route('admin.reservations.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.reservations.*') ? 'bg-white/15' : '' }}">{{ __('Reservations') }}</a>
                @endif
                @if ($user?->canPermission('reports.view'))
                    <a href="{{ route('admin.reports.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.reports.*') ? 'bg-white/15' : '' }}">{{ __('Reports') }}</a>
                @endif
                @if ($user?->canPermission('activity.view'))
                    <a href="{{ route('admin.activity.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.activity.*') ? 'bg-white/15' : '' }}">{{ __('Activity log') }}</a>
                @endif
                <a href="{{ route('admin.my-requests.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.my-requests.*') ? 'bg-white/15' : '' }}">
                    {{ __('My requests') }}
                    @if (($pendingMyRequests ?? 0) > 0)
                        <span class="ms-1 inline-flex min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-[10px] text-[color:var(--plum)]">{{ $pendingMyRequests }}</span>
                    @endif
                </a>
                @if ($user?->isOwner())
                    <a href="{{ route('admin.approvals.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.approvals.*') ? 'bg-white/15' : '' }}">
                        {{ __('Approvals') }}
                        @if (($pendingApprovals ?? 0) > 0)
                            <span class="ms-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px]">{{ $pendingApprovals }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.roles.*') ? 'bg-white/15' : '' }}">{{ __('Roles') }}</a>
                    <a href="{{ route('admin.settings.edit') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.settings.*') ? 'bg-white/15' : '' }}">{{ __('Shop settings') }}</a>
                    <a href="{{ route('admin.governorates.edit') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.governorates.*') ? 'bg-white/15' : '' }}">{{ __('Shipping costs') }}</a>
                    <a href="{{ route('admin.staff.index') }}" class="block rounded-xl px-3 py-2 hover:bg-white/10 {{ request()->routeIs('admin.staff.*') ? 'bg-white/15' : '' }}">{{ __('Staff') }}</a>
                @endif
                <a href="{{ route('home') }}" target="_blank" class="block rounded-xl px-3 py-2 hover:bg-white/10">{{ __('View store') }}</a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-auto p-4">
                @csrf
                <button class="w-full rounded-xl bg-white/10 px-3 py-2 text-sm font-bold hover:bg-white/20">{{ __('Log out') }}</button>
            </form>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <nav class="flex flex-wrap gap-2 border-b border-[color:var(--blush)]/50 bg-[color:var(--plum)] px-3 py-3 text-xs font-bold text-white md:hidden">
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Overview') }}</a>
                @if ($user?->canPermission('categories.view'))
                    <a href="{{ route('admin.categories.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Categories') }}</a>
                @endif
                @if ($user?->canPermission('products.view'))
                    <a href="{{ route('admin.products.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Products') }}</a>
                @endif
                @if ($user?->canPermission('banners.view'))
                    <a href="{{ route('admin.banners.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Banners') }}</a>
                @endif
                @if ($user?->canPermission('reservations.view'))
                    <a href="{{ route('admin.reservations.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Reservations') }}</a>
                @endif
                @if ($user?->canPermission('reports.view'))
                    <a href="{{ route('admin.reports.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Reports') }}</a>
                @endif
                <a href="{{ route('admin.my-requests.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">
                    {{ __('My requests') }}@if(($pendingMyRequests ?? 0) > 0) ({{ $pendingMyRequests }})@endif
                </a>
                @if ($user?->isOwner())
                    <a href="{{ route('admin.approvals.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Approvals') }}@if(($pendingApprovals ?? 0) > 0) ({{ $pendingApprovals }})@endif</a>
                    <a href="{{ route('admin.roles.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Roles') }}</a>
                    <a href="{{ route('admin.staff.index') }}" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Staff') }}</a>
                @endif
                <a href="{{ route('home') }}" target="_blank" class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('View store') }}</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="rounded-lg bg-white/10 px-2.5 py-1.5">{{ __('Exit') }}</button>
                </form>
            </nav>

            <header class="flex items-center justify-between border-b border-[color:var(--blush)]/50 bg-white/80 px-4 py-4 backdrop-blur md:px-8">
                <h1 class="font-display text-xl font-extrabold text-[color:var(--plum)]">@yield('heading', __('Dashboard'))</h1>
                <div class="flex items-center gap-3 text-sm text-[color:var(--muted)]">
                    <div class="flex items-center gap-1 text-xs font-bold">
                        <form action="{{ url('/locale') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="lang" value="ar">
                            <button type="submit" class="rounded-full px-2 py-1 {{ app()->getLocale() === 'ar' ? 'bg-[color:var(--plum)] text-white' : 'hover:text-[color:var(--plum)]' }}">AR</button>
                        </form>
                        <form action="{{ url('/locale') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="lang" value="en">
                            <button type="submit" class="rounded-full px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-[color:var(--plum)] text-white' : 'hover:text-[color:var(--plum)]' }}">EN</button>
                        </form>
                    </div>
                    <span>{{ $user->name ?? '' }}</span>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-8">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <p class="font-bold">{{ __('Could not save. Please review the following fields:') }}</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
