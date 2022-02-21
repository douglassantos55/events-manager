<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_events_are_parents_events()
    {
        $parent = User::factory()->hasEvents(3)->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $this->assertEquals($user->events->all(), $parent->events->all());
    }

    public function test_member_roles_are_parents_roles()
    {
        $parent = User::factory()->hasRoles(3)->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $this->assertEquals($user->roles->all(), $parent->roles->all());
    }

    public function test_member_plan_is_parents_plan()
    {
        $parent = User::factory()->hasEvents(10)->create(['plan' => 'pro']);
        $user = User::factory()->for($parent, 'captain')->create(['plan' => 'premium']);

        $this->assertFalse($parent->plan->can('create-event')->allowed());
        $this->assertFalse($user->plan->can('create-event')->allowed());
    }

    public function test_member_role_is_not_parents_role()
    {
        $parent = User::factory()->hasEvents(10)->create();
        $parent->role = Role::factory()->for($parent)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create(['permissions' => []]);

        $this->assertFalse($user->role->can('create-event'));
        $this->assertTrue($parent->role->can('create-event'));
    }

    public function test_member_members_are_parents_members()
    {
        $parent = User::factory()->hasMembers(5)->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $this->assertEquals($user->members->all(), $parent->members->all());
    }
}
