<?php

namespace App\Modules\Identity;

use App\Modules\Identity\API\Middleware\RequireAuthentication;
use App\Modules\Identity\Domain\Contracts\AuthenticationService;
use App\Modules\Identity\Domain\Contracts\AuthorizationService;
use App\Modules\Identity\Infrastructure\Services\RoleAuthorizationService;
use App\Modules\Identity\Infrastructure\Services\SessionAuthenticationService;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticationService::class, SessionAuthenticationService::class);
        $this->app->bind(AuthorizationService::class, RoleAuthorizationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');

        $this->app['router']->aliasMiddleware('auth.required', RequireAuthentication::class);
    }
}
