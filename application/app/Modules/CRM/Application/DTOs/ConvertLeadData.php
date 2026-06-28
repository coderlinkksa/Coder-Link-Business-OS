<?php

namespace App\Modules\CRM\Application\DTOs;

final readonly class ConvertLeadData
{
    public function __construct(
        public int|string      $leadId,
        public int|string      $companyId,
        public string          $opportunityTitle,
        public int|string|null $contactPersonId   = null,
        public ?int            $valueMinorUnits   = null,
        public ?int            $probability       = null,
        public ?string         $expectedCloseDate = null,
        public int|string|null $assignedTo        = null,
        public ?string $notes             = null,
    ) {}
}
