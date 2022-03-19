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

class RemoveGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        $response = $this->delete(route('guests.delete', ['guest' => $guest]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->delete(route('guests.delete', ['guest' => $guest]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_GUEST],
        ]);

        $event = Event::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create();

        Auth::login($user);

        $response = $this->delete(route('guests.delete', ['guest' => $guest]));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_from_other_users_events()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_GUEST],
        ]);

        Auth::login($user);

        $response = $this->delete(route('guests.delete', ['guest' => $guest]));
        $response->assertForbidden();
    }

    public function test_member_can_remove_from_parents_events()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $guest = Guest::factory()->for($event)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_GUEST],
        ]);

        Auth::login($user);

        $response = $this->delete(route('guests.delete', ['guest' => $guest]));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_removes_successfully()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $guest = Guest::factory()->for($event)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::DELETE_GUEST],
        ]);

        Auth::login($user);

        $this->delete(route('guests.delete', ['guest' => $guest]));
        $this->assertModelMissing($guest);
    }
}
