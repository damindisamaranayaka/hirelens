<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
            'role'                  => 'seeker',
        ], $overrides);
    }

    public function test_user_can_register(): void
    {
        $this->postJson('/api/register', $this->registerPayload())
            ->assertCreated()
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_password_is_hashed_in_database(): void
    {
        $this->postJson('/api/register', $this->registerPayload());

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals('Password123', $user->password);
    }

    public function test_response_never_contains_password(): void
    {
        $this->postJson('/api/register', $this->registerPayload())
            ->assertJsonMissingPath('user.password');
    }

    public function test_cannot_register_as_admin(): void
    {
        $this->postJson('/api/register', $this->registerPayload(['role' => 'admin']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/register', $this->registerPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->postJson('/api/register', $this->registerPayload(['password_confirmation' => 'Different123']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email'    => 'login@example.com',
            'password' => 'Password123',
        ]);

        $this->postJson('/api/login', [
            'email'    => 'login@example.com',
            'password' => 'Password123',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'login@example.com']);

        $this->postJson('/api/login', [
            'email'    => 'login@example.com',
            'password' => 'WrongPassword1',
        ])->assertStatus(401); // use 422 here if you haven't changed the controller yet
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_returns_current_user_with_valid_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_logout_deletes_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => 'x@example.com', 'password' => 'wrong']);
        }

        $this->postJson('/api/login', ['email' => 'x@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
    }
}