<?php

namespace App\Modules\Company\Domain\Events;

use App\Modules\Company\Domain\Models\ContactPerson;
use App\Shared\Events\BaseDomainEvent;

class ContactCreated extends BaseDomainEvent
{
    public function __construct(
        public readonly ContactPerson $contact,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->contact->id;
    }
}
