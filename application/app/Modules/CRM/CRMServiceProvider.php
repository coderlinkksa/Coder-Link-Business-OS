<?php

namespace App\Modules\CRM;

use App\Modules\CRM\Domain\Contracts\ActivityRepository;
use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Infrastructure\Repositories\EloquentActivityRepository;
use App\Modules\CRM\Infrastructure\Repositories\EloquentLeadRepository;
use App\Modules\CRM\Infrastructure\Repositories\EloquentTaskRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LeadRepository::class, EloquentLeadRepository::class);
        $this->app->bind(ActivityRepository::class, EloquentActivityRepository::class);
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/API/Routes/routes.php');
    }
}
