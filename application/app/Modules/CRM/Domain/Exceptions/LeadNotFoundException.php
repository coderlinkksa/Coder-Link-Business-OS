<?php

namespace App\Modules\CRM\Domain\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class LeadNotFoundException extends NotFoundException
{
    public function __construct(int $id)
    {
        parent::__construct("Lead #{$id} not found.");
    }
}
