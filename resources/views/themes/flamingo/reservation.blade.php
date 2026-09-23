@extends('themes.flamingo.layout')

@section('title', __('Complete reservation'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <div class="reveal mb-8 text-center">
        <h1 class="section-title">{{ __('Complete reservation') }}</h1>
        <p class="section-sub mx-auto">{{ __('Enter your details and we\'ll contact you after reviewing the order.') }}</p>
    </div>

    <div class="reveal mb-6 rounded-[1.5rem] bg-white p-5 ring-1 ring-[color:var(--blush)]" id="order-summary" data-items-total="{{ $total }}" data-currency-symbol="{{ currency_symbol() }}">
        <div class="flex items-center justify-between text-sm text-[color:var(--muted)]">
            <span>{{ __('Items total') }} ({{ $items->count() }} {{ __('items') }})</span>
            <span>{{ money($total) }}</span>
        </div>
        <div class="mt-1 flex items-center justify-between text-sm text-[color:var(--muted)]">
            <span>{{ __('Shipping') }}</span>
            <span id="summary-shipping">—</span>
        </div>
        <div class="mt-2 flex items-center justify-between border-t border-[color:var(--blush)] pt-2 text-base font-bold text-[color:var(--deep-purple)]">
            <span>{{ __('Total') }}</span>
            <span id="summary-grand-total">{{ money($total) }}</span>
        </div>
    </div>

    <form action="{{ route('reservation.store') }}" method="POST" class="reveal space-y-5 rounded-[2rem] bg-white p-6 ring-1 ring-[color:var(--blush)] md:p-8">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--plum)]">{{ __('Full name') }}</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
            @error('customer_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--plum)]">{{ __('Mobile number') }} <span class="text-rose-500">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01012345678" class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" dir="ltr">
            <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('Egyptian number starting with 010, 011, 012, or 015') }}</p>
            @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--plum)]">{{ __('Governorate') }} <span class="text-rose-500">*</span></label>
            <select name="governorate_id" id="governorate-select" required class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
                <option value="">{{ __('Select governorate') }}</option>
                @foreach ($governorates as $gov)
                    <option value="{{ $gov->id }}" data-shipping-cost="{{ $gov->shipping_cost }}" @selected(old('governorate_id') == $gov->id)>{{ $gov->t('name') }}</option>
                @endforeach
            </select>
            @error('governorate_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--plum)]">{{ __('Address') }} <span class="text-rose-500">*</span></label>
            <textarea name="address" rows="3" required placeholder="{{ __('Street, building, floor, and any landmarks that help find you') }}" class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">{{ old('address') }}</textarea>
            @error('address')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--plum)]">{{ __('Note (optional)') }}</label>
            <textarea name="note" rows="4" class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">{{ old('note') }}</textarea>
        </div>
        <button type="submit" class="btn-primary w-full" data-magnetic>{{ __('Confirm reservation request') }}</button>
    </form>
</section>

<script>
(() => {
    const summary = document.getElementById('order-summary');
    const select = document.getElementById('governorate-select');
    if (! summary || ! select) return;

    const currencySymbol = summary.dataset.currencySymbol || '';
    const itemsTotal = parseFloat(summary.dataset.itemsTotal) || 0;
    const shippingCell = document.getElementById('summary-shipping');
    const grandTotalCell = document.getElementById('summary-grand-total');

    const money = (amount) => `${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currencySymbol}`;

    const update = () => {
        const option = select.options[select.selectedIndex];
        const shipping = option && option.value ? (parseFloat(option.dataset.shippingCost) || 0) : 0;
        shippingCell.textContent = option && option.value ? money(shipping) : '—';
        grandTotalCell.textContent = money(itemsTotal + shipping);
    };

    select.addEventListener('change', update);
    update();
})();
</script>
@endsection
