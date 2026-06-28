<?php

namespace App\Modules\CRM;

use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');
    }
}

