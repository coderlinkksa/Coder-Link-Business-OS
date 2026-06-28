<?php

namespace App\Modules\Proposal;

use Illuminate\Support\ServiceProvider;

class ProposalServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');
    }
}

