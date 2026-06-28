<?php

namespace App\Modules\Company\Domain\Events;

use App\Modules\Company\Domain\Models\Company;
use App\Shared\Events\BaseDomainEvent;

class CompanyCreated extends BaseDomainEvent
{
    public function __construct(
        public readonly Company $company,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->company->id;
    }
}
