<?php

namespace Test\Feature\Event;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ViewEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();

        $response = $this->get(route('events.view', ['event' => $event->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $event = Event::factory()->forUser()->create();

        $user = User::factory()->make();
        $user->role = Role::factory()->for($user)->make(['permissions' => []]);

        Auth::login($user);

        $response = $this->get(route('events.view', ['event' => $event->id]));
        $response->assertForbidden();
    }

    public function test_passes_permission()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_EVENT]
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $response = $this->get(route('events.view', ['event' => $event->id]));
        $response->assertOk();
    }

    public function test_cannot_view_other_users_events()
    {
        $event = Event::factory()->forUser()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_EVENT]
        ]);

        Auth::login($user);

        $response = $this->get(route('events.view', ['event' => $event->id]));
        $response->assertForbidden();
    }

    public function test_member_can_view_parents_events()
    {
        $parent = User::factory()->create();
        $event = Event::factory()->for($parent)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($parent)->create([
            'permissions' => [Permission::VIEW_EVENT]
        ]);

        Auth::login($user);

        $response = $this->get(route('events.view', ['event' => $event->id]));
        $response->assertOk();
    }
}
