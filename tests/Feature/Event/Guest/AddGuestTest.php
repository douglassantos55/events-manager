<?php

namespace Test\Feature\Event\Guest;

use App\Mail\GuestInvitation;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AddGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'friend',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($user)->create();

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'friend',
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($user)->create();

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'friend',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_member_can_invite_to_parents_events()
    {
        $parent = User::factory()->create();
        $event = Event::factory()->for($parent)->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        Auth::login($user);

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'friend',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_invite_to_other_users_events()
    {
        $event = Event::factory()->forUser()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        Auth::login($user);

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'friend',
        ]);

        $response->assertForbidden();
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => ' ',
            'email' => 'test#test.com',
            'relation' => 'fan',
        ]);

        $response->assertInvalid([
            'name' => 'The name field is required.',
            'email' => 'The email must be a valid email address.',
            'relation' => 'The selected relation is invalid.',
        ]);
    }

    public function test_404_for_non_existing_event()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        Auth::login($user);

        $response = $this->post(route('guests.invite', ['event' => '1234']), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'friend',
        ]);

        $response->assertNotFound();
    }

    public function test_invites_successfully()
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        $event = Event::factory()->for($user)->create();

        Auth::login($user);

        $this->post(route('guests.invite', ['event' => $event]), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'relation' => 'parent',
        ]);

        Mail::assertQueued(GuestInvitation::class);

        $event->refresh();
        $this->assertCount(1, $event->guests()->where('email', 'test@test.com')->get());
    }

    public function test_cannot_invite_same_email()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_GUEST],
        ]);

        $event = Event::factory()->for($user)->hasGuests()->create();
        $guest = $event->guests->first();

        Auth::login($user);

        $response = $this->post(route('guests.invite', ['event' => $event]), [
            'name' => $guest->name,
            'email' => $guest->email,
            'relation' => $guest->relation,
        ]);

        $response->assertInvalid([
            'email' => 'This email has already been invited.',
        ]);
    }

    public function test_invitation_email_has_confirmation_link()
    {
        $event = Event::factory()->forUser()->create();
        $guest = Guest::factory()->for($event, 'event')->create();

        $mailable = new GuestInvitation($guest);

        $mailable->assertSeeInHtml($guest->name);
        $mailable->assertSeeInHtml($guest->event->title);
        $mailable->assertSeeInHtml(route('guests.confirm', ['guest' => $guest]));
    }
}
