<?php

namespace Test\Feature\Member;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ViewMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $response = $this->get(route('members.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.index'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_MEMBERS],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.index'));
        $response->assertOk();
    }

    public function test_lists_users_members()
    {
        $user = User::factory()->hasMembers(5)->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_MEMBERS],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.index'));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('members', 5));
    }

    public function test_lists_parents_members()
    {
        $parent = User::factory()->hasMembers(5)->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $user->role = Role::factory()->for($parent)->create([
            'permissions' => [Permission::VIEW_MEMBERS],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.index'));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('members', 6));
    }
}
