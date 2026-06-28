<?php

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Domain\Events\TaskCompleted;
use App\Modules\CRM\Domain\Exceptions\TaskNotFoundException;
use App\Modules\CRM\Domain\Models\Task;

class MarkTaskCompletedAction
{
    public function __construct(
        private readonly TaskRepository $tasks,
    ) {}

    public function execute(int|string $taskId): Task
    {
        $task = $this->tasks->findById($taskId);

        if ($task === null) {
            throw new TaskNotFoundException($taskId);
        }

        // Throws TaskAlreadyCompletedException if already in a terminal state.
        $task->markCompleted();

        $this->tasks->save($task);

        event(new TaskCompleted($task));

        return $task;
    }
}
