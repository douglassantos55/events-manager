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
use Inertia\Testing\AssertableInertia;
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

        $response->assertInertia(
            fn (AssertableInertia $page) =>
            $page->component('Member/Invite')->has('roles')
        );
    }

    public function test_store_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->post(route('members.store'), [
            'name' => 'john doe',
            'email' => 'johndoe@domain.com',
            'role_id' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $response = $this->post(route('members.store'), [
            'name' => '',
            'role_id' => 6999,
            'email' => 'email#domain.com',
        ]);

        $response->assertInvalid([
            'name' => 'The name field is required.',
            'role_id' => 'The selected role id is invalid.',
            'email' => 'The email must be a valid email address.',
        ]);
    }

    public function test_member_is_assigned_to_members_parent()
    {
        $parent = User::factory()->hasMembers()->hasRoles()->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $this->post(route('members.store'), [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
            'role_id' => $parent->roles->first()->id,
        ]);

        $invited = User::where('email', 'johndoe@email.com')->first();

        $this->assertModelExists($invited);
        $this->assertTrue($parent->members->contains($invited));
    }

    public function test_invites_successfully()
    {
        Mail::fake();

        $user = User::factory()->hasRoles()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::INVITE_MEMBER],
        ]);

        Auth::login($user);

        $this->post(route('members.store'), [
            'name' => 'John Doe',
            'email' => 'johndoe@email.com',
            'role_id' => $user->roles->first()->id,
        ]);

        $invited = User::where('email', 'johndoe@email.com')->first();

        Mail::assertSent(MemberInvitation::class);

        $this->assertModelExists($invited);
        $this->assertEquals($invited->role_id, $user->roles->first()->id);
    }
}
