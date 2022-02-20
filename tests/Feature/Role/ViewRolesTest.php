<?php

namespace Test\Feature\Role;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

class ViewRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $response = $this->get(route('roles.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.index'));
        $response->assertForbidden();
    }

    public function test_plan_authorization()
    {
        $user = User::factory()->hasRoles()->create();

        $user->plan = new Plan($user, [
            Permission::VIEW_ROLES->value => false,
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.index'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_ROLES],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.index'));
        $response->assertOk();
    }

    public function test_lists_roles()
    {
        $users = User::factory(2)->hasRoles(3)->create();
        $users[0]->role = $users[0]->roles()->get()->random();

        Auth::login($users[0]);

        $response = $this->get(route('roles.index'));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('roles', 3));
    }
}
