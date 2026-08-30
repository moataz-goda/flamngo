@extends('layouts.admin')

@section('title', __('Overview'))
@section('heading', __('Overview'))

@section('content')
@php
    $totals = $analytics['totals'] ?? ['revenue' => 0, 'orders' => 0, 'units' => 0];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        [__('Pending reservations'), $stats['pending'], 'text-amber-800'],
        [__('Accepted'), $stats['accepted'], 'text-emerald-800'],
        [__('Rejected'), $stats['rejected'], 'text-rose-600'],
        [__('Total reservations'), $stats['total'], 'text-[color:var(--plum)]'],
    ] as $card)
        <div class="admin-card">
            <p class="text-sm text-[color:var(--muted)]">{{ $card[0] }}</p>
            <p class="mt-2 font-display text-3xl font-extrabold {{ $card[2] }}">{{ $card[1] }}</p>
        </div>
    @endforeach
</div>

@if ($canReports || $canProducts)
    <section
        id="admin-analytics"
        class="mt-8 space-y-5"
        @if ($analytics)
            data-analytics="{{ json_encode($analytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        @endif
    >
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-[color:var(--plum)]">{{ __('Shop insights') }}</h2>
                <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('Sales performance, top movers, and stock that needs attention.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ([7 => __('7 days'), 30 => __('30 days'), 90 => __('90 days')] as $value => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $value]) }}"
                       class="rounded-full px-4 py-2 text-sm font-bold {{ (int) $period === (int) $value ? 'bg-[color:var(--deep-purple)] text-white' : 'bg-white text-[color:var(--plum)] ring-1 ring-[color:var(--blush)]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($canReports)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="admin-card">
                    <p class="text-sm text-[color:var(--muted)]">{{ __('Period sales') }}</p>
                    <p class="mt-2 font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ money($totals['revenue']) }}</p>
                </div>
                <div class="admin-card">
                    <p class="text-sm text-[color:var(--muted)]">{{ __('Accepted orders') }}</p>
                    <p class="mt-2 font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ $totals['orders'] }}</p>
                </div>
                <div class="admin-card">
                    <p class="text-sm text-[color:var(--muted)]">{{ __('Units sold') }}</p>
                    <p class="mt-2 font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ $totals['units'] }}</p>
                </div>
            </div>

            <div class="admin-card" data-chart-card>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Sales trend') }}</h3>
                    <span class="text-xs font-bold text-[color:var(--muted)]">{{ __('Accepted reservations') }}</span>
                </div>
                <div data-chart-body class="relative h-72">
                    <canvas id="chart-sales-trend"></canvas>
                </div>
                <p data-chart-empty class="hidden py-16 text-center text-sm text-[color:var(--muted)]">{{ __('No sales in this period yet.') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="admin-card" data-chart-card>
                    <h3 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Best selling products') }}</h3>
                    <div data-chart-body class="relative h-80">
                        <canvas id="chart-top-products"></canvas>
                    </div>
                    <p data-chart-empty class="hidden py-16 text-center text-sm text-[color:var(--muted)]">{{ __('No product sales yet.') }}</p>
                </div>

                <div class="admin-card" data-chart-card>
                    <h3 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Best customers') }}</h3>
                    <div data-chart-body class="relative h-80">
                        <canvas id="chart-top-customers"></canvas>
                    </div>
                    <p data-chart-empty class="hidden py-16 text-center text-sm text-[color:var(--muted)]">{{ __('No customer sales yet.') }}</p>
                </div>

                <div class="admin-card" data-chart-card>
                    <h3 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Best selling items') }}</h3>
                    <p class="mb-3 text-xs text-[color:var(--muted)]">{{ __('Products and variants ranked by quantity sold.') }}</p>
                    <div data-chart-body class="relative h-80">
                        <canvas id="chart-top-items"></canvas>
                    </div>
                    <p data-chart-empty class="hidden py-16 text-center text-sm text-[color:var(--muted)]">{{ __('No item sales yet.') }}</p>
                </div>

                @if ($canProducts)
                    <div class="admin-card" data-chart-card>
                        <h3 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Restock needed') }}</h3>
                        <p class="mb-3 text-xs text-[color:var(--muted)]">{{ __('Products with available stock at or below 3.') }}</p>
                        <div data-chart-body class="relative h-80">
                            <canvas id="chart-restock"></canvas>
                        </div>
                        <p data-chart-empty class="hidden py-16 text-center text-sm text-[color:var(--muted)]">{{ __('Stock looks good.') }}</p>
                    </div>
                @endif
            </div>
        @elseif ($canProducts)
            <div class="admin-card" data-chart-card>
                <h3 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Restock needed') }}</h3>
                <p class="mb-3 text-xs text-[color:var(--muted)]">{{ __('Products with available stock at or below 3.') }}</p>
                <div data-chart-body class="relative h-80">
                    <canvas id="chart-restock"></canvas>
                </div>
                <p data-chart-empty class="hidden py-16 text-center text-sm text-[color:var(--muted)]">{{ __('Stock looks good.') }}</p>
            </div>
        @endif
    </section>

    @if ($canReports || $canProducts)
        @vite('resources/js/admin-charts.js')
    @endif
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="admin-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Latest reservations') }}</h2>
            @if (auth()->user()?->canPermission('reservations.view') || auth()->user()?->isOwner())
                <a href="{{ route('admin.reservations.index') }}" class="text-sm font-bold text-[color:var(--violet)]">{{ __('View all') }}</a>
            @endif
        </div>
        <div class="space-y-3">
            @forelse ($recent as $reservation)
                @if (auth()->user()?->canPermission('reservations.view') || auth()->user()?->isOwner())
                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="flex items-center justify-between rounded-xl bg-[color:var(--cream)] px-3 py-3 text-sm hover:bg-purple-50">
                        <div>
                            <p class="font-bold" dir="ltr">{{ $reservation->reference }}</p>
                            <p class="text-[color:var(--muted)]">{{ $reservation->customer_name }}</p>
                        </div>
                        <span class="pill">{{ $reservation->status_label }}</span>
                    </a>
                @else
                    <div class="flex items-center justify-between rounded-xl bg-[color:var(--cream)] px-3 py-3 text-sm">
                        <div>
                            <p class="font-bold" dir="ltr">{{ $reservation->reference }}</p>
                            <p class="text-[color:var(--muted)]">{{ $reservation->customer_name }}</p>
                        </div>
                        <span class="pill">{{ $reservation->status_label }}</span>
                    </div>
                @endif
            @empty
                <p class="text-sm text-[color:var(--muted)]">{{ __('No reservations yet.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <h2 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Stock alerts') }}</h2>
        <div class="space-y-3">
            @forelse ($stockAlerts ?? $lowStock as $product)
                <div class="flex items-center justify-between rounded-xl bg-[color:var(--cream)] px-3 py-3 text-sm">
                    <span>{{ $product->t('name') }}</span>
                    <span class="font-bold text-rose-600">{{ $product->available_quantity }} {{ __('available') }}</span>
                </div>
            @empty
                <p class="text-sm text-[color:var(--muted)]">{{ __('Stock looks good.') }}</p>
            @endforelse
        </div>
        <div class="mt-4 flex gap-4 text-sm text-[color:var(--muted)]">
            <span>{{ $categoriesCount }} {{ __('categories') }}</span>
            <span>{{ $productsCount }} {{ __('products') }}</span>
        </div>
    </div>
</div>
@endsection
