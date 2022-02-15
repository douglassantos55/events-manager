<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase
{
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
