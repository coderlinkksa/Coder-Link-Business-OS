<?php

namespace App\Modules\Sales\API\Controllers;

use App\Modules\Sales\API\Requests\StoreOpportunityRequest;
use App\Modules\Sales\API\Resources\OpportunityResource;
use App\Modules\Sales\Application\Actions\CreateOpportunityAction;
use App\Modules\Sales\Application\DTOs\CreateOpportunityData;
use App\Modules\Sales\Domain\Enums\OpportunityStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class OpportunityController extends Controller
{
    public function __construct(
        private readonly CreateOpportunityAction $action,
    ) {}

    public function store(StoreOpportunityRequest $request): JsonResponse
    {
        $data = new CreateOpportunityData(
            companyId:         $request->input('company_id'),
            title:             $request->input('title'),
            stage:             $request->has('stage')
                                   ? OpportunityStage::from($request->input('stage'))
                                   : OpportunityStage::Qualification,
            leadId:            $request->input('lead_id'),
            contactPersonId:   $request->input('contact_person_id'),
            valueMinorUnits:   $request->input('value_minor_units'),
            probability:       $request->input('probability'),
            expectedCloseDate: $request->input('expected_close_date'),
            notes:             $request->input('notes'),
        );

        $opportunity = $this->action->execute($data);

        return (new OpportunityResource($opportunity))
            ->response()
            ->setStatusCode(201);
    }
}
