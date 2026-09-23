@extends('layouts.admin')

@section('title', __('Shipping costs'))
@section('heading', __('Shipping costs'))

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="admin-card space-y-4">
        <div>
            <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Add governorate') }}</h2>
        </div>

        <form action="{{ route('admin.governorates.store') }}" method="POST" class="grid gap-4 sm:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold text-[color:var(--muted)]">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="admin-input">
                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold text-[color:var(--muted)]">{{ __('Name (English)') }}</label>
                <input type="text" name="name_en" value="{{ old('name_en') }}" class="admin-input">
                @error('name_en')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold text-[color:var(--muted)]">{{ __('Shipping cost') }}</label>
                <input type="number" min="0" step="0.01" name="shipping_cost" value="{{ old('shipping_cost', 75) }}" required class="admin-input">
                @error('shipping_cost')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-3">
                <button type="submit" class="btn-primary">{{ __('Add governorate') }}</button>
            </div>
        </form>
    </div>

    <form action="{{ route('admin.governorates.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="admin-card space-y-4">
            <div>
                <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Governorate shipping costs') }}</h2>
                <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('Set the shipping cost added to an order based on the customer\'s governorate.') }}</p>
            </div>

            <div class="space-y-2">
                @foreach ($governorates as $governorate)
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-[color:var(--cream)] px-4 py-3">
                        <span class="font-bold text-[color:var(--plum)]">{{ $governorate->t('name') }}</span>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            name="costs[{{ $governorate->id }}]"
                            value="{{ old('costs.'.$governorate->id, $governorate->shipping_cost) }}"
                            class="admin-input"
                            style="width: 8rem;"
                        >
                    </div>
                    @error('costs.'.$governorate->id)
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>

            <button type="submit" class="btn-primary">{{ __('Save') }}</button>
        </div>
    </form>
</div>
@endsection
