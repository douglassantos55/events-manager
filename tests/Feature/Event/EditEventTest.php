<?php

namespace Test\Feature\Event;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EditEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $response = $this->get(route('events.edit', ['event' => $event->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $event = Event::factory()->forUser()->create();
        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [],
        ]);

        Auth::login($event->user);

        $response = $this->get(route('events.edit', ['event' => $event->id]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $event = Event::factory()->forUser()->create();
        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $response = $this->get(route('events.edit', ['event' => $event->id]));
        $response->assertInertia(
            fn (AssertableInertia $page) =>
            $page->component('Event/Form')->has('event')->has('users')
        );
    }

    public function test_cannot_edit_other_users_events()
    {
        $event = Event::factory()->forUser()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $response = $this->get(route('events.edit', ['event' => $event->id]));
        $response->assertForbidden();
    }

    public function test_member_can_edit_parents_events()
    {
        $parent = User::factory()->hasEvents()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $response = $this->get(route('events.edit', ['event' => $parent->events->first()->id]));
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Event/Form')->has('event'));
    }

    public function test_update_needs_authorization()
    {
        $event = Event::factory()->forUser()->create();
        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [],
        ]);

        Auth::login($event->user);

        $response = $this->post(route('events.update', ['event' => $event->id]));
        $response->assertForbidden();
    }

    public function test_update_validation()
    {
        $event = Event::factory()->forUser()->create();
        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $response = $this->post(route('events.update', ['event' => $event->id]), [
            'title' => '',
            'attending_date' => '2022-20-10',
            'budget' => '3690,99',
        ]);

        $response->assertInvalid([
            'title' => 'The title field is required.',
            'attending_date' => 'The attending date is not a valid date.',
            'budget' => 'The budget must be a number.',
        ]);
    }

    public function test_updates_successfully()
    {
        $event = Event::factory()->forUser()->create();
        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $response = $this->post(route('events.update', ['event' => $event->id]), [
            'title' => 'some other title',
            'attending_date' => '2022-10-10T16:34:35.000Z',
            'budget' => '3690.99',
        ]);

        $this->assertEquals('some other title', $event->refresh()->title);
        $this->assertEquals('3690.99', $event->refresh()->budget);
        $this->assertEquals('2022-10-10 16:34:35', $event->refresh()->attending_date);

        $response->assertRedirect(route('events.view', ['event' => $event->id]));
    }

    public function test_member_can_update_parents_events()
    {
        $parent = User::factory()->hasEvents()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $event = $parent->events->first();
        $response = $this->post(route('events.update', ['event' => $event->id]), [
            'title' => 'foobar',
            'attending_date' => '2022-10-10T16:34:35.000Z',
            'budget' => '3690.99',
        ]);

        $this->assertEquals('foobar', $event->refresh()->title);
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_edit_non_existing_event()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($user);

        $response = $this->get(route('events.edit', 5959));
        $response->assertNotFound();

        $response = $this->post(route('events.update', 5959));
        $response->assertNotFound();
    }

    public function test_can_keep_same_title()
    {
        $event = Event::factory()->forUser()->create();
        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $response = $this->post(route('events.update', ['event' => $event->id]), [
            'title' => $event->title,
            'attending_date' => '2022-10-10T16:34:35.000Z',
            'budget' => '3690.99',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event->id]));
    }
}
