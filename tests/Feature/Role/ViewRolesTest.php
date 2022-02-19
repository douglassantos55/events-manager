<?php

namespace Test\Feature\Role;

use App\Models\Permission;
use App\Models\Plan;
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
        Auth::login(User::factory()->forRole(['permissions' => []])->create());

        $response = $this->get(route('roles.index'));
        $response->assertForbidden();
    }

    public function test_plan_authorization()
    {
        $user = User::factory()->forRole()->create();

        $user->plan = new Plan($user, [
            Permission::VIEW_ROLES->value => false,
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.index'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        Auth::login(User::factory()->forRole(['permissions' => [Permission::VIEW_ROLES]])->create());

        $response = $this->get(route('roles.index'));
        $response->assertOk();
    }

    public function test_lists_roles()
    {
        Auth::login(User::factory()->forRole(['permissions' => [Permission::VIEW_ROLES]])->create());

        $response = $this->get(route('roles.index'));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('roles'));
    }
}
