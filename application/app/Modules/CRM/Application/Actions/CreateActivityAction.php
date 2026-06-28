<?php

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Application\DTOs\CreateActivityData;
use App\Modules\CRM\Domain\Contracts\ActivityRepository;
use App\Modules\CRM\Domain\Events\ActivityCreated;
use App\Modules\CRM\Domain\Models\Activity;

class CreateActivityAction
{
    public function __construct(
        private readonly ActivityRepository $activities,
    ) {}

    public function execute(CreateActivityData $data): Activity
    {
        $activity = new Activity();
        $activity->fill([
            'type'              => $data->type,
            'subject'           => $data->subject,
            'body'              => $data->body,
            'occurred_at'       => $data->occurredAt ?? now()->toDateTimeString(),
            'lead_id'           => $data->leadId,
            'company_id'        => $data->companyId,
            'contact_person_id' => $data->contactPersonId,
            'opportunity_id'    => $data->opportunityId,
        ]);

        $this->activities->save($activity);

        event(new ActivityCreated($activity));

        return $activity;
    }
}
