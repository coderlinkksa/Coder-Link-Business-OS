<?php

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Application\DTOs\ConvertLeadData;
use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Events\LeadConvertedToOpportunity;
use App\Modules\CRM\Domain\Exceptions\LeadNotFoundException;
use App\Modules\CRM\Domain\Models\Lead;

class ConvertLeadToOpportunityAction
{
    public function __construct(
        private readonly LeadRepository $leads,
    ) {}

    public function execute(ConvertLeadData $data): Lead
    {
        $lead = $this->leads->findById($data->leadId);

        if ($lead === null) {
            throw new LeadNotFoundException($data->leadId);
        }

        // Throws LeadAlreadyConvertedException if already converted.
        $lead->markConverted();

        $this->leads->save($lead);

        event(new LeadConvertedToOpportunity($lead, $data));

        return $lead;
    }
}
