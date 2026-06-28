<?php

namespace App\Modules\Identity\Application\Actions;

use App\Modules\Identity\Domain\Contracts\AuthenticationService;

class LogoutAction
{
    public function __construct(
        private readonly AuthenticationService $auth,
    ) {}

    public function execute(): void
    {
        $this->auth->logout();
    }
}
