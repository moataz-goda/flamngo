@extends('themes.bubbles.layout')

@section('title', __('Complete reservation'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12">
    <div class="reveal mb-8 text-center">
        <h1 class="section-title">{{ __('Complete reservation') }}</h1>
        <p class="section-sub mx-auto">{{ __('Enter your details and we\'ll contact you after reviewing the order.') }}</p>
    </div>

    <div class="reveal mb-6 rounded-[1.5rem] bg-white p-5 ring-1 ring-[color:var(--blush)]">
        <p class="text-sm text-[color:var(--muted)]">{{ __('Order summary') }} — {{ $items->count() }} {{ __('items') }} — {{ __('Total') }}
            <strong class="text-[color:var(--rose)]">{{ money($total) }}</strong>
        </p>
    </div>

    <form action="{{ route('reservation.store') }}" method="POST" class="reveal space-y-5 rounded-[2rem] bg-white p-6 ring-1 ring-[color:var(--blush)] md:p-8">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--berry)]">{{ __('Full name') }}</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">
            @error('customer_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--berry)]">{{ __('Mobile number') }} <span class="text-rose-500">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01012345678" class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200" dir="ltr">
            <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('Egyptian number starting with 010, 011, 012, or 015') }}</p>
            @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-[color:var(--berry)]">{{ __('Note (optional)') }}</label>
            <textarea name="note" rows="4" class="w-full rounded-2xl border border-[color:var(--blush)] bg-[color:var(--cream)] px-4 py-3 outline-none focus:ring-2 focus:ring-purple-200">{{ old('note') }}</textarea>
        </div>
        <button type="submit" class="btn-primary w-full" data-magnetic>{{ __('Confirm reservation request') }}</button>
    </form>
</section>
@endsection
