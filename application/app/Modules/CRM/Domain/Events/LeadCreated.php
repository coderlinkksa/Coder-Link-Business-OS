<?php

namespace App\Modules\CRM\Domain\Events;

use App\Modules\CRM\Domain\Models\Lead;
use App\Shared\Events\BaseDomainEvent;

class LeadCreated extends BaseDomainEvent
{
    public function __construct(
        public readonly Lead $lead,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->lead->id;
    }
}
