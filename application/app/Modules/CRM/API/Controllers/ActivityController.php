<?php

namespace App\Modules\CRM\API\Controllers;

use App\Modules\CRM\API\Requests\StoreActivityRequest;
use App\Modules\CRM\API\Resources\ActivityResource;
use App\Modules\CRM\Application\Actions\CreateActivityAction;
use App\Modules\CRM\Application\DTOs\CreateActivityData;
use App\Modules\CRM\Domain\Enums\ActivityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ActivityController extends Controller
{
    public function __construct(
        private readonly CreateActivityAction $action,
    ) {}

    public function store(StoreActivityRequest $request): JsonResponse
    {
        $data = new CreateActivityData(
            type:             ActivityType::from($request->input('type')),
            subject:          $request->input('subject'),
            body:             $request->input('body'),
            occurredAt:       $request->input('occurred_at'),
            leadId:           $request->input('lead_id'),
            companyId:        $request->input('company_id'),
            contactPersonId:  $request->input('contact_person_id'),
            opportunityId:    $request->input('opportunity_id'),
        );

        $activity = $this->action->execute($data);

        return (new ActivityResource($activity))
            ->response()
            ->setStatusCode(201);
    }
}
