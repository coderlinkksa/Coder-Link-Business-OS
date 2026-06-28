<?php

namespace App\Modules\CRM\Application\DTOs;

use App\Modules\CRM\Domain\Enums\LeadSource;

final readonly class CreateLeadData
{
    public function __construct(
        public string     $name,
        public LeadSource $source,
        public ?string    $email            = null,
        public ?string    $phone            = null,
        public ?string    $serviceRequested = null,
        public int|string|null $companyId        = null,
        public int|string|null $contactPersonId  = null,
        public int|string|null $assignedTo       = null,
        public ?string    $notes            = null,
    ) {}
}
