<?php

namespace App\Shared\Events;

use App\Shared\Contracts\DomainEvent;
use DateTimeImmutable;

abstract class BaseDomainEvent implements DomainEvent
{
    private DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new DateTimeImmutable();
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
