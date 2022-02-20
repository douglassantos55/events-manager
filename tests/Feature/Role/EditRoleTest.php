<?php

namespace Test\Feature\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EditRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $response = $this->get(route('roles.edit', ['role' => 1]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.edit', ['role' => 1]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_ROLE],
        ]);

        Auth::login($user);

        $response = $this->get(route('roles.edit', ['role' => 1]));
        $response->assertOk();
    }

    public function test_update_needs_authorization()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->post(route('roles.update', ['role' => 1]), [
            'name' => 'test',
            'permissions' => ['create-event'],
        ]);

        $response->assertForbidden();
    }

    public function test_updates_successfully()
    {
        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_ROLE],
        ]);

        Auth::login($user);

        $response = $this->post(route('roles.update', ['role' => 1]), [
            'name' => 'test',
            'permissions' => ['create-event'],
        ]);

        $this->assertEquals('test', Role::first()->name);
        $response->assertRedirect(route('roles.index'));
    }
}
