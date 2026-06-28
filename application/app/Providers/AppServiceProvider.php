<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->registerBlueprintMacros();
    }

    private function registerBlueprintMacros(): void
    {
        // Adds created_by and updated_by (nullable FK to users.id) on business tables.
        Blueprint::macro('auditStamps', function () {
            /** @var Blueprint $this */
            $this->unsignedBigInteger('created_by')->nullable()->index();
            $this->unsignedBigInteger('updated_by')->nullable()->index();
        });

        // Adds owner_id for future multi-tenant isolation (DATABASE_ARCHITECTURE.md §4).
        Blueprint::macro('ownerReference', function () {
            /** @var Blueprint $this */
            $this->unsignedBigInteger('owner_id')->nullable()->index();
        });

        // Timestamps + soft delete as a single call for all business tables.
        Blueprint::macro('platformTimestamps', function () {
            /** @var Blueprint $this */
            $this->timestamps();
            $this->softDeletes();
        });

        // Convenience macro: all platform-standard columns in one call.
        Blueprint::macro('platformColumns', function () {
            /** @var Blueprint $this */
            $this->auditStamps();
            $this->ownerReference();
            $this->platformTimestamps();
        });
    }
}
