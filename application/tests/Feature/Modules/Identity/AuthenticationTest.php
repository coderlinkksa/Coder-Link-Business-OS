<?php

namespace Tests\Feature\Modules\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'admin@coderlink.sa',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email'    => 'admin@coderlink.sa',
            'password' => 'correct-password',
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email']])
                 ->assertJsonPath('user.email', 'admin@coderlink.sa');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'admin@coderlink.sa',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email'    => 'admin@coderlink.sa',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
                 ->assertJsonPath('message', 'These credentials do not match our records.');

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/auth/login', [
            'email'    => 'nobody@coderlink.sa',
            'password' => 'any-password',
        ]);

        $response->assertUnauthorized();
        $this->assertGuest();
    }

    #[Test]
    public function login_requires_email_and_password(): void
    {
        $response = $this->postJson('/auth/login', []);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function login_rejects_malformed_email(): void
    {
        $response = $this->postJson('/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/auth/logout');

        $response->assertOk()
                 ->assertJsonPath('message', 'Logged out.');

        $this->assertGuest();
    }

    #[Test]
    public function unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/auth/logout');

        $response->assertUnauthorized();
    }

    #[Test]
    public function remember_me_flag_is_accepted(): void
    {
        User::factory()->create([
            'email'    => 'admin@coderlink.sa',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email'    => 'admin@coderlink.sa',
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertOk();
    }
}
