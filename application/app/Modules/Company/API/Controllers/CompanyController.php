<?php

namespace App\Modules\Company\API\Controllers;

use App\Modules\Company\API\Requests\StoreCompanyRequest;
use App\Modules\Company\API\Resources\CompanyResource;
use App\Modules\Company\Application\Actions\CreateCompanyAction;
use App\Modules\Company\Application\DTOs\CreateCompanyData;
use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CreateCompanyAction $action,
    ) {}

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $data = new CreateCompanyData(
            name:     $request->input('name'),
            type:     CompanyType::from($request->input('type')),
            status:   $request->has('status')
                          ? CompanyStatus::from($request->input('status'))
                          : CompanyStatus::New,
            industry: $request->input('industry'),
            phone:    $request->input('phone'),
            email:    $request->input('email'),
            website:  $request->input('website'),
            address:  $request->input('address'),
            city:     $request->input('city'),
            country:  $request->input('country'),
        );

        $company = $this->action->execute($data);

        return (new CompanyResource($company))
            ->response()
            ->setStatusCode(201);
    }
}
