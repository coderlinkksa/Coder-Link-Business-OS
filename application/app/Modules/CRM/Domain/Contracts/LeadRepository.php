<?php

namespace App\Modules\CRM\Domain\Contracts;

use App\Modules\CRM\Domain\Models\Lead;

interface LeadRepository
{
    public function findById(int|string $id): ?Lead;

    public function save(Lead $lead): void;
}
