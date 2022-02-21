<?php

namespace Test\Feature\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $role = Role::factory()->forUser()->create();

        $response = $this->get(route('roles.destroy', ['role' => $role->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $role = Role::factory()->forUser()->create(['permissions' => []]);
        Auth::login(User::factory()->for($role)->create());

        $response = $this->get(route('roles.destroy', ['role' => $role->id]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create(['permissions' => [Permission::DELETE_ROLE]]);

        Auth::login($user);

        $response = $this->get(route('roles.destroy', ['role' => $user->role->id]));

        $response->assertRedirect(route('roles.index'));
        $this->assertModelMissing($user->role);
    }

    public function test_cannot_delete_other_users_roles()
    {
        $roles = Role::factory(3)->forUser()->create();

        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_ROLE],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.destroy', ['role' => $roles[0]->id]));
        $response->assertForbidden();
    }

    public function test_member_can_delete_parents_roles()
    {
        $parent = User::factory()->hasRoles()->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $user->role = $parent->roles()->first();
        Auth::login($user);

        $response = $this->get(route('roles.destroy', ['role' => $parent->roles()->first()->id]));
        $response->assertRedirect(route('roles.index'));
        $this->assertModelMissing($user->role);
    }
}
