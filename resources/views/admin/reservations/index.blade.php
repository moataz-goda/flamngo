@extends('layouts.admin')

@section('title', __('Reservations'))
@section('heading', __('Reservations'))

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        @foreach ([null => __('All'), 'pending' => __('Pending'), 'accepted' => __('Accepted'), 'rejected' => __('Rejected')] as $value => $label)
            <a href="{{ route('admin.reservations.index', array_filter(['status' => $value], fn ($v) => $v !== null && $v !== '')) }}"
               class="rounded-full px-4 py-2 text-sm font-bold {{ ($status ?? null) === $value ? 'bg-[color:var(--deep-purple)] text-white' : 'bg-white text-[color:var(--plum)] ring-1 ring-[color:var(--blush)]' }}">
                {{ $label }}
                @if ($value) ({{ $stats[$value] ?? 0 }}) @endif
            </a>
        @endforeach
    </div>
    <a href="{{ route('admin.reservations.export', array_filter(['status' => $status ?? request('status')])) }}" class="btn-primary">
        {{ __('Export Excel') }}
    </a>
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
                    <th>{{ __('Products') }}</th>
                    <th class="is-num">{{ __('Items') }}</th>
                    <th class="is-num">{{ __('Total') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    <tr>
                        <td class="is-nowrap">{{ $reservation->created_at->format('Y-m-d H:i') }}</td>
                        <td class="is-nowrap font-bold text-[color:var(--violet)]">
                            <a href="{{ route('admin.reservations.show', $reservation) }}">{{ $reservation->reference }}</a>
                        </td>
                        <td class="font-bold text-[color:var(--plum)]">{{ $reservation->customer_name }}</td>
                        <td class="is-nowrap">{{ $reservation->phone }}</td>
                        <td>
                            <ul class="space-y-1">
                                @foreach ($reservation->items as $item)
                                    <li>
                                        <span class="font-medium">{{ $item->product_name }}</span>
                                        @if ($item->variant_label)
                                            <span class="text-xs text-[color:var(--muted)]">({{ $item->variant_label }})</span>
                                        @endif
                                        <span class="text-xs text-[color:var(--muted)]">× {{ $item->quantity }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="is-num">{{ $reservation->items_count ?? $reservation->items->count() }}</td>
                        <td class="is-num font-bold">{{ money($reservation->total) }}</td>
                        <td><span class="pill">{{ $reservation->status_label }}</span></td>
                        <td>
                            <a href="{{ route('admin.reservations.show', $reservation) }}" class="font-bold text-[color:var(--violet)]">{{ __('View details') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:2.5rem 0.75rem;" class="text-[color:var(--muted)]">
                            {{ __('No reservations yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($reservations->hasPages())
        <div class="border-t border-stone-100 p-4">{{ $reservations->links() }}</div>
    @endif
</div>
@endsection
