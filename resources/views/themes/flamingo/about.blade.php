@extends('themes.flamingo.layout')

@section('title', __('About'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-16">
    <div class="reveal text-center">
        <h1 class="section-title">{{ __('About') }}</h1>
        <p class="section-sub mx-auto mt-4">
            {{ $shop->t('tagline') ?? __('A luxury gift shop carefully selecting the finest pieces for you.') }}
        </p>
    </div>
    <div class="reveal mt-10 space-y-4 rounded-[2rem] bg-white p-8 leading-8 text-[color:var(--ink)]/80 ring-1 ring-[color:var(--blush)]">
        <p>
            {{ __('Welcome to :shop — a space for carefully chosen gifts, with a simple reservation experience and no account required.', ['shop' => $shop->name]) }}
        </p>
        @if ($shop->t('address'))
            <p>{{ __('We serve you from:') }} {{ $shop->t('address') }}</p>
        @endif
    </div>
</section>
@endsection
