<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize(Permission::VIEW_ROLES->value, Role::class);

        return inertia('Roles', [
            'roles' => Role::all(),
            'create_url' => route('roles.create'),
        ]);
    }

    public function create()
    {
        $this->authorize(Permission::CREATE_ROLE->value, Role::class);

        return inertia('NewRole', [
            'permissions' => Permission::cases(),
            'save_url' => route('roles.save'),
        ]);
    }

    public function edit(Role $role)
    {
        return inertia('NewRole', [
            'role' => $role,
            'permissions' => Permission::cases(),
            'save_url' => route('roles.save', ['role' => $role]),
            'destroy_url' => route('roles.destroy', ['role' => $role]),
        ]);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index');
    }

    public function save(Request $request, Role $role = null)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'permissions' => ['required', 'array'],
        ]);

        if (!is_null($role)) {
            $role->update($validated);
        } else {
            Role::create($validated);
        }

        return redirect()->route('roles.index');
    }
}
