<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_permission()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => []
        ]);

        Auth::login($user);

        $response = $this->get(route('events.create'));
        $response->assertForbidden();
    }

    public function test_passes_permission()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_EVENT]
        ]);

        Auth::login($user);

        $response = $this->get(route('events.create'));
        $response->assertStatus(200);
    }

    public function test_respects_limit()
    {
        $user = User::factory()->hasEvents(11)->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_EVENT]
        ]);

        Auth::login($user);

        $response = $this->get(route('events.create'));
        $response->assertForbidden();
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_EVENT]
        ]);

        Auth::login($user);

        $response = $this->post(route('events.store'), [
            'title' => '',
            'attending_date' => '2020-20-20',
            'budget' => '10350,00',
        ]);

        $response->assertInvalid([
            'title' => 'The title field is required.',
            'attending_date' => 'The attending date is not a valid date.',
            'budget' => 'The budget must be a number.',
        ]);
    }

    public function test_unique_title()
    {
        $user = User::factory()->hasEvents(1)->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_EVENT]
        ]);

        Auth::login($user);

        $response = $this->post(route('events.store'), [
            'title' => $user->events()->first()->title,
            'attending_date' => '2022-02-16 03:00:00',
            'budget' => '3350.00',
        ]);

        $response->assertInvalid([
            'title' => 'The title has already been taken.',
        ]);
    }

    public function test_creates_successfully()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::CREATE_EVENT]
        ]);

        Auth::login($user);

        $response = $this->post(route('events.store'), [
            'title' => 'My test event',
            'attending_date' => '2022-02-16T03:00:00.000Z',
            'budget' => '3350.00',
            'users' => [1],
        ]);

        $response->assertRedirect(route('events.index'));
    }
}
