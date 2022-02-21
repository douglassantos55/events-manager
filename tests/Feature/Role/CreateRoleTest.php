<?php

namespace Test\Feature\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $response = $this->get(route('roles.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.create'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_ROLE],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.create'));
        $response->assertOk();
    }

    public function test_store_needs_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->post(route('roles.store'), [
            'name' => 'test',
            'permissions' => ['foo', 'bar'],
        ]);

        $response->assertForbidden();
    }

    public function test_validation()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_ROLE],
        ]);

        Auth::login($user);

        $response = $this->post(route('roles.store'), [
            'name' => '',
            'permissions' => [],
        ]);

        $response->assertInvalid([
            'name' => 'The name field is required.',
            'permissions' => 'The permissions field is required.',
        ]);
    }

    public function test_creates_successfully()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_ROLE],
        ]);

        Auth::login($user);

        $response = $this->post(route('roles.store'), [
            'name' => 'test',
            'permissions' => ['foo', 'bar'],
        ]);

        $this->assertInstanceOf(Role::class, Role::where('name', 'test')->first());
        $response->assertRedirect(route('roles.index'));
    }

    public function test_respects_plan_limit()
    {
        $user = User::factory()->hasRoles(9)->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_ROLE],
        ]);

        Auth::login($user);

        $response = $this->post(route('roles.store'), [
            'name' => 'test',
            'permissions' => ['foo', 'bar'],
        ]);

        $response->assertForbidden();
    }
}
