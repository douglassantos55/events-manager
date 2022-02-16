<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EventTest extends TestCase
{
    public function test_needs_authentication()
    {
        $response = $this->get(route('events.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_permission()
    {
        $user = User::first();
        Auth::login($user);

        $user->role = 'editor';

        $response = $this->get(route('events.create'));
        $response->assertForbidden();
    }

    public function test_respects_limit()
    {
        $user = User::first();
        Auth::login($user);

        $user->role = 'assistant';
        $user->max_events = 0;

        $response = $this->get(route('events.create'));
        $response->assertForbidden();
    }

    public function test_passes_permission()
    {
        $user = User::first();
        $user->max_events = 1;
        $user->role = 'assistant';

        Auth::login($user);

        $response = $this->get(route('events.create'));
        $response->assertStatus(200);
    }

    public function test_validation()
    {
        $user = User::first();
        $user->role = 'assistant';
        $user->max_events = 1;

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

    public function test_creates_successfully()
    {
        $user = User::first();
        $user->role = 'assistant';
        $user->max_events = 1;

        Auth::login($user);

        $response = $this->post(route('events.store'), [
            'title' => 'My test event',
            'attending_date' => '2022-02-16',
            'budget' => '3350.00',
            'users' => [1],
        ]);

        $response->assertRedirect(route('events.index'));
    }
}
