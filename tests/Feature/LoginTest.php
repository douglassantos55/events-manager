<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_logout()
    {
        Auth::login($this->user);
        $response = $this->get(route('logout'));

        $this->assertFalse(Auth::check());
        $response->assertRedirect(route('login'));
    }

    public function test_redirects_if_logged_in()
    {
        Auth::login($this->user);
        $response = $this->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_successfully()
    {
        $response = $this->post(route('login'), [
            'email' => 'douglas@email.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_empty_password()
    {
        $response = $this->post(route('login'), [
            'email' => 'douglas@email.com',
            'password' => '',
        ]);

        $response->assertInvalid([
            'password' => 'The password field is required.',
        ]);
    }

    public function test_empty_email()
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertInvalid([
            'email' => 'The email field is required.',
        ]);
    }

    public function test_invalid_email()
    {
        $response = $this->post(route('login'), [
            'email' => 'douglas',
            'password' => 'password',
        ]);

        $response->assertInvalid([
            'email' => 'The email must be a valid email address.',
        ]);
    }

    public function test_wrong_password()
    {
        $response = $this->post(route('login'), [
            'email' => 'douglas@email.com',
            'password' => '1234',
        ]);

        $response->assertInvalid([
            'email' => 'The provided credentials do not match our records',
        ]);
    }

    public function test_wrong_email()
    {
        $response = $this->post(route('login'), [
            'email' => 'douglas@domain.com',
            'password' => 'password',
        ]);

        $response->assertInvalid([
            'email' => 'The provided credentials do not match our records',
        ]);
    }
}
