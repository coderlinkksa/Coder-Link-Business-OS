<?php

namespace App\Modules\CRM\Domain\Events;

use App\Modules\CRM\Application\DTOs\ConvertLeadData;
use App\Modules\CRM\Domain\Models\Lead;
use App\Shared\Events\BaseDomainEvent;

class LeadConvertedToOpportunity extends BaseDomainEvent
{
    public function __construct(
        public readonly Lead            $lead,
        public readonly ConvertLeadData $conversionData,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->lead->id;
    }
}
