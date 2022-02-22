<?php

namespace Test\Feature\Event;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

class ViewEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $response = $this->get(route('events.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_plan_authorization()
    {
        $user = User::factory()->hasRoles(3)->create();

        $user->plan = new Plan($user, [
            Permission::VIEW_EVENTS->value => false,
        ]);

        Auth::login($user);

        $response = $this->get(route('events.index'));
        $response->assertForbidden();
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => []
        ]);

        Auth::login($user);

        $response = $this->get(route('events.index'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_EVENTS]
        ]);

        Auth::login($user);

        $response = $this->get(route('events.index'));
        $response->assertStatus(200);
    }

    public function test_lists_users_events()
    {
        $user = User::factory()->hasEvents(5)->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::VIEW_EVENTS]
        ]);

        Auth::login($user);
        $response = $this->get(route('events.index'));

        $response->assertInertia(
            fn (AssertableInertia $page) =>
            $page->component('Event/Index')->has('events', 5)
        );
    }
}
