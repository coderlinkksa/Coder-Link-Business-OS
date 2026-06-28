<?php

namespace App\Modules\Sales\Application\Actions;

use App\Modules\Sales\Application\DTOs\CreateOpportunityData;
use App\Modules\Sales\Domain\Contracts\OpportunityRepository;
use App\Modules\Sales\Domain\Events\OpportunityCreated;
use App\Modules\Sales\Domain\Models\Opportunity;

class CreateOpportunityAction
{
    public function __construct(
        private readonly OpportunityRepository $opportunities,
    ) {}

    public function execute(CreateOpportunityData $data): Opportunity
    {
        $opportunity = new Opportunity();
        $opportunity->fill([
            'company_id'          => $data->companyId,
            'title'               => $data->title,
            'stage'               => $data->stage,
            'lead_id'             => $data->leadId,
            'contact_person_id'   => $data->contactPersonId,
            'value_minor_units'   => $data->valueMinorUnits,
            'probability'         => $data->probability,
            'expected_close_date' => $data->expectedCloseDate,
            'assigned_to'         => $data->assignedTo,
            'notes'               => $data->notes,
        ]);

        $this->opportunities->save($opportunity);

        event(new OpportunityCreated($opportunity));

        return $opportunity;
    }
}
