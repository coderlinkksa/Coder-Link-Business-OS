<?php

namespace App\Modules\CRM\Application\DTOs;

use App\Modules\CRM\Domain\Enums\TaskPriority;

final readonly class CreateTaskData
{
    public function __construct(
        public string       $title,
        public TaskPriority $priority         = TaskPriority::Normal,
        public ?string      $description      = null,
        public ?string      $dueAt            = null,
        public int|string|null $leadId           = null,
        public int|string|null $companyId        = null,
        public int|string|null $contactPersonId  = null,
        public int|string|null $opportunityId    = null,
        public int|string|null $assignedTo       = null,
    ) {}
}
