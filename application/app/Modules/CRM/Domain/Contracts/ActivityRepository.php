<?php

namespace App\Modules\CRM\Domain\Contracts;

use App\Modules\CRM\Domain\Models\Activity;

interface ActivityRepository
{
    public function findById(int|string $id): ?Activity;

    public function save(Activity $activity): void;
}
