<?php

namespace App\Modules\Sales\Infrastructure\Listeners;

use App\Modules\CRM\Domain\Events\LeadConvertedToOpportunity;
use App\Modules\Sales\Application\Actions\CreateOpportunityAction;
use App\Modules\Sales\Application\DTOs\CreateOpportunityData;

class CreateOpportunityOnLeadConversion
{
    public function __construct(
        private readonly CreateOpportunityAction $createOpportunity,
    ) {}

    public function handle(LeadConvertedToOpportunity $event): void
    {
        $data = $event->conversionData;

        $this->createOpportunity->execute(new CreateOpportunityData(
            companyId:         $data->companyId,
            title:             $data->opportunityTitle,
            leadId:            $event->lead->id,
            contactPersonId:   $data->contactPersonId,
            valueMinorUnits:   $data->valueMinorUnits,
            probability:       $data->probability,
            expectedCloseDate: $data->expectedCloseDate,
            assignedTo:        $data->assignedTo,
            notes:             $data->notes,
        ));
    }
}
