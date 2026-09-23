@extends('layouts.admin')

@section('title', __('Reservation details'))
@section('heading', __('Reservation details'))

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm text-[color:var(--muted)]">{{ __('Reference') }}</p>
        <p class="font-display text-2xl font-extrabold text-[color:var(--plum)]" dir="ltr">{{ $reservation->reference }}</p>
    </div>
    <span class="pill text-base">{{ $reservation->status_label }}</span>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card">
            <h2 class="mb-4 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Customer') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Name') }}</p>
                    <p class="mt-1 font-bold text-[color:var(--plum)]">{{ $reservation->customer_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Mobile') }}</p>
                    <p class="mt-1 font-bold" dir="ltr">{{ $reservation->phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Date') }}</p>
                    <p class="mt-1 font-bold" dir="ltr">{{ $reservation->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Reservation total') }}</p>
                    <p class="mt-1 font-bold" dir="ltr">{{ money($reservation->total) }}</p>
                </div>
                @if ($reservation->governorate)
                    <div>
                        <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Governorate') }}</p>
                        <p class="mt-1 font-bold text-[color:var(--plum)]">{{ $reservation->governorate->t('name') }}</p>
                    </div>
                @endif
            </div>
            @if ($reservation->address)
                <div class="mt-4 rounded-xl bg-[color:var(--cream)] p-3 text-sm">
                    <p class="font-bold">{{ __('Address') }}</p>
                    <p class="mt-1 text-[color:var(--muted)]">{{ $reservation->address }}</p>
                </div>
            @endif
            @if ($reservation->note)
                <div class="mt-4 rounded-xl bg-[color:var(--cream)] p-3 text-sm">
                    <p class="font-bold">{{ __('Customer note') }}</p>
                    <p class="mt-1 text-[color:var(--muted)]">{{ $reservation->note }}</p>
                </div>
            @endif
        </div>

        @php($subtotal = $reservation->subtotal())
        @php($discountAmount = $reservation->discountAmount())
        <div class="admin-card p-0">
            <div class="border-b border-stone-100 px-5 py-4">
                <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Products') }}</h2>
            </div>
            @if ($reservation->isPending() && auth()->user()?->canPermission('reservations.decide'))
                <form action="{{ route('admin.reservations.update', $reservation) }}" method="POST" id="items-form" data-currency-symbol="{{ currency_symbol() }}" data-discount-type="{{ $reservation->discount_type }}" data-discount-value="{{ $reservation->discount_value ?? 0 }}" data-shipping-cost="{{ $reservation->shipping_cost ?? 0 }}">
                    @csrf
                    @method('PUT')
                    <div class="admin-data-table p-2 sm:p-3">
                        <table style="min-width: 36rem;" id="items-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Variant') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="is-num">{{ __('Qty') }}</th>
                                    <th class="is-num">{{ __('Unit price') }}</th>
                                    <th class="is-num">{{ __('Line total') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservation->items as $index => $item)
                                    <tr class="item-row" data-unit-price="{{ $item->unit_price }}">
                                        <td class="font-bold text-[color:var(--plum)]">
                                            {{ $item->product_name }}
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        </td>
                                        <td class="text-[color:var(--muted)]">{{ $item->variant_label ?: '—' }}</td>
                                        <td>{{ $item->product?->category?->t('name') ?? $item->product?->category?->name ?? __('Uncategorized') }}</td>
                                        <td class="is-num">
                                            <input type="number" min="1" step="1" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="admin-input item-quantity" style="width: 5rem;">
                                        </td>
                                        <td class="is-num">{{ money($item->unit_price) }}</td>
                                        <td class="is-num font-bold item-line-total">{{ money($item->line_total) }}</td>
                                        <td class="is-num">
                                            <button type="button" class="btn-ghost remove-item" title="{{ __('Remove') }}">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="is-num text-[color:var(--muted)]">{{ __('Subtotal') }}</td>
                                    <td class="is-num" id="items-subtotal">{{ money($subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="is-num text-[color:var(--muted)]">{{ __('Discount') }}</td>
                                    <td class="is-num text-rose-600" id="items-discount">- {{ money($discountAmount) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="is-num text-[color:var(--muted)]">{{ __('Shipping') }}</td>
                                    <td class="is-num" id="items-shipping">{{ money($reservation->shipping_cost ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="is-num text-[color:var(--muted)]">{{ __('Reservation total') }}</td>
                                    <td class="is-num font-display text-lg font-extrabold text-[color:var(--plum)]" id="items-grand-total">{{ money($reservation->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="border-t border-stone-100 px-5 py-4">
                        <button type="submit" id="items-save" class="rounded-full bg-[color:var(--plum)] px-5 py-3 text-sm font-bold text-white">{{ __('Save changes') }}</button>
                    </div>
                </form>

                <form action="{{ route('admin.reservations.discount', $reservation) }}" method="POST" class="flex flex-wrap items-end gap-3 border-t border-stone-100 px-5 py-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-bold text-[color:var(--muted)]">{{ __('Discount type') }}</label>
                        <select name="discount_type" class="admin-input">
                            <option value="" @selected(! $reservation->discount_type)>{{ __('No discount') }}</option>
                            <option value="fixed" @selected($reservation->discount_type === 'fixed')>{{ __('Fixed amount') }}</option>
                            <option value="percentage" @selected($reservation->discount_type === 'percentage')>{{ __('Percentage') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-[color:var(--muted)]">{{ __('Value') }}</label>
                        <input type="number" min="0" step="0.01" name="discount_value" value="{{ $reservation->discount_value ?? 0 }}" class="admin-input" style="width: 8rem;">
                    </div>
                    <button type="submit" class="rounded-full bg-[color:var(--violet)] px-5 py-3 text-sm font-bold text-white">{{ __('Apply discount') }}</button>
                </form>
            @else
                <div class="admin-data-table p-2 sm:p-3">
                    <table style="min-width: 36rem;">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Variant') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th class="is-num">{{ __('Qty') }}</th>
                                <th class="is-num">{{ __('Unit price') }}</th>
                                <th class="is-num">{{ __('Line total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservation->items as $item)
                                <tr>
                                    <td class="font-bold text-[color:var(--plum)]">{{ $item->product_name }}</td>
                                    <td class="text-[color:var(--muted)]">{{ $item->variant_label ?: '—' }}</td>
                                    <td>{{ $item->product?->category?->t('name') ?? $item->product?->category?->name ?? __('Uncategorized') }}</td>
                                    <td class="is-num">{{ $item->quantity }}</td>
                                    <td class="is-num">{{ money($item->unit_price) }}</td>
                                    <td class="is-num font-bold">{{ money($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="is-num text-[color:var(--muted)]">{{ __('Subtotal') }}</td>
                                <td class="is-num">{{ money($subtotal) }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="is-num text-[color:var(--muted)]">{{ __('Discount') }}</td>
                                <td class="is-num text-rose-600">- {{ money($discountAmount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="is-num text-[color:var(--muted)]">{{ __('Shipping') }}</td>
                                <td class="is-num">{{ money($reservation->shipping_cost ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="is-num text-[color:var(--muted)]">{{ __('Reservation total') }}</td>
                                <td class="is-num font-display text-lg font-extrabold text-[color:var(--plum)]">{{ money($reservation->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="admin-card space-y-4 h-fit">
        <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Admin decision') }}</h2>
        @if ($reservation->isPending() && auth()->user()?->canPermission('reservations.decide'))
            <form action="{{ route('admin.reservations.accept', $reservation) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="admin_note" rows="3" placeholder="{{ __('Internal note (optional)') }}" class="admin-input"></textarea>
                <button class="w-full rounded-full bg-emerald-600 px-4 py-3 text-sm font-bold text-white">{{ __('Accept reservation') }}</button>
            </form>
            <form action="{{ route('admin.reservations.reject', $reservation) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="admin_note" rows="3" placeholder="{{ __('Rejection reason (optional)') }}" class="admin-input"></textarea>
                <button class="w-full rounded-full bg-rose-600 px-4 py-3 text-sm font-bold text-white">{{ __('Reject reservation') }}</button>
            </form>
        @elseif ($reservation->isPending())
            <p class="text-sm text-[color:var(--muted)]">{{ __('You can view this reservation but cannot decide it.') }}</p>
        @else
            <p class="text-sm text-[color:var(--muted)]">{{ __('Decision made on') }} <span dir="ltr">{{ optional($reservation->decided_at)->format('Y-m-d H:i') }}</span></p>
            @if ($reservation->decidedByUser)
                <p class="text-sm">{{ __('By') }}: <strong>{{ $reservation->decidedByUser->name }}</strong></p>
            @endif
            @if ($reservation->admin_note)
                <div class="rounded-xl bg-[color:var(--cream)] p-3 text-sm">
                    <p class="font-bold">{{ __('Admin note') }}</p>
                    <p class="mt-1 text-[color:var(--muted)]">{{ $reservation->admin_note }}</p>
                </div>
            @endif
        @endif
        <a href="{{ route('admin.reservations.index') }}" class="btn-ghost w-full">{{ __('Back') }}</a>
    </div>
</div>

@if ($reservation->isPending() && auth()->user()?->canPermission('reservations.decide'))
<script>
(() => {
    const form = document.getElementById('items-form');
    if (! form) return;

    const currencySymbol = form.dataset.currencySymbol || '';
    const discountType = form.dataset.discountType || '';
    const discountValue = parseFloat(form.dataset.discountValue) || 0;
    const shippingCost = parseFloat(form.dataset.shippingCost) || 0;
    const subtotalCell = document.getElementById('items-subtotal');
    const discountCell = document.getElementById('items-discount');
    const grandTotalCell = document.getElementById('items-grand-total');
    const saveButton = document.getElementById('items-save');

    const money = (amount) => `${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currencySymbol}`;

    const recalculate = () => {
        let subtotal = 0;

        form.querySelectorAll('.item-row').forEach((row) => {
            const unitPrice = parseFloat(row.dataset.unitPrice) || 0;
            const quantityInput = row.querySelector('.item-quantity');
            const quantity = Math.max(1, parseInt(quantityInput.value, 10) || 1);
            const lineTotal = unitPrice * quantity;

            row.querySelector('.item-line-total').textContent = money(lineTotal);
            subtotal += lineTotal;
        });

        let discountAmount = 0;
        if (discountType === 'percentage') {
            discountAmount = subtotal * discountValue / 100;
        } else if (discountType === 'fixed') {
            discountAmount = discountValue;
        }
        discountAmount = Math.min(Math.max(0, discountAmount), subtotal);

        subtotalCell.textContent = money(subtotal);
        discountCell.textContent = `- ${money(discountAmount)}`;
        grandTotalCell.textContent = money(subtotal - discountAmount + shippingCost);

        const remaining = form.querySelectorAll('.item-row').length;
        saveButton.disabled = remaining === 0;
        saveButton.classList.toggle('opacity-50', remaining === 0);
    };

    form.addEventListener('input', (event) => {
        if (event.target.classList.contains('item-quantity')) {
            recalculate();
        }
    });

    form.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-item');
        if (! button) return;

        event.preventDefault();
        button.closest('.item-row')?.remove();
        recalculate();
    });
})();
</script>
@endif
@endsection
