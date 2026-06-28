<?php

namespace App\Modules\Company\Application\Actions;

use App\Modules\Company\Application\DTOs\CreateCompanyData;
use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Events\CompanyCreated;
use App\Modules\Company\Domain\Models\Company;

class CreateCompanyAction
{
    public function __construct(
        private readonly CompanyRepository $companies,
    ) {}

    public function execute(CreateCompanyData $data): Company
    {
        $company = new Company();
        $company->fill([
            'name'        => $data->name,
            'type'        => $data->type,
            'status'      => $data->status,
            'industry'    => $data->industry,
            'phone'       => $data->phone,
            'email'       => $data->email,
            'website'     => $data->website,
            'address'     => $data->address,
            'city'        => $data->city,
            'country'     => $data->country,
            'assigned_to' => $data->assignedTo,
        ]);

        $this->companies->save($company);

        event(new CompanyCreated($company));

        return $company;
    }
}
