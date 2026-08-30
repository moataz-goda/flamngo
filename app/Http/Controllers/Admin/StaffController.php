<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::query()
            ->where('shop_id', current_shop()->id)
            ->where('is_admin', true)
            ->with('roles')
            ->orderByRaw("role = 'owner' DESC")
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', [
            'staff' => $staff,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'assigned_role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($q) => $q->where('shop_id', current_shop()->id)),
            ],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => true,
            'role' => User::ROLE_STAFF,
            'shop_id' => current_shop()->id,
        ]);

        // Default: no privileges unless a role is explicitly assigned.
        if (! empty($data['assigned_role_id'])) {
            $user->syncAssignedRole((int) $data['assigned_role_id']);
        }

        return back()->with('success', __('Staff member added.'));
    }

    public function update(Request $request, int $staff): RedirectResponse
    {
        $user = User::query()
            ->where('shop_id', current_shop()->id)
            ->whereKey($staff)
            ->firstOrFail();

        if ($user->isOwner()) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'password' => ['nullable', 'string', 'min:8'],
            ]);

            $user->name = $data['name'];
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            return back()->with('success', __('Staff member updated.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:8'],
            'assigned_role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($q) => $q->where('shop_id', current_shop()->id)),
            ],
        ]);

        $user->name = $data['name'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $user->syncAssignedRole(
            isset($data['assigned_role_id']) ? (int) $data['assigned_role_id'] : null
        );

        return back()->with('success', __('Staff member updated.'));
    }

    public function destroy(int $staff): RedirectResponse
    {
        $user = User::query()
            ->where('shop_id', current_shop()->id)
            ->whereKey($staff)
            ->firstOrFail();

        if ($user->id === auth()->id()) {
            return back()->with('error', __('You cannot delete yourself.'));
        }

        if ($user->isOwner() && User::query()->where('shop_id', current_shop()->id)->where('role', User::ROLE_OWNER)->count() <= 1) {
            return back()->with('error', __('At least one owner is required.'));
        }

        $user->roles()->detach();
        $user->delete();

        return back()->with('success', __('Staff member removed.'));
    }
}
