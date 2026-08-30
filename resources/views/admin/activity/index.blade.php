@extends('layouts.admin')

@section('title', __('Activity log'))
@section('heading', __('Activity log'))

@section('content')
<div class="admin-card p-0">
    <div class="admin-data-table p-2 sm:p-3">
        <table style="min-width: 36rem;">
            <thead>
                <tr>
                    <th class="is-nowrap">{{ __('Date') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Description') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="is-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td><span class="pill">{{ $log->action_label }}</span></td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:2.5rem 0.75rem;" class="text-[color:var(--muted)]">
                            {{ __('No activity recorded.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="border-t border-stone-100 p-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
