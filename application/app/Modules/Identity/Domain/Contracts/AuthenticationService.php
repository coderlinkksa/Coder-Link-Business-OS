<?php

namespace App\Modules\Identity\Domain\Contracts;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\LoginData;

interface AuthenticationService
{
    /**
     * Attempt to authenticate a user with the given credentials.
     * Returns the authenticated User on success.
     * Throws AuthenticationFailedException on failure.
     */
    public function login(LoginData $data): User;

    /**
     * Terminate the current authenticated session.
     */
    public function logout(): void;

    /**
     * Return the currently authenticated user, or null.
     */
    public function user(): ?User;

    /**
     * Determine if there is a currently authenticated user.
     */
    public function check(): bool;
}
