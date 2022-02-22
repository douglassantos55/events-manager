<?php

namespace Test\Feature\Member;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EditMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $user = User::factory()->create();

        $response = $this->get(route('members.edit', ['member' => $user]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.edit', ['member' => $user]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->hasMembers()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_MEMBER],
        ]);

        Auth::login($user);
        $response = $this->get(route('members.edit', ['member' => $user->members->first()]));

        $response->assertInertia(
            fn (AssertableInertia $page) =>
            $page->component('Member/Form')->has('member')
        );
    }

    public function test_cannot_edit_other_users_members()
    {
        $other = User::factory()->hasMembers()->create();

        $user = User::factory()->hasMembers()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.edit', ['member' => $other->members->first()]));
        $response->assertForbidden();
    }

    public function test_member_can_edit_parents_members()
    {
        $parent = User::factory()->hasMembers()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_MEMBER],
        ]);

        Auth::login($user);
        $response = $this->get(route('members.edit', ['member' => $parent->members->first()]));

        $response->assertInertia(
            fn (AssertableInertia $page) =>
            $page->component('Member/Form')->has('member')
        );
    }

    public function test_update_needs_permission()
    {
        $user = User::factory()->hasMembers()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->post(route('members.update', ['member' => $user->members->first()]));
        $response->assertForbidden();
    }

    public function test_cannot_update_other_users_members()
    {
        $other = User::factory()->hasMembers()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->post(route('members.update', ['member' => $other->members->first()]));
        $response->assertForbidden();
    }

    public function test_member_can_update_parents_members()
    {
        $parent = User::factory()->hasMembers()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->post(route('members.update', ['member' => $parent->members->first()]));
        $response->assertInvalid();
    }

    public function test_update_validation()
    {
        $user = User::factory()->hasMembers()->create();
        $user->role = Role::factory()->for($user)->create();

        Auth::login($user);

        $member = $user->members->first();
        $route = route('members.update', ['member' => $member]);

        $response = $this->post($route, [
            'name' => '',
            'role_id' => Role::factory()->forUser()->create(),
        ]);

        $response->assertInvalid([
            'name' => 'The name field is required.',
            'role_id' => 'The selected role id is invalid.',
        ]);
    }

    public function test_updates_successfully()
    {
        $user = User::factory()->hasMembers()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create();

        Auth::login($user);

        $member = $user->members->first();
        $route = route('members.update', ['member' => $member]);

        $response = $this->post($route, [
            'name' => 'John Doe',
            'role_id' => $user->roles->last()->id,
        ]);

        $member->refresh();

        $this->assertEquals('John Doe', $member->name);
        $this->assertEquals($user->roles->last()->id, $member->role_id);
        $response->assertRedirect(route('members.index'));
    }
}
