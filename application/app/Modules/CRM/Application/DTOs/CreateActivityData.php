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
        public int|string|null $leadId           = null,
        public int|string|null $companyId        = null,
        public int|string|null $contactPersonId  = null,
        public int|string|null $opportunityId    = null,
    ) {}
}
