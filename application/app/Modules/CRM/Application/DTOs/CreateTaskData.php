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
        public ?int         $leadId           = null,
        public ?int         $companyId        = null,
        public ?int         $contactPersonId  = null,
        public ?int         $opportunityId    = null,
        public ?int         $assignedTo       = null,
    ) {}
}
