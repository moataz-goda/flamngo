<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->withCount(['users', 'permissions'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => null,
            'permissions' => Permission::query()->orderBy('sort_order')->get()->groupBy('group'),
            'selected' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $role = Role::query()->create([
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'is_system' => false,
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', __('Role created.'));
    }

    public function edit(int $role): View
    {
        $role = Role::query()->with(['permissions'])->withCount('users')->findOrFail($role);

        $selected = [];
        foreach ($role->permissions as $permission) {
            $selected[$permission->code] = $permission->pivot->mode ?? PermissionCatalog::MODE_AUTO;
        }

        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('sort_order')->get()->groupBy('group'),
            'selected' => $selected,
        ]);
    }

    public function update(Request $request, int $role): RedirectResponse
    {
        $role = Role::query()->findOrFail($role);
        $data = $this->validated($request, $role->id);

        $role->update([
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', __('Role updated.'));
    }

    public function destroy(int $role): RedirectResponse
    {
        $role = Role::query()->withCount('users')->findOrFail($role);

        if ($role->is_system) {
            return back()->with('error', __('Cannot delete the default role.'));
        }

        if ((int) $role->users_count > 0) {
            return back()->with('error', __('Cannot delete a role that is assigned to staff.'));
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', __('Role deleted.'));
    }

    protected function validated(Request $request, ?int $roleId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('shop_id', current_shop()->id))
                    ->ignore($roleId),
            ],
            'name_en' => ['nullable', 'string', 'max:120'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'in:off,auto,approval'],
        ]);
    }

    /**
     * @param  array<string, string>  $permissionsInput
     */
    protected function syncPermissions(Role $role, array $permissionsInput): void
    {
        $all = Permission::query()->get()->keyBy('code');
        $sync = [];

        foreach ($permissionsInput as $code => $mode) {
            if ($mode === 'off' || $mode === null || $mode === '') {
                continue;
            }

            $permission = $all->get($code);
            if (! $permission) {
                continue;
            }

            $syncMode = $permission->supports_approval && $mode === PermissionCatalog::MODE_APPROVAL
                ? PermissionCatalog::MODE_APPROVAL
                : PermissionCatalog::MODE_AUTO;

            $sync[$permission->id] = ['mode' => $syncMode];
        }

        $role->permissions()->sync($sync);
    }
}
