<?php

namespace App\Modules\CRM\Infrastructure\Repositories;

use App\Modules\CRM\Domain\Contracts\ActivityRepository;
use App\Modules\CRM\Domain\Models\Activity;

class EloquentActivityRepository implements ActivityRepository
{
    public function findById(int $id): ?Activity
    {
        return Activity::find($id);
    }

    public function save(Activity $activity): void
    {
        $activity->save();
    }
}
