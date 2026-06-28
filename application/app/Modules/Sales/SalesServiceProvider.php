<?php

namespace App\Modules\Sales;

use App\Modules\CRM\Domain\Events\LeadConvertedToOpportunity;
use App\Modules\Sales\Domain\Contracts\OpportunityRepository;
use App\Modules\Sales\Infrastructure\Listeners\CreateOpportunityOnLeadConversion;
use App\Modules\Sales\Infrastructure\Repositories\EloquentOpportunityRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OpportunityRepository::class, EloquentOpportunityRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/API/Routes/routes.php');

        Event::listen(LeadConvertedToOpportunity::class, CreateOpportunityOnLeadConversion::class);
    }
}
