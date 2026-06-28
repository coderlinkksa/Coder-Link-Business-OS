<?php

namespace App\Modules\CRM\API\Controllers;

use App\Modules\CRM\API\Requests\ConvertLeadRequest;
use App\Modules\CRM\API\Requests\StoreLeadRequest;
use App\Modules\CRM\API\Resources\LeadResource;
use App\Modules\CRM\Application\Actions\ConvertLeadToOpportunityAction;
use App\Modules\CRM\Application\Actions\CreateLeadAction;
use App\Modules\CRM\Application\DTOs\ConvertLeadData;
use App\Modules\CRM\Application\DTOs\CreateLeadData;
use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Exceptions\LeadAlreadyConvertedException;
use App\Modules\CRM\Domain\Exceptions\LeadNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class LeadController extends Controller
{
    public function __construct(
        private readonly CreateLeadAction             $createLead,
        private readonly ConvertLeadToOpportunityAction $convertLead,
    ) {}

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $data = new CreateLeadData(
            name:             $request->input('name'),
            source:           LeadSource::from($request->input('source')),
            email:            $request->input('email'),
            phone:            $request->input('phone'),
            serviceRequested: $request->input('service_requested'),
            companyId:        $request->input('company_id'),
            contactPersonId:  $request->input('contact_person_id'),
            notes:            $request->input('notes'),
        );

        $lead = $this->createLead->execute($data);

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }

    public function convert(ConvertLeadRequest $request, string $leadId): JsonResponse
    {
        try {
            $data = new ConvertLeadData(
                leadId:           $leadId,
                companyId:        $request->input('company_id'),
                opportunityTitle: $request->input('opportunity_title'),
                contactPersonId:  $request->input('contact_person_id'),
                valueMinorUnits:  $request->input('value_minor_units'),
                probability:      $request->input('probability'),
                expectedCloseDate: $request->input('expected_close_date'),
                notes:            $request->input('notes'),
            );

            $lead = $this->convertLead->execute($data);

            return (new LeadResource($lead))
                ->response()
                ->setStatusCode(200);
        } catch (LeadNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (LeadAlreadyConvertedException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
}
