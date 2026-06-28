<?php

namespace App\Modules\CRM\Domain\Events;

use App\Modules\CRM\Domain\Models\Task;
use App\Shared\Events\BaseDomainEvent;

class TaskCompleted extends BaseDomainEvent
{
    public function __construct(
        public readonly Task $task,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->task->id;
    }
}
