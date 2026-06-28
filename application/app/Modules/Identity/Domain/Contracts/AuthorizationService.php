<?php

namespace App\Modules\Identity\Domain\Contracts;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\Permission;
use App\Modules\Identity\Domain\Enums\Role;

interface AuthorizationService
{
    /**
     * Return true if the user holds the given permission.
     * Throws no exception — callers decide how to handle denial.
     */
    public function userCan(User $user, Permission $permission): bool;

    /**
     * Return true if the user holds the given permission AND owns the record
     * (relevant for view-own / update-own scoped permissions).
     * Owner and Admin roles bypass the ownership check.
     */
    public function userCanOnOwned(User $user, Permission $permission, mixed $record): bool;

    /**
     * Return the Role assigned to this user, or null if none is assigned.
     */
    public function roleFor(User $user): ?Role;

    /**
     * Return true if the user has been assigned any role.
     */
    public function hasRole(User $user): bool;
}
