<?php

namespace App\Modules\Company\Domain\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class CompanyNotFoundException extends NotFoundException
{
    public function __construct(int $id)
    {
        parent::__construct("Company #{$id} not found.");
    }
}
