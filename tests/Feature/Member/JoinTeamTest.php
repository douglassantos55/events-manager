<?php

namespace Test\Feature\Member;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JoinTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_no_authentication()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->get(route('members.join', ['member' => $user]));
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('Member/Join'));
    }

    public function test_redirects_if_email_is_verified()
    {
        $user = User::factory()->create();

        $response = $this->get(route('members.join', ['member' => $user]));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('members.save', ['member' => $user]));
        $response->assertRedirect(route('login'));
    }

    public function test_prefills_form()
    {
        $user = User::factory()->unverified()->create(['name' => 'John Doe']);
        $response = $this->get(route('members.join', ['member' => $user]));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Member/Join')
                ->where('member.name', 'John Doe')
        );
    }

    public function test_updates_successfully()
    {
        $user = User::factory()->unverified()->create(['name' => 'Some name']);

        $response = $this->post(route('members.save', ['member' => $user]), [
            'name' => 'Jane Doe',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user->refresh();

        $this->assertEquals('Jane Doe', $user->name);
        $this->assertNotEmpty($user->email_verified_at);

        $this->assertTrue(Auth::check());
        $response->assertRedirect(route('dashboard'));
    }
}
