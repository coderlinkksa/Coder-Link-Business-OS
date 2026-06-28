<?php

namespace App\Modules\CRM\Domain\Contracts;

use App\Modules\CRM\Domain\Models\Task;

interface TaskRepository
{
    public function findById(int|string $id): ?Task;

    public function save(Task $task): void;
}
