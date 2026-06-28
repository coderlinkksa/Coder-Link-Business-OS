<?php

namespace Tests\Unit\Modules\CRM\Application;

use App\Modules\CRM\Application\Actions\MarkTaskCompletedAction;
use App\Modules\CRM\Domain\Contracts\TaskRepository;
use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Events\TaskCompleted;
use App\Modules\CRM\Domain\Exceptions\TaskAlreadyCompletedException;
use App\Modules\CRM\Domain\Exceptions\TaskNotFoundException;
use App\Modules\CRM\Domain\Models\Task;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarkTaskCompletedActionTest extends TestCase
{
    private function makeRepo(?Task $task): TaskRepository
    {
        return new class ($task) implements TaskRepository {
            public ?Task $saved = null;

            public function __construct(private readonly ?Task $task) {}

            public function findById(int|string $id): ?Task { return $this->task; }

            public function save(Task $task): void { $this->saved = $task; }
        };
    }

    private function makePendingTask(int $id = 1): Task
    {
        $task = new Task();
        $task->id = $id;
        $task->fill([
            'title'    => 'Follow up with client',
            'status'   => TaskStatus::Pending,
            'priority' => TaskPriority::Normal,
        ]);
        return $task;
    }

    #[Test]
    public function it_marks_the_task_as_completed(): void
    {
        Event::fake();

        $task   = $this->makePendingTask();
        $action = new MarkTaskCompletedAction($this->makeRepo($task));

        $result = $action->execute(1);

        $this->assertSame(TaskStatus::Completed, $result->status);
    }

    #[Test]
    public function it_sets_the_completed_at_timestamp(): void
    {
        Event::fake();

        $before = now()->subSecond();
        $task   = $this->makePendingTask();
        $action = new MarkTaskCompletedAction($this->makeRepo($task));

        $result = $action->execute(1);

        $this->assertNotNull($result->completed_at);
        $this->assertTrue($result->completed_at->gte($before));
    }

    #[Test]
    public function it_fires_task_completed_event(): void
    {
        Event::fake();

        $task   = $this->makePendingTask();
        $action = new MarkTaskCompletedAction($this->makeRepo($task));

        $result = $action->execute(1);

        Event::assertDispatched(
            TaskCompleted::class,
            fn ($e) => $e->task === $result,
        );
    }

    #[Test]
    public function it_persists_the_updated_task(): void
    {
        Event::fake();

        $task = $this->makePendingTask();
        $repo = $this->makeRepo($task);
        (new MarkTaskCompletedAction($repo))->execute(1);

        $this->assertSame(TaskStatus::Completed, $repo->saved->status);
        $this->assertNotNull($repo->saved->completed_at);
    }

    #[Test]
    public function it_throws_when_task_does_not_exist(): void
    {
        Event::fake();

        $action = new MarkTaskCompletedAction($this->makeRepo(null));

        $this->expectException(TaskNotFoundException::class);

        $action->execute(999);
    }

    #[Test]
    public function it_throws_when_task_is_already_completed(): void
    {
        Event::fake();

        $task         = $this->makePendingTask();
        $task->status = TaskStatus::Completed;

        $action = new MarkTaskCompletedAction($this->makeRepo($task));

        $this->expectException(TaskAlreadyCompletedException::class);

        $action->execute(1);
    }

    #[Test]
    public function it_throws_when_task_is_cancelled(): void
    {
        Event::fake();

        $task         = $this->makePendingTask();
        $task->status = TaskStatus::Cancelled;

        $action = new MarkTaskCompletedAction($this->makeRepo($task));

        $this->expectException(TaskAlreadyCompletedException::class);

        $action->execute(1);
    }
}
