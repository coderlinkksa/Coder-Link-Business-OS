<?php

namespace Tests\Unit\Modules\CRM\Application;

use App\Modules\CRM\Application\Actions\CreateTaskAction;
use App\Modules\CRM\Application\DTOs\CreateTaskData;
use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Events\TaskCreated;
use App\Modules\CRM\Domain\Models\Task;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateTaskActionTest extends TestCase
{
    private TaskRepository $repository;
    private CreateTaskAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements TaskRepository {
            public ?Task $saved = null;

            public function findById(int|string $id): ?Task { return null; }

            public function save(Task $task): void
            {
                $task->id    = 1;
                $this->saved = $task;
            }
        };

        $this->action = new CreateTaskAction($this->repository);
    }

    #[Test]
    public function it_creates_a_task_with_pending_status(): void
    {
        Event::fake();

        $task = $this->action->execute(new CreateTaskData(
            title:  'Call back prospect tomorrow',
            leadId: 3,
        ));

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame('Call back prospect tomorrow', $task->title);
        $this->assertSame(TaskStatus::Pending, $task->status);
    }

    #[Test]
    public function it_defaults_priority_to_normal(): void
    {
        Event::fake();

        $task = $this->action->execute(new CreateTaskData(
            title:  'Send proposal',
            leadId: 1,
        ));

        $this->assertSame(TaskPriority::Normal, $task->priority);
    }

    #[Test]
    public function it_fires_task_created_event(): void
    {
        Event::fake();

        $task = $this->action->execute(new CreateTaskData(
            title:    'Schedule discovery meeting',
            priority: TaskPriority::High,
            leadId:   5,
        ));

        Event::assertDispatched(
            TaskCreated::class,
            fn ($e) => $e->task === $task,
        );
    }

    #[Test]
    public function it_persists_the_task_through_the_repository(): void
    {
        Event::fake();

        $this->action->execute(new CreateTaskData(
            title:  'Prepare proposal',
            leadId: 2,
        ));

        $this->assertNotNull($this->repository->saved);
        $this->assertSame('Prepare proposal', $this->repository->saved->title);
    }

    #[Test]
    public function it_maps_all_optional_fields(): void
    {
        Event::fake();

        $task = $this->action->execute(new CreateTaskData(
            title:           'Full task',
            priority:        TaskPriority::Urgent,
            description:     'Must be done before the meeting.',
            dueAt:           '2026-07-01 09:00:00',
            leadId:          1,
            companyId:       2,
            contactPersonId: 3,
            opportunityId:   4,
            assignedTo:      5,
        ));

        $this->assertSame('Must be done before the meeting.', $task->description);
        $this->assertSame(1, $task->lead_id);
        $this->assertSame(2, $task->company_id);
        $this->assertSame(3, $task->contact_person_id);
        $this->assertSame(4, $task->opportunity_id);
        $this->assertSame(5, $task->assigned_to);
        $this->assertSame(TaskPriority::Urgent, $task->priority);
    }
}
