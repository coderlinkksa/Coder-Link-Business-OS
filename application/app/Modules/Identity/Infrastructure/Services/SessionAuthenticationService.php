<?php

namespace App\Modules\Identity\Infrastructure\Services;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\LoginData;
use App\Modules\Identity\Domain\Contracts\AuthenticationService;
use App\Modules\Identity\Domain\Events\UserLoggedIn;
use App\Modules\Identity\Domain\Events\UserLoggedOut;
use App\Modules\Identity\Domain\Events\UserLoginFailed;
use App\Modules\Identity\Domain\Exceptions\AuthenticationFailedException;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;

class SessionAuthenticationService implements AuthenticationService
{
    public function __construct(
        private readonly AuthManager $auth,
        private readonly Session $session,
    ) {}

    public function login(LoginData $data): User
    {
        $credentials = [
            'email'    => $data->email,
            'password' => $data->password,
        ];

        if (! $this->auth->attempt($credentials, $data->remember)) {
            event(new UserLoginFailed($data->email));

            throw new AuthenticationFailedException();
        }

        $this->session->regenerate();

        $user = $this->auth->user();

        event(new UserLoggedIn($user));

        return $user;
    }

    public function logout(): void
    {
        $userId = $this->auth->id();

        $this->auth->logout();

        $this->session->invalidate();
        $this->session->regenerateToken();

        if ($userId !== null) {
            event(new UserLoggedOut($userId));
        }
    }

    public function user(): ?User
    {
        return $this->auth->user();
    }

    public function check(): bool
    {
        return $this->auth->check();
    }
}
