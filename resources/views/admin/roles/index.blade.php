@extends('layouts.admin')

@section('title', __('Staff roles'))
@section('heading', __('Staff roles'))

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="max-w-2xl">
        <p class="text-sm leading-7 text-[color:var(--muted)]">
            {{ __('Create roles for your staff and choose what they can see or change in the admin panel.') }}
        </p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn-primary shrink-0">{{ __('Create new role') }}</a>
</div>

@if ($roles->isEmpty())
    <div class="admin-card py-14 text-center">
        <p class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('No roles yet.') }}</p>
        <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[color:var(--muted)]">
            {{ __('Start by creating a role, then assign it to a staff member from the Staff page.') }}
        </p>
        <a href="{{ route('admin.roles.create') }}" class="btn-primary mt-6 inline-flex">{{ __('Create new role') }}</a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($roles as $role)
            <div class="admin-card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ $role->t('name') }}</h2>
                        @if ($role->is_system)
                            <span class="rounded-full bg-[color:var(--cream)] px-2.5 py-0.5 text-xs font-bold text-[color:var(--muted)]">{{ __('Default') }}</span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="rounded-full bg-[color:var(--cream)] px-3 py-1 text-[color:var(--plum)]">
                            {{ __(':count permissions', ['count' => $role->permissions_count]) }}
                        </span>
                        <span class="rounded-full bg-[color:var(--cream)] px-3 py-1 text-[color:var(--plum)]">
                            {{ __('Used by :count staff', ['count' => $role->users_count]) }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn-ghost text-sm">{{ __('Edit role') }}</a>
                    @if (! $role->is_system && (int) $role->users_count === 0)
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('{{ __('Delete this role?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-bold text-rose-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    @elseif (! $role->is_system && (int) $role->users_count > 0)
                        <span class="text-xs text-[color:var(--muted)]">{{ __('Cannot delete: assigned to staff.') }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
