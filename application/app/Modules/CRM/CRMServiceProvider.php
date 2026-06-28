<?php

namespace App\Modules\CRM;

use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Infrastructure\Repositories\EloquentLeadRepository;
use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LeadRepository::class, EloquentLeadRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');
    }
}
