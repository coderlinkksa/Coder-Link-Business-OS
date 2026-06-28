<?php

namespace App\Modules\Company\Domain\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class CompanyNotFoundException extends NotFoundException
{
    public function __construct(int|string $id)
    {
        parent::__construct("Company #{$id} not found.");
    }
}
