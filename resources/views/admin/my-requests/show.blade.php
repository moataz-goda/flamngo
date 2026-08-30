@extends('layouts.admin')

@section('title', __('Request details'))
@section('heading', __('Request details'))

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="admin-card space-y-4 lg:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-[color:var(--muted)]">{{ __('Action') }}</p>
                <p class="font-display text-xl font-extrabold text-[color:var(--plum)]">{{ $approval->action_label }}</p>
            </div>
            <span class="pill">{{ $approval->status_label }}</span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 text-sm">
            <div>
                <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Date') }}</p>
                <p class="mt-1 font-bold" dir="ltr">{{ $approval->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Permission') }}</p>
                <p class="mt-1 font-bold">{{ $approval->permission_label }}</p>
            </div>
            @if ($approval->reviewed_at)
                <div>
                    <p class="text-xs font-bold text-[color:var(--muted)]">{{ __('Reviewed') }}</p>
                    <p class="mt-1 font-bold" dir="ltr">{{ $approval->reviewed_at->format('Y-m-d H:i') }} — {{ $approval->reviewer?->name }}</p>
                </div>
            @endif
        </div>

        @if ($approval->review_note)
            <div class="rounded-xl bg-[color:var(--cream)] p-3 text-sm">
                <p class="font-bold">{{ __('Review note') }}</p>
                <p class="mt-1 text-[color:var(--muted)]">{{ $approval->review_note }}</p>
            </div>
        @endif

        <div>
            <p class="mb-2 text-sm font-bold">{{ __('What you submitted') }}</p>
            <p class="mb-3 text-sm text-[color:var(--muted)]">{{ __('A readable summary of the values in this request.') }}</p>

            @if (count($approval->change_rows))
                <div class="overflow-hidden rounded-2xl ring-1 ring-[color:var(--blush)]">
                    <dl class="divide-y divide-stone-100">
                        @foreach ($approval->change_rows as $row)
                            <div class="grid gap-1 px-4 py-3 sm:grid-cols-[11rem_1fr] sm:gap-4">
                                <dt class="text-sm font-bold text-[color:var(--muted)]">{{ $row['label'] }}</dt>
                                <dd class="text-sm font-semibold text-[color:var(--plum)] whitespace-pre-line">
                                    @if (! empty($row['previous']))
                                        <span class="block text-[color:var(--muted)] line-through font-medium">{{ $row['previous'] }}</span>
                                        <span class="mt-0.5 block">{{ $row['value'] }}</span>
                                    @else
                                        {{ $row['value'] }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @else
                <p class="rounded-xl bg-[color:var(--cream)] px-4 py-3 text-sm text-[color:var(--muted)]">{{ __('No extra details available.') }}</p>
            @endif
        </div>
    </div>

    <div class="admin-card space-y-4 h-fit">
        <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Status') }}</h2>
        @if ($approval->isPending())
            <p class="text-sm leading-7 text-[color:var(--muted)]">{{ __('Waiting for the shop owner to approve or reject this request.') }}</p>
        @elseif ($approval->status === 'approved')
            <p class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ __('This request was approved and applied.') }}</p>
        @else
            <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ __('This request was rejected.') }}</p>
        @endif
        <a href="{{ route('admin.my-requests.index') }}" class="btn-ghost w-full">{{ __('Back') }}</a>
    </div>
</div>
@endsection
