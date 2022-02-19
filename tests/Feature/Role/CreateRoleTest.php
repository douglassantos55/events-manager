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
        Auth::login(User::factory()->forRole(['permissions' => []])->create());

        $response = $this->get(route('roles.create'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        Auth::login(User::factory()->forRole(['permissions' => [Permission::CREATE_ROLE]])->create());

        $response = $this->get(route('roles.create'));
        $response->assertOk();
    }

    public function test_validation()
    {
        Auth::login(User::factory()->forRole(['permissions' => [Permission::CREATE_ROLE]])->create());

        $response = $this->post(route('roles.save'), [
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
        Auth::login(User::factory()->forRole(['permissions' => [Permission::CREATE_ROLE]])->create());

        $response = $this->post(route('roles.save'), [
            'name' => 'test',
            'permissions' => ['foo', 'bar'],
        ]);

        $this->assertInstanceOf(Role::class, Role::where('name', 'test')->first());
        $response->assertRedirect(route('roles.index'));
    }
}
