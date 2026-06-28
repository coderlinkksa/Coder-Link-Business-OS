<?php

namespace App\Modules\Identity\Domain\Events;

use App\Models\User;
use App\Shared\Events\BaseDomainEvent;

class UserLoggedIn extends BaseDomainEvent
{
    public function __construct(
        public readonly User $user,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->user->id;
    }
}
