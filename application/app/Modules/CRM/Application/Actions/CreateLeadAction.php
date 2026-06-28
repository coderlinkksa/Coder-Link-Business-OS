<?php

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Application\DTOs\CreateLeadData;
use App\Modules\CRM\Domain\Contracts\LeadRepository;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Events\LeadCreated;
use App\Modules\CRM\Domain\Models\Lead;
use App\Shared\Exceptions\ValidationException;

class CreateLeadAction
{
    public function __construct(
        private readonly LeadRepository $leads,
    ) {}

    public function execute(CreateLeadData $data): Lead
    {
        if ($data->email === null && $data->phone === null) {
            throw new ValidationException('A lead must have at least an email or a phone number.');
        }

        $lead = new Lead();
        $lead->fill([
            'name'              => $data->name,
            'source'            => $data->source,
            'status'            => LeadStatus::New,
            'email'             => $data->email,
            'phone'             => $data->phone,
            'service_requested' => $data->serviceRequested,
            'company_id'        => $data->companyId,
            'contact_person_id' => $data->contactPersonId,
            'assigned_to'       => $data->assignedTo,
            'notes'             => $data->notes,
        ]);

        $this->leads->save($lead);

        event(new LeadCreated($lead));

        return $lead;
    }
}
