<?php

namespace App\Shared\Models;

use App\Models\User;
use App\Modules\Identity\Domain\Contracts\AuthorizationService;
use App\Modules\Identity\Domain\Enums\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Base for all module policies.
 * Delegates permission checks to the AuthorizationService contract so policies
 * never depend on a concrete implementation.
 */
abstract class BasePolicy
{
    use HandlesAuthorization;

    public function __construct(
        protected readonly AuthorizationService $authorization,
    ) {}

    /**
     * Check a straight permission (no ownership scope).
     */
    protected function can(User $user, Permission $permission): bool
    {
        return $this->authorization->userCan($user, $permission);
    }

    /**
     * Check a permission with ownership scope.
     * Owner and Admin bypass the ownership check (see RoleAuthorizationService).
     */
    protected function canOnOwned(User $user, Permission $permission, mixed $record): bool
    {
        return $this->authorization->userCanOnOwned($user, $permission, $record);
    }

    /**
     * Shortcut for the before() hook: Owner and Admin always pass.
     * Return null to fall through to individual policy methods.
     */
    protected function beforeCheck(User $user): ?bool
    {
        $role = $this->authorization->roleFor($user);

        if ($role === null) {
            return false;
        }

        if ($role->bypassesOwnership()) {
            return true;
        }

        return null;
    }
}
