<?php

namespace Tests\Feature\Event\Agenda;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateAgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $response = $this->post(route('agenda.attach', ['event' => $event]));

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $response = $this->post(route('agenda.attach', ['event' => $event]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_AGENDA],
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $response = $this->post(route('agenda.attach', ['event' => $event]), [
            'date' => '2022-03-25',
            'time' => '14:00',
            'title' => 'Choose your destiny',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_create_for_other_users_events()
    {
        $event = Event::factory()->forUser()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_AGENDA],
        ]);

        Auth::login($user);

        $response = $this->post(route('agenda.attach', ['event' => $event]), [
            'date' => '2022-03-25',
            'time' => '14:00',
            'title' => 'Choose your destiny',
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_create_for_parents_events()
    {
        $parent = User::factory()->create();
        $event = Event::factory()->for($parent)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_AGENDA],
        ]);

        Auth::login($user);

        $response = $this->post(route('agenda.attach', ['event' => $event]), [
            'date' => '2022-03-25',
            'time' => '14:00',
            'title' => 'Choose your destiny',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_AGENDA],
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $response = $this->post(route('agenda.attach', ['event' => $event]), [
            'date' => '2022-03-35',
            'time' => '25:00',
            'title' => '',
        ]);

        $response->assertInvalid([
            'date' => 'The date is not a valid date.',
            'time' => 'The time does not match the format H:i.',
            'title' => 'The title field is required.',
        ]);
    }

    public function test_creates_sucessfully()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_AGENDA],
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $response = $this->post(route('agenda.attach', ['event' => $event]), [
            'date' => '2022-03-25',
            'time' => '22:00',
            'title' => 'Choose your fate',
        ]);

        $event->refresh();
        $this->assertEquals(1, $event->agenda->count());
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }
}
