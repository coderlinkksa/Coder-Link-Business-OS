<?php

namespace App\Modules\Identity\Infrastructure\Services;

use App\Models\User;
use App\Modules\Identity\Domain\Contracts\AuthorizationService;
use App\Modules\Identity\Domain\Enums\Permission;
use App\Modules\Identity\Domain\Enums\Role;
use App\Modules\Identity\Domain\Events\PermissionDenied;
use App\Modules\Identity\Domain\RBAC\RolePermissionMap;

class RoleAuthorizationService implements AuthorizationService
{
    public function userCan(User $user, Permission $permission): bool
    {
        $role = $this->roleFor($user);

        if ($role === null) {
            event(new PermissionDenied($user, $permission));
            return false;
        }

        $granted = RolePermissionMap::roleHas($role, $permission);

        if (! $granted) {
            event(new PermissionDenied($user, $permission));
        }

        return $granted;
    }

    public function userCanOnOwned(User $user, Permission $permission, mixed $record): bool
    {
        $role = $this->roleFor($user);

        if ($role === null) {
            event(new PermissionDenied($user, $permission));
            return false;
        }

        // Owner and Admin bypass ownership restrictions.
        if ($role->bypassesOwnership()) {
            return RolePermissionMap::roleHas($role, $permission);
        }

        if (! RolePermissionMap::roleHas($role, $permission)) {
            event(new PermissionDenied($user, $permission));
            return false;
        }

        return $this->ownsRecord($user, $record);
    }

    public function roleFor(User $user): ?Role
    {
        $raw = $user->getAttribute('role');

        if ($raw === null || $raw === '') {
            return null;
        }

        return Role::tryFrom($raw);
    }

    public function hasRole(User $user): bool
    {
        return $this->roleFor($user) !== null;
    }

    /**
     * A user owns a record when they are either the creator or the assignee.
     * See RBAC_SPECIFICATION.md §8.
     */
    private function ownsRecord(User $user, mixed $record): bool
    {
        if (! is_object($record)) {
            return false;
        }

        $assignedTo = $record->assigned_to ?? null;
        $createdBy  = $record->created_by  ?? null;

        return ($assignedTo !== null && (int) $assignedTo === $user->id)
            || ($createdBy  !== null && (int) $createdBy  === $user->id);
    }
}
