<?php

namespace App\Modules\Identity\Domain\Events;

use App\Shared\Events\BaseDomainEvent;

class UserLoggedOut extends BaseDomainEvent
{
    public function __construct(
        public readonly int|string $userId,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->userId;
    }
}
