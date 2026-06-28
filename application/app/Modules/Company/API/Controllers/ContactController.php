<?php

namespace App\Modules\Company\API\Controllers;

use App\Modules\Company\API\Requests\StoreContactRequest;
use App\Modules\Company\API\Resources\ContactResource;
use App\Modules\Company\Application\Actions\CreateContactAction;
use App\Modules\Company\Application\DTOs\CreateContactData;
use App\Modules\Company\Domain\Enums\ContactRole;
use App\Modules\Company\Domain\Exceptions\CompanyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ContactController extends Controller
{
    public function __construct(
        private readonly CreateContactAction $action,
    ) {}

    public function store(StoreContactRequest $request, string $companyId): JsonResponse
    {
        try {
            $data = new CreateContactData(
                companyId: $companyId,
                firstName: $request->input('first_name'),
                lastName:  $request->input('last_name'),
                role:      ContactRole::from($request->input('role')),
                email:     $request->input('email'),
                phone:     $request->input('phone'),
                isPrimary: $request->boolean('is_primary', false),
            );

            $contact = $this->action->execute($data);

            return (new ContactResource($contact))
                ->response()
                ->setStatusCode(201);
        } catch (CompanyNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
