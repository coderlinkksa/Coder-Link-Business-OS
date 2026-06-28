<?php

namespace Tests\Unit\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\Permission;
use App\Modules\Identity\Domain\Enums\Role;
use App\Modules\Identity\Infrastructure\Services\RoleAuthorizationService;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationServiceTest extends TestCase
{
    private RoleAuthorizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleAuthorizationService();
    }

    private function userWithRole(Role $role): User
    {
        $user = new User();
        $user->id = 1;
        $user->forceFill(['role' => $role->value]);
        return $user;
    }

    private function userWithNoRole(): User
    {
        $user = new User();
        $user->id = 2;
        return $user;
    }

    // ── roleFor ───────────────────────────────────────────────────────────────

    #[Test]
    public function role_for_returns_correct_role(): void
    {
        $user = $this->userWithRole(Role::Admin);
        $this->assertSame(Role::Admin, $this->service->roleFor($user));
    }

    #[Test]
    public function role_for_returns_null_when_user_has_no_role(): void
    {
        $this->assertNull($this->service->roleFor($this->userWithNoRole()));
    }

    #[Test]
    public function has_role_returns_true_when_role_is_assigned(): void
    {
        $this->assertTrue($this->service->hasRole($this->userWithRole(Role::Viewer)));
    }

    #[Test]
    public function has_role_returns_false_when_no_role_is_assigned(): void
    {
        $this->assertFalse($this->service->hasRole($this->userWithNoRole()));
    }

    // ── userCan ───────────────────────────────────────────────────────────────

    #[Test]
    public function owner_can_do_everything(): void
    {
        $user = $this->userWithRole(Role::Owner);

        foreach (Permission::cases() as $permission) {
            $this->assertTrue(
                $this->service->userCan($user, $permission),
                "Owner should be able to: {$permission->value}",
            );
        }
    }

    #[Test]
    public function user_with_no_role_cannot_do_anything(): void
    {
        Event::fake();
        $user = $this->userWithNoRole();

        $this->assertFalse($this->service->userCan($user, Permission::CrmView));
    }

    #[Test]
    public function sales_representative_can_create_crm_records(): void
    {
        $user = $this->userWithRole(Role::SalesRepresentative);
        $this->assertTrue($this->service->userCan($user, Permission::CrmCreate));
    }

    #[Test]
    public function sales_representative_cannot_view_billing(): void
    {
        Event::fake();
        $user = $this->userWithRole(Role::SalesRepresentative);
        $this->assertFalse($this->service->userCan($user, Permission::BillingView));
    }

    #[Test]
    public function technical_support_cannot_view_sales_pipeline(): void
    {
        Event::fake();
        $user = $this->userWithRole(Role::TechnicalSupport);
        $this->assertFalse($this->service->userCan($user, Permission::SalesView));
    }

    // ── userCanOnOwned ────────────────────────────────────────────────────────

    #[Test]
    public function owner_can_act_on_any_record_regardless_of_ownership(): void
    {
        $user   = $this->userWithRole(Role::Owner);
        $record = (object) ['assigned_to' => 999, 'created_by' => 999]; // belongs to someone else

        $this->assertTrue($this->service->userCanOnOwned($user, Permission::CrmUpdateOwn, $record));
    }

    #[Test]
    public function admin_can_act_on_any_record_regardless_of_ownership(): void
    {
        $user   = $this->userWithRole(Role::Admin);
        $record = (object) ['assigned_to' => 999, 'created_by' => 999];

        $this->assertTrue($this->service->userCanOnOwned($user, Permission::CrmUpdate, $record));
    }

    #[Test]
    public function sales_rep_can_act_on_record_they_are_assigned_to(): void
    {
        $user        = $this->userWithRole(Role::SalesRepresentative);
        $ownedRecord = (object) ['assigned_to' => 1, 'created_by' => 99];

        $this->assertTrue($this->service->userCanOnOwned($user, Permission::CrmUpdateOwn, $ownedRecord));
    }

    #[Test]
    public function sales_rep_can_act_on_record_they_created(): void
    {
        $user        = $this->userWithRole(Role::SalesRepresentative);
        $ownedRecord = (object) ['assigned_to' => null, 'created_by' => 1];

        $this->assertTrue($this->service->userCanOnOwned($user, Permission::CrmUpdateOwn, $ownedRecord));
    }

    #[Test]
    public function sales_rep_cannot_act_on_record_they_do_not_own(): void
    {
        Event::fake();
        $user          = $this->userWithRole(Role::SalesRepresentative);
        $unownedRecord = (object) ['assigned_to' => 50, 'created_by' => 50];

        $this->assertFalse($this->service->userCanOnOwned($user, Permission::CrmUpdateOwn, $unownedRecord));
    }

    #[Test]
    public function user_with_no_role_cannot_act_on_owned_records(): void
    {
        Event::fake();
        $user   = $this->userWithNoRole();
        $record = (object) ['assigned_to' => 2, 'created_by' => 2]; // user id 2 owns it

        $this->assertFalse($this->service->userCanOnOwned($user, Permission::CrmUpdateOwn, $record));
    }

    // ── Permission denied event ───────────────────────────────────────────────

    #[Test]
    public function permission_denied_event_is_fired_on_failed_check(): void
    {
        Event::fake();
        $user = $this->userWithRole(Role::TechnicalSupport);

        $this->service->userCan($user, Permission::BillingView);

        Event::assertDispatched(
            \App\Modules\Identity\Domain\Events\PermissionDenied::class,
            fn ($e) => $e->user->id === $user->id && $e->permission === Permission::BillingView,
        );
    }
}
