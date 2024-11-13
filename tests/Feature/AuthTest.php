<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'data' => ['access_token', 'token_type']]);
});

test('user cannot register with invalid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
    ]);

    $response->assertStatus(422);
});

test('user can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'data' => ['access_token', 'token_type']]);
});

test('user cannot log in with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'invalidpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

test('authenticated user can log out', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully logged out']);
});

test('user can request password reset link', function () {
    $this->withOutExceptionHandling();

    User::factory()->create(['email' => 'reset@example.com']);

    $response = $this->postJson('/api/v1/auth/password/email', [
        'email' => 'reset@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('passwords.sent')]);
});

test('user can reset password with valid token', function () {
    $user = User::factory()->create(['email' => 'reset@example.com']);
    $token = Password::createToken($user);

    $response = $this->postJson('/api/v1/auth/password/reset', [
        'email' => 'reset@example.com',
        'token' => $token,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('passwords.reset')]);
});
