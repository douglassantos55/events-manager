<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permission::VIEW_ROLES->value, Role::class);

        return inertia('Role/Index', [
            'roles' => $request->user()->roles,
            'create_url' => route('roles.create'),
        ]);
    }

    public function create()
    {
        $this->authorize(Permission::CREATE_ROLE->value, Role::class);

        return inertia('Role/Form', [
            'permissions' => Permission::cases(),
            'save_url' => route('roles.store'),
        ]);
    }

    public function edit(Role $role)
    {
        $this->authorize(Permission::EDIT_ROLE->value, $role);

        return inertia('Role/Form', [
            'role' => $role,
            'permissions' => Permission::cases(),
            'save_url' => route('roles.update', ['role' => $role]),
            'destroy_url' => route('roles.destroy', ['role' => $role]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize(Permission::CREATE_ROLE->value, Role::class);

        $validated = $request->validate([
            'name' => ['required'],
            'permissions' => ['required', 'array'],
        ]);

        $request->user()->roles()->create($validated);
        return redirect()->route('roles.index');
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize(Permission::EDIT_ROLE->value, $role);

        $validated = $request->validate([
            'name' => ['required'],
            'permissions' => ['required', 'array'],
        ]);

        $role->update($validated);
        return redirect()->route('roles.index');
    }

    public function destroy(Role $role)
    {
        $this->authorize(Permission::DELETE_ROLE->value, $role);

        $role->delete();
        return redirect()->route('roles.index');
    }
}
