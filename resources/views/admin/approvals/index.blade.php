@extends('layouts.admin')

@section('title', __('Approvals'))
@section('heading', __('Approvals'))

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        @foreach (['pending' => __('Pending approval'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'all' => __('All')] as $value => $label)
            <a href="{{ route('admin.approvals.index', ['status' => $value]) }}"
               class="rounded-full px-4 py-2 text-sm font-bold {{ ($status ?? 'pending') === $value ? 'bg-[color:var(--deep-purple)] text-white' : 'bg-white text-[color:var(--plum)] ring-1 ring-[color:var(--blush)]' }}">
                {{ $label }}
                @if ($value === 'pending') ({{ $pendingCount }}) @endif
            </a>
        @endforeach
    </div>
</div>

<div class="admin-card p-0">
    <div class="admin-data-table p-2 sm:p-3">
        <table style="min-width: 40rem;">
            <thead>
                <tr>
                    <th class="is-nowrap">{{ __('Date') }}</th>
                    <th>{{ __('Staff') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $item)
                    <tr>
                        <td class="is-nowrap">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                        <td class="font-bold text-[color:var(--plum)]">{{ $item->requester?->name ?? '—' }}</td>
                        <td>{{ $item->action_label }}</td>
                        <td><span class="pill">{{ $item->status_label }}</span></td>
                        <td>
                            <a href="{{ route('admin.approvals.show', $item) }}" class="font-bold text-[color:var(--violet)]">{{ __('View details') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:2.5rem;" class="text-[color:var(--muted)]">{{ __('No approval requests.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($requests->hasPages())
        <div class="border-t border-stone-100 p-4">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
