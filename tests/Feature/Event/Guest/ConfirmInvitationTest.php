<?php

namespace Test\Feature\Event\Guest;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ConfirmInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_status()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        $this->get(route('guests.confirm', ['guest' => $guest]));

        $guest->refresh();
        $this->assertEquals(Guest::STATUS_CONFIRMED, $guest->status);
    }

    public function test_redirects_to_thank_you_page()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create();

        $response = $this->get(route('guests.confirm', ['guest' => $guest]));
        $response->assertRedirect(route('guests.thanks'));
    }

    public function test_cannot_access_after_confirmation()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create(['status' => Guest::STATUS_CONFIRMED]);

        $response = $this->get(route('guests.confirm', ['guest' => $guest]));
        $response->assertNotFound();
    }

    public function test_cannot_access_after_refusing()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event)->create(['status' => Guest::STATUS_REFUSED]);

        $response = $this->get(route('guests.confirm', ['guest' => $guest]));
        $response->assertNotFound();
    }

    public function test_thank_you_page()
    {
        $response = $this->get(route('guests.thanks'));
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Guest/Thanks'));
    }
}
