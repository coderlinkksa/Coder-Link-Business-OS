<?php

namespace App\Modules\Sales\Domain\Events;

use App\Modules\Sales\Domain\Models\Opportunity;
use App\Shared\Events\BaseDomainEvent;

class OpportunityCreated extends BaseDomainEvent
{
    public function __construct(
        public readonly Opportunity $opportunity,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->opportunity->id;
    }
}
