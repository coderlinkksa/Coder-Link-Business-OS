<?php

namespace App\Modules\Identity\Domain\Enums;

/**
 * Every permission in the system.
 *
 * Naming convention: {category}.{action}
 * Categories and actions defined in RBAC_SPECIFICATION.md §6 and §7.
 *
 * No permission is added speculatively — every value here is used
 * by at least one role in RolePermissionMap.
 */
enum Permission: string
{
    // ── identity ─────────────────────────────────────────────────────────────
    case IdentityView      = 'identity.view';
    case IdentityConfigure = 'identity.configure';

    // ── company ──────────────────────────────────────────────────────────────
    case CompanyView   = 'company.view';
    case CompanyCreate = 'company.create';
    case CompanyUpdate = 'company.update';
    case CompanyDelete = 'company.delete';
    case CompanyExport = 'company.export';

    // ── crm ──────────────────────────────────────────────────────────────────
    case CrmView      = 'crm.view';
    case CrmViewOwn   = 'crm.view-own';
    case CrmCreate    = 'crm.create';
    case CrmUpdate    = 'crm.update';
    case CrmUpdateOwn = 'crm.update-own';
    case CrmDelete    = 'crm.delete';
    case CrmExport    = 'crm.export';

    // ── sales ─────────────────────────────────────────────────────────────────
    case SalesView      = 'sales.view';
    case SalesViewOwn   = 'sales.view-own';
    case SalesCreate    = 'sales.create';
    case SalesUpdate    = 'sales.update';
    case SalesUpdateOwn = 'sales.update-own';
    case SalesDelete    = 'sales.delete';
    case SalesExport    = 'sales.export';

    // ── proposal ──────────────────────────────────────────────────────────────
    case ProposalView      = 'proposal.view';
    case ProposalViewOwn   = 'proposal.view-own';
    case ProposalCreate    = 'proposal.create';
    case ProposalUpdate    = 'proposal.update';
    case ProposalUpdateOwn = 'proposal.update-own';
    case ProposalDelete    = 'proposal.delete';

    // ── contract ──────────────────────────────────────────────────────────────
    case ContractView   = 'contract.view';
    case ContractCreate = 'contract.create';
    case ContractUpdate = 'contract.update';
    case ContractDelete = 'contract.delete';

    // ── project ───────────────────────────────────────────────────────────────
    case ProjectView      = 'project.view';
    case ProjectViewOwn   = 'project.view-own';
    case ProjectCreate    = 'project.create';
    case ProjectUpdate    = 'project.update';
    case ProjectUpdateOwn = 'project.update-own';
    case ProjectDelete    = 'project.delete';

    // ── billing ───────────────────────────────────────────────────────────────
    case BillingView      = 'billing.view';
    case BillingCreate    = 'billing.create';
    case BillingUpdate    = 'billing.update';
    case BillingDelete    = 'billing.delete';
    case BillingExport    = 'billing.export';
    case BillingConfigure = 'billing.configure';

    // ── support ───────────────────────────────────────────────────────────────
    case SupportView      = 'support.view';
    case SupportViewOwn   = 'support.view-own';
    case SupportCreate    = 'support.create';
    case SupportUpdate    = 'support.update';
    case SupportUpdateOwn = 'support.update-own';
    case SupportDelete    = 'support.delete';

    // ── notification ──────────────────────────────────────────────────────────
    case NotificationView      = 'notification.view';
    case NotificationConfigure = 'notification.configure';

    // ── integration ───────────────────────────────────────────────────────────
    case IntegrationView      = 'integration.view';
    case IntegrationConfigure = 'integration.configure';

    // ── ai ────────────────────────────────────────────────────────────────────
    case AiView   = 'ai.view';
    case AiCreate = 'ai.create';

    // ── admin ─────────────────────────────────────────────────────────────────
    case AdminView      = 'admin.view';
    case AdminConfigure = 'admin.configure';
    case AdminExport    = 'admin.export';

    public function category(): string
    {
        return explode('.', $this->value)[0];
    }

    public function action(): string
    {
        return explode('.', $this->value, 2)[1];
    }
}
