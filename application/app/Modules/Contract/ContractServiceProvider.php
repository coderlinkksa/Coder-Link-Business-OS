<?php

namespace App\Modules\Contract;

use Illuminate\Support\ServiceProvider;

class ContractServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');
    }
}

