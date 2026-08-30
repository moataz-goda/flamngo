@extends('layouts.admin')

@section('title', __('Staff'))
@section('heading', __('Staff'))

@section('content')
<p class="mb-6 max-w-2xl text-sm leading-7 text-[color:var(--muted)]">
    {{ __('Add team members with no privileges by default. Assign a role to grant permissions.') }}
</p>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="admin-card">
        <h2 class="mb-1 font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Add staff') }}</h2>
        <p class="mb-4 text-xs text-[color:var(--muted)]">{{ __('New staff start without permissions until you assign a role.') }}</p>

        <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <div>
                <label for="staff-name" class="mb-1 block text-sm font-bold">{{ __('Name') }}</label>
                <input id="staff-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" class="admin-input @error('name') border-rose-400 ring-1 ring-rose-300 @enderror">
                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="staff-email" class="mb-1 block text-sm font-bold">{{ __('Email') }}</label>
                <input id="staff-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" dir="ltr" class="admin-input @error('email') border-rose-400 ring-1 ring-rose-300 @enderror">
                @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="staff-password" class="mb-1 block text-sm font-bold">{{ __('Password') }}</label>
                <input id="staff-password" type="password" name="password" autocomplete="new-password" class="admin-input @error('password') border-rose-400 ring-1 ring-rose-300 @enderror">
                <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('At least 8 characters.') }}</p>
                @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="staff-role" class="mb-1 block text-sm font-bold">{{ __('Assigned role') }}</label>
                <select id="staff-role" name="assigned_role_id" class="admin-input">
                    <option value="">{{ __('No access (default)') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('assigned_role_id') === (string) $role->id)>{{ $role->t('name') }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('Optional. Leave empty for no privileges.') }}</p>
            </div>
            <button type="submit" class="btn-primary">{{ __('Add') }}</button>
        </form>
    </div>

    <div class="admin-card space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Team') }}</h2>
            <a href="{{ route('admin.roles.index') }}" class="text-sm font-bold text-[color:var(--violet)]">{{ __('Manage roles') }}</a>
        </div>
        @forelse ($staff as $member)
            <div class="rounded-2xl bg-[color:var(--cream)] p-4">
                <form action="{{ route('admin.staff.update', $member) }}" method="POST" class="space-y-3" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold">{{ __('Name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $member->name) }}" class="admin-input">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold">{{ __('Type') }}</label>
                            <p class="admin-input bg-white/60">{{ $member->isOwner() ? __('Owner') : __('Staff role') }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs text-[color:var(--muted)]" dir="ltr">{{ $member->email }}</p>
                        </div>
                        @unless ($member->isOwner())
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold">{{ __('Assigned role') }}</label>
                                <select name="assigned_role_id" class="admin-input">
                                    <option value="">{{ __('No access (default)') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) optional($member->roles->first())->id === (string) $role->id)>{{ $role->t('name') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endunless
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-bold">{{ __('New password (optional)') }}</label>
                            <input type="password" name="password" autocomplete="new-password" class="admin-input">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary text-sm">{{ __('Update') }}</button>
                </form>
                @if ($member->id !== auth()->id())
                    <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="mt-2" onsubmit="return confirm('{{ __('Delete this staff member?') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-bold text-rose-600">{{ __('Delete') }}</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-[color:var(--muted)]">{{ __('No staff members.') }}</p>
        @endforelse
    </div>
</div>
@endsection
