<?php

namespace App\Modules\CRM\Domain\Exceptions;

use App\Shared\Exceptions\DomainException;

class LeadAlreadyConvertedException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("Lead #{$id} has already been converted to an opportunity.");
    }
}
