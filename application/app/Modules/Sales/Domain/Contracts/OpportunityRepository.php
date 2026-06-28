<?php

namespace App\Modules\Sales\Domain\Contracts;

use App\Modules\Sales\Domain\Models\Opportunity;

interface OpportunityRepository
{
    public function findById(int|string $id): ?Opportunity;

    public function save(Opportunity $opportunity): void;
}
