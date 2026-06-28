<?php

namespace App\Modules\Identity\Domain\Events;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\Role;
use App\Shared\Events\BaseDomainEvent;

class RoleRevoked extends BaseDomainEvent
{
    public function __construct(
        public readonly User $targetUser,
        public readonly Role $role,
        public readonly User $revokedBy,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->targetUser->id;
    }
}
