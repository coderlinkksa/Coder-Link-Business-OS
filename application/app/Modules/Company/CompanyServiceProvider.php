<?php

namespace App\Modules\Company;

use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Contracts\ContactRepository;
use App\Modules\Company\Infrastructure\Repositories\EloquentCompanyRepository;
use App\Modules\Company\Infrastructure\Repositories\EloquentContactRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CompanyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepository::class, EloquentCompanyRepository::class);
        $this->app->bind(ContactRepository::class, EloquentContactRepository::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/API/Routes/routes.php');
    }
}
