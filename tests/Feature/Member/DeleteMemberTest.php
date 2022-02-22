<?php

namespace Test\Feature\Member;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DeleteMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $member = User::factory()->create();
        $response = $this->get(route('members.destroy', ['member' => $member]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->hasMembers()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.destroy', [
            'member' => $user->members->first()
        ]));

        $response->assertForbidden();
    }

    public function test_deletes_successfully()
    {
        $user = User::factory()->hasMembers()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_MEMBER],
        ]);

        Auth::login($user);

        $member = $user->members->first();
        $response = $this->get(route('members.destroy', [
            'member' => $member
        ]));

        $this->assertModelMissing($member);
        $response->assertRedirect(route('members.index'));
    }

    public function test_cannot_delete_other_users_members()
    {
        $other = User::factory()->hasMembers()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.destroy', ['member' => $other->members->first()]));
        $response->assertForbidden();
    }

    public function test_member_can_delete_parents_members()
    {
        $parent = User::factory()->hasMembers()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($parent)->create([
            'permissions' => [Permission::DELETE_MEMBER],
        ]);

        Auth::login($user);

        $member = $parent->members->first();
        $response = $this->get(route('members.destroy', ['member' => $member]));

        $this->assertModelMissing($member);
        $response->assertRedirect(route('members.index'));
    }

    public function test_cannot_delete_yourself()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.destroy', ['member' => $user]));
        $response->assertForbidden();
    }

    public function test_cannot_delete_user_that_is_not_member()
    {
        $other = User::factory()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.destroy', ['member' => $other]));
        $response->assertForbidden();
    }
}
