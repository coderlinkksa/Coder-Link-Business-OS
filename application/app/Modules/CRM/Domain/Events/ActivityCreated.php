<?php

namespace App\Modules\CRM\Domain\Events;

use App\Modules\CRM\Domain\Models\Activity;
use App\Shared\Events\BaseDomainEvent;

class ActivityCreated extends BaseDomainEvent
{
    public function __construct(
        public readonly Activity $activity,
    ) {
        parent::__construct();
    }

    public function aggregateId(): int|string
    {
        return $this->activity->id;
    }
}
