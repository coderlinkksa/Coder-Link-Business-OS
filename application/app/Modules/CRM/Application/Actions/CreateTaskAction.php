<?php

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Application\DTOs\CreateTaskData;
use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Events\TaskCreated;
use App\Modules\CRM\Domain\Models\Task;

class CreateTaskAction
{
    public function __construct(
        private readonly TaskRepository $tasks,
    ) {}

    public function execute(CreateTaskData $data): Task
    {
        $task = new Task();
        $task->fill([
            'title'             => $data->title,
            'description'       => $data->description,
            'status'            => TaskStatus::Pending,
            'priority'          => $data->priority,
            'due_at'            => $data->dueAt,
            'lead_id'           => $data->leadId,
            'company_id'        => $data->companyId,
            'contact_person_id' => $data->contactPersonId,
            'opportunity_id'    => $data->opportunityId,
            'assigned_to'       => $data->assignedTo,
        ]);

        $this->tasks->save($task);

        event(new TaskCreated($task));

        return $task;
    }
}
