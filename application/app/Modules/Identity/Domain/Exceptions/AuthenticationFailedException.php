<?php

namespace App\Modules\Identity\Domain\Exceptions;

use App\Shared\Exceptions\DomainException;

class AuthenticationFailedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('These credentials do not match our records.');
    }
}
