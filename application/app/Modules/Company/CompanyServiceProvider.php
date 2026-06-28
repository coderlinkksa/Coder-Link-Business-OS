<?php

namespace App\Modules\Company;

use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Contracts\ContactRepository;
use App\Modules\Company\Infrastructure\Repositories\EloquentCompanyRepository;
use App\Modules\Company\Infrastructure\Repositories\EloquentContactRepository;
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
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');
    }
}
