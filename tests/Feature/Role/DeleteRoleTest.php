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
        $role = Role::factory()->create();

        $response = $this->get(route('roles.destroy', ['role' => $role->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $role = Role::factory()->create(['permissions' => []]);
        Auth::login(User::factory()->for($role)->create());

        $response = $this->get(route('roles.destroy', ['role' => $role->id]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $role = Role::factory()->create(['permissions' => [Permission::DELETE_ROLE->value]]);
        Auth::login(User::factory()->for($role)->create());

        $response = $this->get(route('roles.destroy', ['role' => $role->id]));

        $response->assertRedirect(route('roles.index'));
        $this->assertModelMissing($role);
    }
}
