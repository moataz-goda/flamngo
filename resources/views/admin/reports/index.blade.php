@extends('layouts.admin')

@section('title', __('Reports'))
@section('heading', __('Sales reports'))

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        @foreach (['day' => __('Daily'), 'week' => __('Weekly'), 'category' => __('By category')] as $value => $label)
            <a href="{{ route('admin.reports.index', ['period' => $value]) }}"
               class="rounded-full px-4 py-2 text-sm font-bold {{ ($period ?? 'day') === $value ? 'bg-[color:var(--deep-purple)] text-white' : 'bg-white text-[color:var(--plum)] ring-1 ring-[color:var(--blush)]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    <a href="{{ route('admin.reports.export', ['period' => $period ?? 'day']) }}" class="btn-primary">
        {{ __('Export Excel') }}
    </a>
</div>

<div class="mb-4 grid gap-3 sm:grid-cols-3">
    <div class="admin-card">
        <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Accepted reservations') }}</p>
        <p class="mt-1 font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ $summary['reservations'] ?? 0 }}</p>
    </div>
    <div class="admin-card">
        <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Sold items') }}</p>
        <p class="mt-1 font-display text-2xl font-extrabold text-[color:var(--plum)]">{{ $summary['items'] ?? 0 }}</p>
    </div>
    <div class="admin-card">
        <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Total sales') }}</p>
        <p class="mt-1 font-display text-2xl font-extrabold text-[color:var(--plum)]" dir="ltr">{{ money($summary['total'] ?? 0) }}</p>
    </div>
</div>

<div class="admin-card p-0">
    <div class="admin-data-table p-2 sm:p-3">
        <table>
            <thead>
                <tr>
                    <th class="is-nowrap">{{ __('Date') }}</th>
                    <th class="is-nowrap">{{ __('Reference') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th class="is-nowrap">{{ __('Mobile') }}</th>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Variant') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th class="is-num">{{ __('Qty') }}</th>
                    <th class="is-num">{{ __('Unit price') }}</th>
                    <th class="is-num">{{ __('Line total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="is-nowrap">{{ $row['date'] }}</td>
                        <td class="is-nowrap font-bold text-[color:var(--violet)]">{{ $row['reference'] }}</td>
                        <td class="font-bold text-[color:var(--plum)]">{{ $row['customer_name'] }}</td>
                        <td class="is-nowrap">{{ $row['phone'] }}</td>
                        <td>{{ $row['product'] }}</td>
                        <td class="text-[color:var(--muted)]">{{ $row['variant'] ?: '—' }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td class="is-num">{{ $row['quantity'] }}</td>
                        <td class="is-num">{{ money($row['unit_price']) }}</td>
                        <td class="is-num font-bold">{{ money($row['line_total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="!text-center text-[color:var(--muted)]" style="text-align:center;padding:2.5rem 0.75rem;">
                            {{ __('No data for this period.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
