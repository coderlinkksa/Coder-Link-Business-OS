<?php

namespace App\Modules\Identity\Domain\Events;

use App\Shared\Events\BaseDomainEvent;

class UserLoginFailed extends BaseDomainEvent
{
    public function __construct(
        public readonly string $email,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->email;
    }
}
