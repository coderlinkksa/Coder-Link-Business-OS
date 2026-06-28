<?php

namespace App\Modules\CRM\API\Controllers;

use App\Modules\CRM\API\Requests\StoreTaskRequest;
use App\Modules\CRM\API\Resources\TaskResource;
use App\Modules\CRM\Application\Actions\CreateTaskAction;
use App\Modules\CRM\Application\Actions\MarkTaskCompletedAction;
use App\Modules\CRM\Application\DTOs\CreateTaskData;
use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Exceptions\TaskAlreadyCompletedException;
use App\Modules\CRM\Domain\Exceptions\TaskNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TaskController extends Controller
{
    public function __construct(
        private readonly CreateTaskAction        $createTask,
        private readonly MarkTaskCompletedAction $markCompleted,
    ) {}

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = new CreateTaskData(
            title:            $request->input('title'),
            priority:         $request->has('priority')
                                  ? TaskPriority::from($request->input('priority'))
                                  : TaskPriority::Normal,
            description:      $request->input('description'),
            dueAt:            $request->input('due_at'),
            leadId:           $request->input('lead_id'),
            companyId:        $request->input('company_id'),
            contactPersonId:  $request->input('contact_person_id'),
            opportunityId:    $request->input('opportunity_id'),
            assignedTo:       $request->input('assigned_to'),
        );

        $task = $this->createTask->execute($data);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function complete(string $taskId): JsonResponse
    {
        try {
            $task = $this->markCompleted->execute($taskId);

            return (new TaskResource($task))
                ->response()
                ->setStatusCode(200);
        } catch (TaskNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (TaskAlreadyCompletedException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
}
