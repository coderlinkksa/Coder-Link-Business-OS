<?php

namespace App\Modules\Company\Infrastructure\Repositories;

use App\Modules\Company\Domain\Contracts\ContactRepository;
use App\Modules\Company\Domain\Models\ContactPerson;

class EloquentContactRepository implements ContactRepository
{
    public function findById(int|string $id): ?ContactPerson
    {
        return ContactPerson::find($id);
    }

    public function save(ContactPerson $contact): void
    {
        $contact->save();
    }
}
