@extends('themes.bubbles.layout')

@section('title', __('Contact'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-16">
    <div class="reveal mb-8 text-center">
        <h1 class="section-title">{{ __('Contact') }}</h1>
        <p class="section-sub mx-auto">{{ __('We\'d love to hear from you about gifts or reservations.') }}</p>
    </div>

    <div class="reveal space-y-4 rounded-[2rem] bg-white/80 p-8 ring-1 ring-[color:var(--blush)] backdrop-blur">
        @if ($shop->phone)
            <p class="text-sm"><span class="font-bold text-[color:var(--berry)]">{{ __('Mobile') }}:</span> <span dir="ltr">{{ $shop->phone }}</span></p>
        @endif
        @if ($shop->email)
            <p class="text-sm"><span class="font-bold text-[color:var(--berry)]">{{ __('Email') }}:</span> <span dir="ltr">{{ $shop->email }}</span></p>
        @endif
        @if ($shop->t('address'))
            <p class="text-sm"><span class="font-bold text-[color:var(--berry)]">{{ __('Address') }}:</span> {{ $shop->t('address') }}</p>
        @endif
    </div>
</section>
@endsection
