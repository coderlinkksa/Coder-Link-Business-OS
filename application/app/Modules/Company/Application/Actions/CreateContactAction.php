<?php

namespace App\Modules\Company\Application\Actions;

use App\Modules\Company\Application\DTOs\CreateContactData;
use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Contracts\ContactRepository;
use App\Modules\Company\Domain\Events\ContactCreated;
use App\Modules\Company\Domain\Exceptions\CompanyNotFoundException;
use App\Modules\Company\Domain\Models\ContactPerson;

class CreateContactAction
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly ContactRepository $contacts,
    ) {}

    public function execute(CreateContactData $data): ContactPerson
    {
        $company = $this->companies->findById($data->companyId);

        if ($company === null) {
            throw new CompanyNotFoundException($data->companyId);
        }

        $contact = new ContactPerson();
        $contact->fill([
            'company_id'  => $data->companyId,
            'first_name'  => $data->firstName,
            'last_name'   => $data->lastName,
            'role'        => $data->role,
            'email'       => $data->email,
            'phone'       => $data->phone,
            'is_primary'  => $data->isPrimary,
            'assigned_to' => $data->assignedTo,
        ]);

        $this->contacts->save($contact);

        event(new ContactCreated($contact));

        return $contact;
    }
}
