<?php

namespace Test\Feature\Event;

use App\Models\Permission;
use App\Models\Plan;
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
        $user = User::factory()->forRole()->create();

        $user->plan = new Plan($user, [
            Permission::VIEW_EVENTS->value => false
        ]);

        Auth::login($user);

        $response = $this->get(route('events.index'));
        $response->assertForbidden();
    }

    public function test_needs_authorization()
    {
        Auth::login(User::factory()->forRole(['permissions' => []])->create());

        $response = $this->get(route('events.index'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        Auth::login(User::factory()->forRole()->create());

        $response = $this->get(route('events.index'));
        $response->assertStatus(200);
    }

    public function test_lists_users_events()
    {
        $user = User::factory()->forRole()->hasEvents(5)->create();
        Auth::login($user);

        $response = $this->get(route('events.index'));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('events', 5));
    }
}
