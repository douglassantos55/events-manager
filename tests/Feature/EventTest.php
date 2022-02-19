<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->for(Role::factory()->create())->create();
    }

    public function test_needs_authentication()
    {
        $response = $this->get(route('events.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_list_needs_authorization()
    {
        Auth::login($this->user);

        $this->user->role = new Role([
            'permissions' => collect([]),
        ]);

        $response = $this->get(route('events.index'));
        $response->assertForbidden();
    }

    public function test_list_passes_authorization()
    {
        Auth::login($this->user);

        $this->user->role = new Role([
            'permissions' => collect([
                Permission::VIEW_EVENTS,
            ]),
        ]);

        $response = $this->get(route('events.index'));
        $response->assertStatus(200);
    }

    public function test_needs_permission()
    {
        Auth::login($this->user);

        $this->user->role = new Role([
            'permissions' => collect([
                Permission::VIEW_EVENTS,
            ])
        ]);

        $response = $this->get(route('events.create'));
        $response->assertForbidden();
    }

    public function test_respects_limit()
    {
        Auth::login($this->user);
        $this->user->events = Event::factory(11)->for($this->user)->create();

        $response = $this->get(route('events.create'));
        $response->assertForbidden();
    }

    public function test_passes_permission()
    {
        Auth::login($this->user);

        $response = $this->get(route('events.create'));
        $response->assertStatus(200);
    }

    public function test_validation()
    {
        Auth::login($this->user);

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
        Auth::login($this->user);

        Event::create([
            'title' => 'Testing',
            'budget' => 1000,
            'attending_date' => '2022-02-16 21:42:00',
            'user_id' => $this->user->id,
        ]);

        $response = $this->post(route('events.store'), [
            'title' => 'Testing',
            'attending_date' => '2022-02-16 03:00:00',
            'budget' => '3350.00',
        ]);

        $response->assertInvalid([
            'title' => 'The title has already been taken.',
        ]);
    }

    public function test_creates_successfully()
    {
        Auth::login($this->user);

        $response = $this->post(route('events.store'), [
            'title' => 'My test event',
            'attending_date' => '2022-02-16T03:00:00.000Z',
            'budget' => '3350.00',
            'users' => [1],
        ]);

        $response->assertRedirect(route('events.index'));
    }
}
