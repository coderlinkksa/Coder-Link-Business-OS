<?php

namespace App\Modules\Sales\Application\DTOs;

use App\Modules\Sales\Domain\Enums\OpportunityStage;

final readonly class CreateOpportunityData
{
    public function __construct(
        public int            $companyId,
        public string         $title,
        public OpportunityStage $stage          = OpportunityStage::Qualification,
        public ?int           $leadId           = null,
        public ?int           $contactPersonId  = null,
        public ?int           $valueMinorUnits  = null,
        public ?int           $probability      = null,
        public ?string        $expectedCloseDate = null,
        public ?int           $assignedTo       = null,
        public ?string        $notes            = null,
    ) {}
}
