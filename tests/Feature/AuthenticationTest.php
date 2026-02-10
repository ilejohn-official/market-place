<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_register_login_profile_and_logout(): void
    {
        $registerResponse = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $token = $loginResponse->json('data.token');

        $profileResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'john@example.com');

        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
