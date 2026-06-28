<?php

namespace App\Modules\Identity\Application\Actions;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\LoginData;
use App\Modules\Identity\Domain\Contracts\AuthenticationService;

class LoginAction
{
    public function __construct(
        private readonly AuthenticationService $auth,
    ) {}

    public function execute(LoginData $data): User
    {
        return $this->auth->login($data);
    }
}
