@extends('layouts.admin')

@section('title', $role ? __('Edit role') : __('Create new role'))
@section('heading', $role ? __('Edit role') : __('Create new role'))

@section('content')
@php
    $permissionMeta = [
        'categories.view' => [
            'title' => __('View categories'),
            'help' => __('Can open the categories list only.'),
        ],
        'categories.manage' => [
            'title' => __('Manage categories'),
            'help' => __('Can add, edit, and delete categories.'),
        ],
        'products.view' => [
            'title' => __('View products'),
            'help' => __('Can open the products list only.'),
        ],
        'products.manage' => [
            'title' => __('Manage products'),
            'help' => __('Can add, edit, delete, and import products.'),
        ],
        'banners.view' => [
            'title' => __('View banners'),
            'help' => __('Can open the banners list only.'),
        ],
        'banners.manage' => [
            'title' => __('Manage banners'),
            'help' => __('Can add, edit, and delete homepage banners.'),
        ],
        'reservations.view' => [
            'title' => __('View reservations'),
            'help' => __('Can open reservations and export them.'),
        ],
        'reservations.decide' => [
            'title' => __('Accept or reject reservations'),
            'help' => __('Can accept or reject customer reservation requests.'),
        ],
        'reports.view' => [
            'title' => __('View sales reports'),
            'help' => __('Can open sales reports and download Excel.'),
        ],
        'activity.view' => [
            'title' => __('View activity log'),
            'help' => __('Can open the shop activity history.'),
        ],
    ];
@endphp

<form action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST" class="space-y-6 pb-24">
    @csrf
    @if ($role) @method('PUT') @endif

    <div class="admin-card space-y-4">
        <div>
            <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Role details') }}</h2>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('This name appears when you assign the role to a staff member.') }}</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Role name') }} (AR)</label>
                <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required class="admin-input" placeholder="{{ __('Example: Sales assistant') }}">
                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Role name') }} (EN)</label>
                <input type="text" name="name_en" value="{{ old('name_en', $role->name_en ?? '') }}" class="admin-input" dir="ltr" placeholder="Sales assistant">
            </div>
        </div>
    </div>

    <div class="admin-card role-mode-legend">
        <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('How access works') }}</h2>
        <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('For each permission, choose one option:') }}</p>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="role-legend-card">
                <span class="role-legend-dot role-legend-dot--off"></span>
                <p class="font-bold text-[color:var(--plum)]">{{ __('No access') }}</p>
                <p class="mt-1 text-xs leading-6 text-[color:var(--muted)]">{{ __('Staff cannot see this section or make changes.') }}</p>
            </div>
            <div class="role-legend-card">
                <span class="role-legend-dot role-legend-dot--auto"></span>
                <p class="font-bold text-[color:var(--plum)]">{{ __('Allow immediately') }}</p>
                <p class="mt-1 text-xs leading-6 text-[color:var(--muted)]">{{ __('Staff can do this action right away without waiting for you.') }}</p>
            </div>
            <div class="role-legend-card">
                <span class="role-legend-dot role-legend-dot--approval"></span>
                <p class="font-bold text-[color:var(--plum)]">{{ __('Needs your approval') }}</p>
                <p class="mt-1 text-xs leading-6 text-[color:var(--muted)]">{{ __('Staff sends a request; you approve it from Approvals before it applies.') }}</p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Role permissions') }}</h2>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ __('Set access for each part of the admin panel.') }}</p>
        </div>

        @foreach ($permissions as $group => $groupPermissions)
            <div class="admin-card space-y-4">
                <h3 class="text-sm font-extrabold text-[color:var(--plum)]">{{ __('permission.group.'.$group) }}</h3>
                <div class="space-y-3">
                    @foreach ($groupPermissions as $permission)
                        @php
                            $current = old('permissions.'.$permission->code, $selected[$permission->code] ?? 'off');
                            $meta = $permissionMeta[$permission->code] ?? [
                                'title' => $permission->t('name'),
                                'help' => '',
                            ];
                            $modes = $permission->supports_approval
                                ? ['off', 'auto', 'approval']
                                : ['off', 'auto'];
                        @endphp
                        <div class="role-permission-row">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-[color:var(--plum)]">{{ $meta['title'] }}</p>
                                @if ($meta['help'] !== '')
                                    <p class="mt-0.5 text-xs leading-6 text-[color:var(--muted)]">{{ $meta['help'] }}</p>
                                @endif
                            </div>
                            <div class="role-seg" role="radiogroup" aria-label="{{ $meta['title'] }}">
                                @foreach ($modes as $mode)
                                    @php
                                        $label = match ($mode) {
                                            'auto' => __('Allow immediately'),
                                            'approval' => __('Needs your approval'),
                                            default => __('No access'),
                                        };
                                    @endphp
                                    <label class="role-seg__option {{ $current === $mode ? 'is-active' : '' }}">
                                        <input
                                            type="radio"
                                            name="permissions[{{ $permission->code }}]"
                                            value="{{ $mode }}"
                                            class="sr-only"
                                            @checked($current === $mode)
                                            onchange="this.closest('.role-seg').querySelectorAll('.role-seg__option').forEach(el => el.classList.remove('is-active')); this.closest('.role-seg__option').classList.add('is-active');"
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="role-form-actions">
        <button type="submit" class="btn-primary">{{ __('Save role') }}</button>
        <a href="{{ route('admin.roles.index') }}" class="btn-ghost">{{ __('Cancel') }}</a>
        @if ($role && ! $role->is_system && (int) ($role->users_count ?? $role->users()->count()) === 0)
            <button
                type="submit"
                form="delete-role-form"
                class="ms-auto text-sm font-bold text-rose-600 hover:underline"
                onclick="return confirm('{{ __('Delete this role?') }}')"
            >{{ __('Delete') }}</button>
        @endif
    </div>
</form>

@if ($role && ! $role->is_system && (int) ($role->users_count ?? $role->users()->count()) === 0)
    <form id="delete-role-form" action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
