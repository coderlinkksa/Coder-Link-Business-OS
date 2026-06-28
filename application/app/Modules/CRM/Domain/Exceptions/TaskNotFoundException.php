<?php

namespace App\Modules\CRM\Domain\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class TaskNotFoundException extends NotFoundException
{
    public function __construct(int|string $id)
    {
        parent::__construct("Task #{$id} not found.");
    }
}
