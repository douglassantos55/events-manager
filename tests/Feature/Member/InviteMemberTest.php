<?php

namespace Test\Feature\Member;

use App\Mail\MemberInvitation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $response = $this->get(route('members.invite'));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.invite'));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->get(route('members.invite'));
        $response->assertInertia();
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->post(route('members.store'), [
            'email' => 'email#domain.com',
        ]);
        $response->assertInvalid();
    }

    public function test_member_is_assigned_to_members_parent()
    {
        $parent = User::factory()->hasMembers()->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $this->post(route('members.store'), [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
        ]);

        $invited = User::where('email', 'johndoe@email.com')->first();

        $this->assertModelExists($invited);
        $this->assertTrue($parent->members->contains($invited));
    }

    public function test_invites_successfully()
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $this->post(route('members.store'), [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
        ]);

        $invited = User::where('email', 'johndoe@email.com')->first();

        $this->assertModelExists($invited);
        Mail::assertSent(MemberInvitation::class);
    }
}
