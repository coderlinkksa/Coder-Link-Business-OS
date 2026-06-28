<?php

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Domain\Enums\Permission;
use App\Modules\Identity\Domain\Enums\Role;
use App\Modules\Identity\Domain\RBAC\RolePermissionMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RolePermissionMapTest extends TestCase
{
    // ── Owner ─────────────────────────────────────────────────────────────────

    #[Test]
    public function owner_has_every_permission(): void
    {
        foreach (Permission::cases() as $permission) {
            $this->assertTrue(
                RolePermissionMap::roleHas(Role::Owner, $permission),
                "Owner should have permission: {$permission->value}",
            );
        }
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    #[Test]
    public function admin_has_full_operational_permissions(): void
    {
        $expected = [
            Permission::IdentityView, Permission::IdentityConfigure,
            Permission::CompanyView, Permission::CompanyCreate, Permission::CompanyUpdate, Permission::CompanyDelete,
            Permission::CrmView, Permission::CrmCreate, Permission::CrmUpdate, Permission::CrmDelete,
            Permission::BillingView, Permission::BillingConfigure,
            Permission::AdminView, Permission::AdminConfigure,
        ];

        foreach ($expected as $permission) {
            $this->assertTrue(
                RolePermissionMap::roleHas(Role::Admin, $permission),
                "Admin should have: {$permission->value}",
            );
        }
    }

    // ── Sales Representative ──────────────────────────────────────────────────

    #[Test]
    public function sales_representative_can_create_and_view_leads(): void
    {
        $this->assertTrue(RolePermissionMap::roleHas(Role::SalesRepresentative, Permission::CrmCreate));
        $this->assertTrue(RolePermissionMap::roleHas(Role::SalesRepresentative, Permission::CrmView));
        $this->assertTrue(RolePermissionMap::roleHas(Role::SalesRepresentative, Permission::CrmUpdateOwn));
    }

    #[Test]
    public function sales_representative_cannot_access_billing_or_contracts(): void
    {
        $forbidden = [
            Permission::BillingView, Permission::BillingCreate,
            Permission::ContractView, Permission::ContractCreate,
        ];

        foreach ($forbidden as $permission) {
            $this->assertFalse(
                RolePermissionMap::roleHas(Role::SalesRepresentative, $permission),
                "SalesRepresentative should NOT have: {$permission->value}",
            );
        }
    }

    #[Test]
    public function sales_representative_cannot_delete_any_record(): void
    {
        $deletePermissions = array_filter(
            Permission::cases(),
            fn (Permission $p) => str_ends_with($p->value, '.delete'),
        );

        foreach ($deletePermissions as $permission) {
            $this->assertFalse(
                RolePermissionMap::roleHas(Role::SalesRepresentative, $permission),
                "SalesRepresentative should NOT have delete: {$permission->value}",
            );
        }
    }

    // ── Account Manager ───────────────────────────────────────────────────────

    #[Test]
    public function account_manager_can_view_contracts_but_not_modify_them(): void
    {
        $this->assertTrue(RolePermissionMap::roleHas(Role::AccountManager, Permission::ContractView));
        $this->assertFalse(RolePermissionMap::roleHas(Role::AccountManager, Permission::ContractCreate));
        $this->assertFalse(RolePermissionMap::roleHas(Role::AccountManager, Permission::ContractUpdate));
    }

    #[Test]
    public function account_manager_can_view_billing_but_not_configure_it(): void
    {
        $this->assertTrue(RolePermissionMap::roleHas(Role::AccountManager, Permission::BillingView));
        $this->assertFalse(RolePermissionMap::roleHas(Role::AccountManager, Permission::BillingCreate));
        $this->assertFalse(RolePermissionMap::roleHas(Role::AccountManager, Permission::BillingConfigure));
    }

    #[Test]
    public function account_manager_cannot_delete_any_record(): void
    {
        $deletePermissions = array_filter(
            Permission::cases(),
            fn (Permission $p) => str_ends_with($p->value, '.delete'),
        );

        foreach ($deletePermissions as $permission) {
            $this->assertFalse(
                RolePermissionMap::roleHas(Role::AccountManager, $permission),
                "AccountManager should NOT have delete: {$permission->value}",
            );
        }
    }

    // ── Technical Support ─────────────────────────────────────────────────────

    #[Test]
    public function technical_support_can_view_companies_and_own_projects(): void
    {
        $this->assertTrue(RolePermissionMap::roleHas(Role::TechnicalSupport, Permission::CompanyView));
        $this->assertTrue(RolePermissionMap::roleHas(Role::TechnicalSupport, Permission::ProjectViewOwn));
        $this->assertTrue(RolePermissionMap::roleHas(Role::TechnicalSupport, Permission::SupportCreate));
    }

    #[Test]
    public function technical_support_cannot_access_pipeline_or_financial_data(): void
    {
        $forbidden = [
            Permission::CrmView, Permission::SalesView,
            Permission::ProposalView, Permission::BillingView,
        ];

        foreach ($forbidden as $permission) {
            $this->assertFalse(
                RolePermissionMap::roleHas(Role::TechnicalSupport, $permission),
                "TechnicalSupport should NOT have: {$permission->value}",
            );
        }
    }

    // ── Viewer ────────────────────────────────────────────────────────────────

    #[Test]
    public function viewer_has_only_read_permissions(): void
    {
        $viewerPermissions = RolePermissionMap::permissionsFor(Role::Viewer);

        foreach ($viewerPermissions as $permission) {
            $this->assertStringContainsString(
                'view',
                $permission->action(),
                "Viewer permission {$permission->value} is not a view permission",
            );
        }
    }

    #[Test]
    public function viewer_cannot_access_admin_or_system_config(): void
    {
        $this->assertFalse(RolePermissionMap::roleHas(Role::Viewer, Permission::AdminView));
        $this->assertFalse(RolePermissionMap::roleHas(Role::Viewer, Permission::AdminConfigure));
        $this->assertFalse(RolePermissionMap::roleHas(Role::Viewer, Permission::IdentityConfigure));
    }

    // ── No role ───────────────────────────────────────────────────────────────

    #[Test]
    public function every_role_has_at_least_one_permission(): void
    {
        foreach (Role::cases() as $role) {
            $this->assertNotEmpty(
                RolePermissionMap::permissionsFor($role),
                "Role {$role->value} has no permissions",
            );
        }
    }
}
