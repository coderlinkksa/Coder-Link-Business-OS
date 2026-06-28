<?php

namespace App\Modules\Identity\Domain\Events;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\Permission;
use App\Shared\Events\BaseDomainEvent;

/**
 * Raised whenever a permission check fails for an authenticated user.
 * Consumed by the audit/notification layer per RBAC_SPECIFICATION.md §12.
 */
class PermissionDenied extends BaseDomainEvent
{
    public function __construct(
        public readonly User $user,
        public readonly Permission $permission,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->user->id;
    }
}
