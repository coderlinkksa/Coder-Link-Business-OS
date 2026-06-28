<?php

namespace App\Modules\CRM\Infrastructure\Repositories;

use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Models\Lead;

class EloquentLeadRepository implements LeadRepository
{
    public function findById(int $id): ?Lead
    {
        return Lead::find($id);
    }

    public function save(Lead $lead): void
    {
        $lead->save();
    }
}
