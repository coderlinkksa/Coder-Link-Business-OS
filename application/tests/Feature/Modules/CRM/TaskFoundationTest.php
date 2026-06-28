<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Application\Actions\CreateTaskAction;
use App\Modules\CRM\Application\Actions\MarkTaskCompletedAction;
use App\Modules\CRM\Application\DTOs\CreateTaskData;
use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Events\TaskCompleted;
use App\Modules\CRM\Domain\Events\TaskCreated;
use App\Modules\CRM\Domain\Exceptions\TaskAlreadyCompletedException;
use App\Modules\CRM\Domain\Exceptions\TaskNotFoundException;
use App\Modules\CRM\Domain\Models\Task;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for CRM Task creation and lifecycle.
 * Uses an in-memory repository — no database required at this stage.
 */
class TaskFoundationTest extends TestCase
{
    private InMemoryTaskRepository $taskRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskRepo = new InMemoryTaskRepository();
        $this->app->instance(TaskRepository::class, $this->taskRepo);
    }

    // ── CreateTaskAction ──────────────────────────────────────────────────────

    #[Test]
    public function create_task_action_resolves_from_container(): void
    {
        $action = $this->app->make(CreateTaskAction::class);
        $this->assertInstanceOf(CreateTaskAction::class, $action);
    }

    #[Test]
    public function creating_a_task_fires_task_created_event(): void
    {
        Event::fake();

        $action = $this->app->make(CreateTaskAction::class);

        $task = $action->execute(new CreateTaskData(
            title:  'Schedule follow-up call',
            leadId: 3,
        ));

        Event::assertDispatched(
            TaskCreated::class,
            fn ($e) => $e->task->title === 'Schedule follow-up call',
        );

        $this->assertSame(TaskStatus::Pending, $task->status);
    }

    #[Test]
    public function task_is_stored_in_repository_after_creation(): void
    {
        Event::fake();

        $action = $this->app->make(CreateTaskAction::class);

        $action->execute(new CreateTaskData(
            title:    'Prepare meeting agenda',
            priority: TaskPriority::High,
            leadId:   5,
        ));

        $this->assertCount(1, $this->taskRepo->all());
        $this->assertSame(TaskPriority::High, $this->taskRepo->all()[0]->priority);
    }

    // ── MarkTaskCompletedAction ───────────────────────────────────────────────

    #[Test]
    public function mark_task_completed_action_resolves_from_container(): void
    {
        $action = $this->app->make(MarkTaskCompletedAction::class);
        $this->assertInstanceOf(MarkTaskCompletedAction::class, $action);
    }

    #[Test]
    public function completing_a_task_sets_completed_status_and_timestamp(): void
    {
        Event::fake();

        $createAction = $this->app->make(CreateTaskAction::class);
        $task         = $createAction->execute(new CreateTaskData(
            title:  'Send contract for review',
            leadId: 1,
        ));

        $completeAction = $this->app->make(MarkTaskCompletedAction::class);
        $completed      = $completeAction->execute($task->id);

        $this->assertSame(TaskStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
    }

    #[Test]
    public function completing_a_task_fires_task_completed_event(): void
    {
        Event::fake();

        $createAction = $this->app->make(CreateTaskAction::class);
        $task         = $createAction->execute(new CreateTaskData(
            title:  'Close follow-up loop',
            leadId: 2,
        ));

        $completeAction = $this->app->make(MarkTaskCompletedAction::class);
        $completeAction->execute($task->id);

        Event::assertDispatched(
            TaskCompleted::class,
            fn ($e) => $e->task->id === $task->id,
        );
    }

    #[Test]
    public function completing_a_nonexistent_task_throws(): void
    {
        Event::fake();

        $action = $this->app->make(MarkTaskCompletedAction::class);

        $this->expectException(TaskNotFoundException::class);

        $action->execute(9999);
    }

    #[Test]
    public function completing_an_already_completed_task_throws(): void
    {
        Event::fake();

        $createAction = $this->app->make(CreateTaskAction::class);
        $task         = $createAction->execute(new CreateTaskData(
            title:  'Will be completed twice',
            leadId: 1,
        ));

        $completeAction = $this->app->make(MarkTaskCompletedAction::class);
        $completeAction->execute($task->id);

        $this->expectException(TaskAlreadyCompletedException::class);

        $completeAction->execute($task->id);
    }

    #[Test]
    public function completed_task_retains_completion_timestamp_after_re_fetch(): void
    {
        Event::fake();

        $createAction = $this->app->make(CreateTaskAction::class);
        $task         = $createAction->execute(new CreateTaskData(
            title:  'Timestamped completion',
            leadId: 1,
        ));

        $before         = now()->subSecond();
        $completeAction = $this->app->make(MarkTaskCompletedAction::class);
        $completeAction->execute($task->id);

        // Re-fetch from the in-memory store.
        $stored = $this->taskRepo->findById($task->id);
        $this->assertNotNull($stored);
        $this->assertSame(TaskStatus::Completed, $stored->status);
        $this->assertTrue($stored->completed_at->gte($before));
    }

    #[Test]
    public function task_created_event_carries_occurred_at_timestamp(): void
    {
        Event::fake();

        $action = $this->app->make(CreateTaskAction::class);
        $action->execute(new CreateTaskData(
            title:  'Timestamped task',
            leadId: 1,
        ));

        Event::assertDispatched(TaskCreated::class, function (TaskCreated $e) {
            return $e->occurredAt() instanceof \DateTimeImmutable;
        });
    }
}

// ── In-memory test double ─────────────────────────────────────────────────────

class InMemoryTaskRepository implements TaskRepository
{
    /** @var Task[] */
    private array $store  = [];
    private int   $nextId = 1;

    public function findById(int $id): ?Task
    {
        return $this->store[$id] ?? null;
    }

    public function save(Task $task): void
    {
        if (! isset($task->id) || $task->id === null) {
            $task->id = $this->nextId++;
        }
        $this->store[$task->id] = $task;
    }

    /** @return Task[] */
    public function all(): array
    {
        return array_values($this->store);
    }
}
