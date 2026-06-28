<?php

namespace App\Modules\Identity\Domain\Rules;

/**
 * Validates that a permission string conforms to the naming convention:
 * {category}.{action}  (lowercase, hyphen-separated words, dot separator).
 * See RBAC_SPECIFICATION.md §7.
 */
final class PermissionNameRule
{
    private const VALID_CATEGORIES = [
        'identity', 'company', 'crm', 'sales', 'proposal',
        'contract', 'project', 'billing', 'support',
        'notification', 'integration', 'ai', 'admin',
    ];

    private const VALID_ACTIONS = [
        'view', 'view-own', 'create', 'update', 'update-own',
        'delete', 'restore', 'export', 'configure',
    ];

    public static function validate(string $permission): bool
    {
        $parts = explode('.', $permission, 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$category, $action] = $parts;

        return in_array($category, self::VALID_CATEGORIES, true)
            && in_array($action, self::VALID_ACTIONS, true);
    }

    public static function validCategories(): array
    {
        return self::VALID_CATEGORIES;
    }

    public static function validActions(): array
    {
        return self::VALID_ACTIONS;
    }
}
