<?php

namespace App\Shared\Contracts;

interface DomainEvent
{
    public function occurredAt(): \DateTimeImmutable;

    public function aggregateId(): int|string;
}
