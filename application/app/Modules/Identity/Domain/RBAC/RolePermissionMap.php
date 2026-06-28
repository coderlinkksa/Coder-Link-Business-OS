<?php

namespace App\Modules\Identity\Domain\RBAC;

use App\Modules\Identity\Domain\Enums\Permission;
use App\Modules\Identity\Domain\Enums\Role;

/**
 * Canonical mapping from Role to its granted Permissions.
 *
 * Derived from RBAC_SPECIFICATION.md §3, §6, §7.
 * Add permissions here when a new module is formally specified.
 * Do not grant permissions speculatively.
 */
final class RolePermissionMap
{
    /**
     * Returns the complete set of permissions granted to a given role.
     *
     * @return Permission[]
     */
    public static function permissionsFor(Role $role): array
    {
        return match ($role) {
            Role::Owner               => self::ownerPermissions(),
            Role::Admin               => self::adminPermissions(),
            Role::SalesRepresentative => self::salesRepresentativePermissions(),
            Role::AccountManager      => self::accountManagerPermissions(),
            Role::TechnicalSupport    => self::technicalSupportPermissions(),
            Role::Viewer              => self::viewerPermissions(),
        };
    }

    /**
     * Returns true if the given role has the given permission.
     */
    public static function roleHas(Role $role, Permission $permission): bool
    {
        return in_array($permission, self::permissionsFor($role), true);
    }

    // ── Owner ─────────────────────────────────────────────────────────────────
    // Full access to everything. Super-admin operations checked separately via isSuperAdmin().
    private static function ownerPermissions(): array
    {
        return Permission::cases();
    }

    // ── Admin ─────────────────────────────────────────────────────────────────
    // Full operational access; cannot perform Owner-exclusive operations.
    private static function adminPermissions(): array
    {
        return [
            // identity — can manage users but not assign Owner role (enforced at service layer)
            Permission::IdentityView,
            Permission::IdentityConfigure,
            // company
            Permission::CompanyView,
            Permission::CompanyCreate,
            Permission::CompanyUpdate,
            Permission::CompanyDelete,
            Permission::CompanyExport,
            // crm
            Permission::CrmView,
            Permission::CrmCreate,
            Permission::CrmUpdate,
            Permission::CrmDelete,
            Permission::CrmExport,
            // sales
            Permission::SalesView,
            Permission::SalesCreate,
            Permission::SalesUpdate,
            Permission::SalesDelete,
            Permission::SalesExport,
            // proposal
            Permission::ProposalView,
            Permission::ProposalCreate,
            Permission::ProposalUpdate,
            Permission::ProposalDelete,
            // contract
            Permission::ContractView,
            Permission::ContractCreate,
            Permission::ContractUpdate,
            Permission::ContractDelete,
            // project
            Permission::ProjectView,
            Permission::ProjectCreate,
            Permission::ProjectUpdate,
            Permission::ProjectDelete,
            // billing
            Permission::BillingView,
            Permission::BillingCreate,
            Permission::BillingUpdate,
            Permission::BillingDelete,
            Permission::BillingExport,
            Permission::BillingConfigure,
            // support
            Permission::SupportView,
            Permission::SupportCreate,
            Permission::SupportUpdate,
            Permission::SupportDelete,
            // notification
            Permission::NotificationView,
            Permission::NotificationConfigure,
            // integration
            Permission::IntegrationView,
            Permission::IntegrationConfigure,
            // ai
            Permission::AiView,
            Permission::AiCreate,
            // admin
            Permission::AdminView,
            Permission::AdminConfigure,
            Permission::AdminExport,
        ];
    }

    // ── Sales Representative ──────────────────────────────────────────────────
    // Pipeline and lead management; no financial or contract access.
    private static function salesRepresentativePermissions(): array
    {
        return [
            // company — view-only for context during lead work
            Permission::CompanyView,
            // crm — full pipeline entry; view all for handoff support
            Permission::CrmView,
            Permission::CrmCreate,
            Permission::CrmUpdateOwn,
            // sales — own opportunities
            Permission::SalesView,
            Permission::SalesCreate,
            Permission::SalesUpdateOwn,
            // proposal — create and manage own proposals
            Permission::ProposalCreate,
            Permission::ProposalViewOwn,
            Permission::ProposalUpdateOwn,
            // ai — generate drafts
            Permission::AiCreate,
        ];
    }

    // ── Account Manager ───────────────────────────────────────────────────────
    // All Sales Representative capabilities plus post-sale relationship management.
    private static function accountManagerPermissions(): array
    {
        return [
            // company — full read/write
            Permission::CompanyView,
            Permission::CompanyCreate,
            Permission::CompanyUpdate,
            // crm — full pipeline entry
            Permission::CrmView,
            Permission::CrmCreate,
            Permission::CrmUpdateOwn,
            // sales — full deal management
            Permission::SalesView,
            Permission::SalesCreate,
            Permission::SalesUpdateOwn,
            // proposal — manage through all stages including acceptance
            Permission::ProposalView,
            Permission::ProposalCreate,
            Permission::ProposalUpdate,
            Permission::ProposalViewOwn,
            Permission::ProposalUpdateOwn,
            // contract — view only
            Permission::ContractView,
            // project — manage own account projects
            Permission::ProjectView,
            Permission::ProjectCreate,
            Permission::ProjectUpdateOwn,
            // billing — read-only view of invoices and renewal dates
            Permission::BillingView,
            // support — create and manage support for own accounts
            Permission::SupportView,
            Permission::SupportCreate,
            Permission::SupportUpdateOwn,
            // ai — generate drafts
            Permission::AiCreate,
        ];
    }

    // ── Technical Support ─────────────────────────────────────────────────────
    // Project delivery and post-delivery support; no pipeline or financial access.
    private static function technicalSupportPermissions(): array
    {
        return [
            // company — read only
            Permission::CompanyView,
            // project — view and update own assigned tasks
            Permission::ProjectViewOwn,
            Permission::ProjectUpdateOwn,
            // support — create and manage own support requests
            Permission::SupportView,
            Permission::SupportCreate,
            Permission::SupportUpdateOwn,
        ];
    }

    // ── Viewer ────────────────────────────────────────────────────────────────
    // Read-only across all modules except system config and audit log.
    private static function viewerPermissions(): array
    {
        return [
            Permission::CompanyView,
            Permission::CrmView,
            Permission::SalesView,
            Permission::ProposalView,
            Permission::ContractView,
            Permission::ProjectView,
            Permission::BillingView,
            Permission::SupportView,
            Permission::NotificationView,
            Permission::IntegrationView,
            Permission::AiView,
        ];
    }
}
