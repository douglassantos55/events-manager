<?php

namespace Test\Feature\Assignee;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RemoveAssigneeTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $event->assignees->first()
        ]);

        $response = $this->delete($route);
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [],
        ]);

        Auth::login($event->user);

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $event->assignees->first()
        ]);

        $response = $this->delete($route);
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $event->assignees->first()
        ]);

        $response = $this->delete($route);
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_user_who_is_not_assigned()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $event->user,
        ]);

        $response = $this->delete($route);
        $response->assertNotFound();
    }

    public function test_member_can_remove_assignee()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $user = User::factory()->for($event->user, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $event->assignees->first(),
        ]);

        $response = $this->delete($route);
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_assignee_from_other_users_events()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $event->assignees->first(),
        ]);

        $response = $this->delete($route);
        $response->assertForbidden();
    }

    public function test_removes_assignee_successfully()
    {
        $event = Event::factory()->forUser()->hasAssignees()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);
        $assignee = $event->assignees->first();

        $route = route('assignees.remove', [
            'event' => $event,
            'assignee' => $assignee,
        ]);

        $response = $this->delete($route);
        $response->assertRedirect(route('events.view', ['event' => $event]));

        $this->assertModelExists($assignee);
        $this->assertFalse($event->refresh()->assignees->contains($assignee));
    }
}
