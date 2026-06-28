<?php

namespace App\Modules\CRM\Application\DTOs;

use App\Modules\CRM\Domain\Enums\ActivityType;

final readonly class CreateActivityData
{
    public function __construct(
        public ActivityType $type,
        public string       $subject,
        public ?string      $body             = null,
        public ?string      $occurredAt       = null,
        public ?int         $leadId           = null,
        public ?int         $companyId        = null,
        public ?int         $contactPersonId  = null,
        public ?int         $opportunityId    = null,
    ) {}
}
