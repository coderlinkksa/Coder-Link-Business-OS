<?php

namespace App\Modules\Sales\Infrastructure\Repositories;

use App\Modules\Sales\Domain\Contracts\OpportunityRepository;
use App\Modules\Sales\Domain\Models\Opportunity;

class EloquentOpportunityRepository implements OpportunityRepository
{
    public function findById(int $id): ?Opportunity
    {
        return Opportunity::find($id);
    }

    public function save(Opportunity $opportunity): void
    {
        $opportunity->save();
    }
}
