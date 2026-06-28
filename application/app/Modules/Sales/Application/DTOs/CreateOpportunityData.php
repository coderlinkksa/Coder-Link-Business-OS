<?php

namespace App\Modules\Sales\Application\DTOs;

use App\Modules\Sales\Domain\Enums\OpportunityStage;

final readonly class CreateOpportunityData
{
    public function __construct(
        public int|string       $companyId,
        public string           $title,
        public OpportunityStage $stage             = OpportunityStage::Qualification,
        public int|string|null  $leadId            = null,
        public int|string|null  $contactPersonId   = null,
        public ?int             $valueMinorUnits   = null,
        public ?int             $probability       = null,
        public ?string          $expectedCloseDate = null,
        public int|string|null  $assignedTo        = null,
        public ?string        $notes            = null,
    ) {}
}
