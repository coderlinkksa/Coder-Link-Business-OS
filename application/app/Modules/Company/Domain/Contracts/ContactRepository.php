<?php

namespace App\Modules\Company\Domain\Contracts;

use App\Modules\Company\Domain\Models\ContactPerson;

interface ContactRepository
{
    public function findById(int $id): ?ContactPerson;

    public function save(ContactPerson $contact): void;
}
