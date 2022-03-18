<?php

namespace Tests\Feature\Event\Guest;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EditGuestTests extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        $response = $this->put(route('guests.update', ['guest' => $guest]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        $event = Event::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create();

        Auth::login($user);

        $response = $this->put(route('guests.update', ['guest' => $guest]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_GUEST],
        ]);

        $event = Event::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create();

        Auth::login($user);

        $response = $this->put(route('guests.update', ['guest' => $guest]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_member_can_edit_from_parents_events()
    {
        $parent = User::factory()->create();
        $event = Event::factory()->for($parent)->create();
        $guest = Guest::factory()->for($event)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_GUEST],
        ]);

        Auth::login($user);

        $response = $this->put(route('guests.update', ['guest' => $guest]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_edit_from_other_users_events()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_GUEST],
        ]);

        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        Auth::login($user);

        $response = $this->put(route('guests.update', ['guest' => $guest]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $response->assertForbidden();
    }

    public function test_edits_successfully()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_GUEST],
        ]);

        $event = Event::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create();

        Auth::login($user);

        $this->put(route('guests.update', ['guest' => $guest]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $guest->refresh();
        $this->assertEquals('Grandma Lola', $guest->name);
        $this->assertEquals(Guest::STATUS_CONFIRMED, $guest->status);
    }

    public function test_cannot_edit_non_existing_guest()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_GUEST],
        ]);

        Auth::login($user);

        $response = $this->put(route('guests.update', ['guest' => 42069]), [
            'name' => 'Grandma Lola',
            'status' => Guest::STATUS_CONFIRMED,
        ]);

        $response->assertNotFound();
    }
}
