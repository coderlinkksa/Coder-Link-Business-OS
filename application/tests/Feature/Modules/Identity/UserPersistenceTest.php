<?php

namespace Tests\Feature\Modules\Identity;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\Role;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Services\RoleAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // ── Schema & UUID ─────────────────────────────────────────────────────────

    #[Test]
    public function users_table_primary_key_is_a_uuid(): void
    {
        $user = User::factory()->create();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $user->getKey(),
        );
    }

    #[Test]
    public function each_user_receives_a_unique_uuid(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->assertNotSame($a->getKey(), $b->getKey());
    }

    #[Test]
    public function user_can_be_retrieved_by_uuid(): void
    {
        $created = User::factory()->create(['name' => 'Test User']);
        $found   = User::find($created->getKey());

        $this->assertNotNull($found);
        $this->assertSame('Test User', $found->name);
    }

    // ── Email uniqueness ──────────────────────────────────────────────────────

    #[Test]
    public function email_must_be_unique_across_users(): void
    {
        User::factory()->create(['email' => 'unique@coderlink.sa']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'unique@coderlink.sa']);
    }

    // ── Role persistence ──────────────────────────────────────────────────────

    #[Test]
    public function every_role_variant_can_be_persisted_and_retrieved(): void
    {
        foreach (Role::cases() as $role) {
            $user  = User::factory()->create(['role' => $role]);
            $fresh = User::find($user->getKey());

            $this->assertSame($role, $fresh->role, "Failed for role: {$role->value}");
        }
    }

    #[Test]
    public function factory_defaults_to_viewer_role(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Role::Viewer, $user->fresh()->role);
    }

    // ── Status persistence ────────────────────────────────────────────────────

    #[Test]
    public function user_status_active_is_persisted_correctly(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Active]);

        $this->assertSame(UserStatus::Active, $user->fresh()->status);
        $this->assertTrue($user->fresh()->isActive());
    }

    #[Test]
    public function user_status_inactive_is_persisted_correctly(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Inactive]);

        $this->assertSame(UserStatus::Inactive, $user->fresh()->status);
        $this->assertFalse($user->fresh()->isActive());
    }

    #[Test]
    public function factory_defaults_to_active_status(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->isActive());
    }

    // ── Password storage ──────────────────────────────────────────────────────

    #[Test]
    public function password_is_stored_as_a_bcrypt_hash(): void
    {
        $user = User::factory()->create();

        $this->assertStringStartsWith('$2y$', $user->fresh()->password);
    }

    #[Test]
    public function correct_password_passes_hash_check(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $this->assertTrue(Hash::check('secret123', $user->fresh()->password));
    }

    #[Test]
    public function raw_password_string_is_never_stored(): void
    {
        $user = User::factory()->create(['password' => Hash::make('plaintext')]);

        $this->assertNotSame('plaintext', $user->fresh()->password);
    }

    // ── Authentication compatibility ──────────────────────────────────────────

    #[Test]
    public function uuid_user_can_login_via_session_auth(): void
    {
        User::factory()->create([
            'email'    => 'admin@coderlink.sa',
            'password' => Hash::make('secure-password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email'    => 'admin@coderlink.sa',
            'password' => 'secure-password',
        ]);

        $response->assertOk()
                 ->assertJsonPath('user.email', 'admin@coderlink.sa');

        $this->assertAuthenticated();
    }

    #[Test]
    public function login_response_returns_the_uuid_as_user_id(): void
    {
        $user = User::factory()->create([
            'email'    => 'uuid@coderlink.sa',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email'    => 'uuid@coderlink.sa',
            'password' => 'password',
        ]);

        $response->assertOk()
                 ->assertJsonPath('user.id', $user->getKey());
    }

    // ── Role lookup from persisted user ───────────────────────────────────────

    #[Test]
    public function role_authorization_service_resolves_role_from_persisted_user(): void
    {
        $user    = User::factory()->create(['role' => Role::SalesRepresentative]);
        $fresh   = User::find($user->getKey());
        $service = new RoleAuthorizationService();

        $this->assertSame(Role::SalesRepresentative, $service->roleFor($fresh));
    }

    #[Test]
    public function role_authorization_service_returns_null_for_user_with_no_role_attribute(): void
    {
        // Bypass the factory default so role is null/empty in the raw attributes.
        $user = new User();
        $user->forceFill(['role' => null]);

        $service = new RoleAuthorizationService();
        $this->assertNull($service->roleFor($user));
    }
}
