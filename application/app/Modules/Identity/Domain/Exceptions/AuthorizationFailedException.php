<?php

namespace App\Modules\Identity\Domain\Exceptions;

use App\Shared\Exceptions\AuthorizationException;

class AuthorizationFailedException extends AuthorizationException
{
    public function __construct(string $permission = '')
    {
        $message = $permission
            ? "You do not have permission to perform this action: {$permission}."
            : 'You do not have permission to perform this action.';

        parent::__construct($message);
    }
}
