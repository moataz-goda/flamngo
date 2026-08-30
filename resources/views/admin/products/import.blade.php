@extends('layouts.admin')

@section('title', __('Import products from Excel'))
@section('heading', __('Import products from Excel'))

@section('content')
<div class="admin-card mx-auto max-w-2xl space-y-4">
    <p class="text-sm leading-7 text-[color:var(--muted)]">
        {{ __('Upload an Excel file (.xlsx) with product columns. You can download a sample file to see the required format.') }}
    </p>

    <a href="{{ route('admin.products.import.sample') }}" class="inline-flex text-sm font-bold text-[color:var(--violet)]">{{ __('Download sample Excel') }}</a>

    <form action="{{ route('admin.products.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-bold">{{ __('Excel file') }}</label>
            <input type="file" name="excel" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required class="admin-input">
            @error('excel')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">{{ __('Import') }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn-ghost">{{ __('Back') }}</a>
        </div>
    </form>
</div>
@endsection
