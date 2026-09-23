@extends('themes.flamingo.layout')

@section('title', __('Track reservation'))

@section('content')
<section class="mx-auto max-w-xl px-4 py-12">
    <div class="reveal mb-8 text-center">
        <h1 class="section-title">{{ __('Track reservation') }}</h1>
        <p class="section-sub mx-auto">{{ __('Enter your reference and mobile number to check your order status.') }}</p>
    </div>

    <form action="{{ route('reservation.track.submit') }}" method="POST" class="reveal space-y-4 rounded-[2rem] bg-white p-6 ring-1 ring-[color:var(--blush)]">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-bold">{{ __('Reference number') }}</label>
            <input type="text" name="reference" value="{{ old('reference', request('reference')) }}" required class="w-full rounded-2xl border border-[color:var(--blush)] px-4 py-3" dir="ltr" placeholder="{{ current_shop()?->reference_prefix ?? 'XXX' }}-XXXXXX">
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold">{{ __('Mobile number') }}</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01012345678" class="w-full rounded-2xl border border-[color:var(--blush)] px-4 py-3" dir="ltr">
        </div>
        <button class="btn-primary w-full" data-magnetic>{{ __('Show status') }}</button>
    </form>

    @if (!empty($searched))
        <div class="reveal mt-6 rounded-[1.5rem] bg-white p-6 ring-1 ring-[color:var(--blush)]">
            @if ($reservation)
                <p class="text-sm text-[color:var(--muted)]">{{ __('Reference') }}</p>
                <p class="font-display text-xl font-extrabold text-[color:var(--plum)]" dir="ltr">{{ $reservation->reference }}</p>
                <p class="mt-3">{{ __('Status') }}:
                    <span class="font-bold @class([
                        'text-amber-600' => $reservation->status === 'pending',
                        'text-emerald-600' => $reservation->status === 'accepted',
                        'text-rose-600' => $reservation->status === 'rejected',
                    ])">{{ $reservation->status_label }}</span>
                </p>
                <p class="mt-2 text-sm text-[color:var(--muted)]">{{ __('Total') }}: {{ money($reservation->total) }}</p>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($reservation->items as $item)
                        <li class="flex justify-between gap-3 border-b border-[color:var(--blush)]/50 py-2">
                            <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                            <span>{{ money($item->line_total) }}</span>
                        </li>
                    @endforeach
                    @if ($reservation->governorate)
                        <li class="flex justify-between gap-3 border-b border-[color:var(--blush)]/50 py-2">
                            <span>{{ __('Shipping') }} ({{ $reservation->governorate->t('name') }})</span>
                            <span>{{ money($reservation->shipping_cost) }}</span>
                        </li>
                    @endif
                </ul>
                @if (! $reservation->isPending() && $reservation->admin_note)
                    <div class="mt-4 rounded-xl bg-[color:var(--cream)] p-3 text-sm">
                        <p class="font-bold text-[color:var(--plum)]">{{ __('Note from the shop') }}</p>
                        <p class="mt-1 text-[color:var(--muted)]">{{ $reservation->admin_note }}</p>
                    </div>
                @endif
            @else
                <p class="text-center text-rose-600">{{ __('No reservation found with these details.') }}</p>
            @endif
        </div>
    @endif
</section>
@endsection
