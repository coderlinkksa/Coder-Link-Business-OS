<?php

namespace App\Modules\CRM\Infrastructure\Repositories;

use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Domain\Models\Task;

class EloquentTaskRepository implements TaskRepository
{
    public function findById(int|string $id): ?Task
    {
        return Task::find($id);
    }

    public function save(Task $task): void
    {
        $task->save();
    }
}
