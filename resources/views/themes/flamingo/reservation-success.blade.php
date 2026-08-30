@extends('themes.flamingo.layout')

@section('title', __('Reservation submitted'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-16 text-center">
    <div class="reveal rounded-[2rem] bg-white p-10 ring-1 ring-[color:var(--blush)]">
        <div class="mx-auto mb-6 grid h-16 w-16 place-items-center rounded-full bg-emerald-50 text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
        </div>
        <h1 class="font-display text-3xl font-extrabold text-[color:var(--plum)]">{{ __('Reservation request submitted') }}</h1>
        <p class="mt-3 text-[color:var(--muted)]">{{ __('Your request is under review. Keep your reference number to track its status.') }}</p>
        <div class="mt-6 rounded-2xl bg-[color:var(--cream)] px-4 py-5">
            <p class="text-sm text-[color:var(--muted)]">{{ __('Reference number') }}</p>
            <p class="mt-1 font-display text-2xl font-extrabold tracking-wider text-[color:var(--deep-purple)]" dir="ltr">{{ $reservation->reference }}</p>
            <p class="mt-3 text-sm">{{ __('Status') }}: <strong>{{ $reservation->status_label }}</strong></p>
        </div>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('reservation.track') }}" class="btn-primary" data-magnetic>{{ __('Track reservation') }}</a>
            <a href="{{ route('home') }}" class="btn-ghost" data-magnetic>{{ __('Back to home') }}</a>
        </div>
    </div>
</section>
@endsection
