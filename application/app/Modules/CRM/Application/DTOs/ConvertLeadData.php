<?php

namespace App\Modules\CRM\Application\DTOs;

final readonly class ConvertLeadData
{
    public function __construct(
        public int     $leadId,
        public int     $companyId,
        public string  $opportunityTitle,
        public ?int    $contactPersonId   = null,
        public ?int    $valueMinorUnits   = null,
        public ?int    $probability       = null,
        public ?string $expectedCloseDate = null,
        public ?int    $assignedTo        = null,
        public ?string $notes             = null,
    ) {}
}
