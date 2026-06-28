<?php

namespace App\Modules\CRM\Domain\Exceptions;

use App\Shared\Exceptions\DomainException;

class TaskAlreadyCompletedException extends DomainException
{
    public function __construct(int|string $id)
    {
        parent::__construct("Task #{$id} is already in a terminal state and cannot be completed.");
    }
}
