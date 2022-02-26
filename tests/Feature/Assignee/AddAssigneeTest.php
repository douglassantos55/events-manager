<?php

namespace Test\Feature\Assignee;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AddAssigneeTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser()->create();

        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $user,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $event = Event::factory()->forUser()->create();

        $event->user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($event->user);

        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $user,
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->hasMembers()->create();
        $event = Event::factory()->for($user)->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $user->members->first()->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_attach_to_other_users_events()
    {
        $other = User::factory()->hasEvents()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);
        $event = $other->events->first();

        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $user,
        ]);
        $response->assertForbidden();
    }

    public function test_cannot_attach_user_that_is_not_member()
    {
        $user = User::factory()->hasEvents()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);
        $event = $user->events->first();

        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => User::factory()->create()->id,
        ]);

        $response->assertInvalid([
            'assignee' => 'The selected assignee is invalid.',
        ]);
    }

    public function test_member_can_attach_to_parents_events()
    {
        $parent = User::factory()->hasMembers()->hasEvents()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $event = $parent->events->first();
        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $parent->members->first()->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event->id]));
        $this->assertTrue($event->refresh()->assignees->contains($parent->members->first()));
    }

    public function test_attachs_successfully()
    {
        $user = User::factory()->hasMembers()->hasEvents()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $event = $user->events->first();
        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $user->members->first()->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event->id]));
        $this->assertTrue($event->refresh()->assignees->contains($user->members->first()));
    }

    public function test_cannot_attach_other_users_members()
    {
        $other = User::factory()->hasMembers()->create();

        $user = User::factory()->hasEvents()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $event = $user->events->first();
        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $other->members->first()->id,
        ]);

        $response->assertInvalid([
            'assignee' => 'The selected assignee is invalid.',
        ]);
    }

    public function test_cannot_assign_pending_member()
    {
        $user = User::factory()->hasEvents()->create();
        $member = User::factory()->unverified()->for($user, 'captain')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $event = $user->events->first();
        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $member->id,
        ]);

        $response->assertInvalid([
            'assignee' => 'The selected assignee is invalid.',
        ]);
    }

    public function test_ignores_duplicated_assignee()
    {
        $user = User::factory()->hasMembers()->hasEvents()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $event = $user->events->first();
        $member = $user->members->first();
        $event->assignees()->attach($member);

        $response = $this->post(route('assignees.attach', ['event' => $event]), [
            'assignee' => $member->id,
        ]);

        $this->assertCount(1, $event->refresh()->assignees);
        $this->assertTrue($event->refresh()->assignees->contains($member));
        $response->assertRedirect(route('events.view', ['event' => $event->id]));
    }
}
