<?php

namespace App\Modules\Identity\Domain\Enums;

/**
 * System roles as defined in RBAC_SPECIFICATION.md §3.
 * Every user has exactly one role. Users with no role have no access.
 */
enum Role: string
{
    case Owner              = 'owner';
    case Admin              = 'admin';
    case SalesRepresentative = 'sales_representative';
    case AccountManager     = 'account_manager';
    case TechnicalSupport   = 'technical_support';
    case Viewer             = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner               => 'Owner',
            self::Admin               => 'Admin',
            self::SalesRepresentative => 'Sales Representative',
            self::AccountManager      => 'Account Manager',
            self::TechnicalSupport    => 'Technical Support',
            self::Viewer              => 'Viewer',
        };
    }

    /**
     * Owner and Admin bypass all ownership restrictions.
     */
    public function bypassesOwnership(): bool
    {
        return match ($this) {
            self::Owner, self::Admin => true,
            default                  => false,
        };
    }

    /**
     * Only Owner can perform super-admin operations (delete admins, assign Owner role).
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::Owner;
    }
}
