<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Models\Task;
use App\Modules\CRM\Infrastructure\Repositories\EloquentTaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeTask(array $attributes = []): Task
    {
        return Task::create(array_merge([
            'title'    => 'Test task',
            'status'   => TaskStatus::Pending->value,
            'priority' => TaskPriority::Normal->value,
        ], $attributes));
    }

    // ── UUID primary keys ─────────────────────────────────────────────────────

    #[Test]
    public function task_primary_key_is_a_uuid(): void
    {
        $task = $this->makeTask();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $task->getKey(),
        );
    }

    #[Test]
    public function each_task_receives_a_unique_uuid(): void
    {
        $a = $this->makeTask(['title' => 'Task A']);
        $b = $this->makeTask(['title' => 'Task B']);

        $this->assertNotSame($a->getKey(), $b->getKey());
    }

    // ── Column persistence ────────────────────────────────────────────────────

    #[Test]
    public function task_can_be_retrieved_by_uuid(): void
    {
        $saved = $this->makeTask(['title' => 'Lookup task']);
        $found = Task::find($saved->getKey());

        $this->assertNotNull($found);
        $this->assertSame('Lookup task', $found->title);
    }

    #[Test]
    public function task_status_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeTask(['status' => TaskStatus::InProgress->value]);

        $this->assertSame(TaskStatus::InProgress, Task::first()->status);
    }

    #[Test]
    public function task_priority_is_cast_to_enum_after_retrieval(): void
    {
        $this->makeTask(['priority' => TaskPriority::Urgent->value]);

        $this->assertSame(TaskPriority::Urgent, Task::first()->priority);
    }

    #[Test]
    public function task_status_defaults_to_pending(): void
    {
        Task::create(['title' => 'Default status task']);

        $this->assertSame(TaskStatus::Pending, Task::first()->status);
    }

    #[Test]
    public function task_priority_defaults_to_normal(): void
    {
        Task::create(['title' => 'Default priority task']);

        $this->assertSame(TaskPriority::Normal, Task::first()->priority);
    }

    #[Test]
    public function task_description_persists_correctly(): void
    {
        $this->makeTask(['description' => 'Send the updated proposal PDF.']);

        $this->assertSame('Send the updated proposal PDF.', Task::first()->description);
    }

    // ── Due date ──────────────────────────────────────────────────────────────

    #[Test]
    public function due_at_persists_and_is_cast_to_datetime(): void
    {
        $this->makeTask(['due_at' => '2026-12-31 17:00:00']);

        $found = Task::first();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $found->due_at);
        $this->assertSame('2026-12-31 17:00:00', $found->due_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function due_at_is_null_when_not_provided(): void
    {
        $this->makeTask();

        $this->assertNull(Task::first()->due_at);
    }

    // ── Completion ────────────────────────────────────────────────────────────

    #[Test]
    public function completed_at_is_null_on_a_new_task(): void
    {
        $this->makeTask();

        $this->assertNull(Task::first()->completed_at);
    }

    #[Test]
    public function mark_completed_sets_status_and_completed_at(): void
    {
        $task = $this->makeTask();
        $task->markCompleted();
        $task->save();

        $found = Task::find($task->getKey());
        $this->assertSame(TaskStatus::Completed, $found->status);
        $this->assertNotNull($found->completed_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $found->completed_at);
    }

    #[Test]
    public function is_overdue_returns_false_for_future_due_date(): void
    {
        $task = $this->makeTask(['due_at' => now()->addYear()->toDateTimeString()]);

        $this->assertFalse(Task::find($task->getKey())->isOverdue());
    }

    #[Test]
    public function is_overdue_returns_true_for_past_due_date_on_pending_task(): void
    {
        $task = $this->makeTask(['due_at' => now()->subDay()->toDateTimeString()]);

        $this->assertTrue(Task::find($task->getKey())->isOverdue());
    }

    #[Test]
    public function is_overdue_returns_false_for_completed_task_even_if_past_due(): void
    {
        $task = $this->makeTask(['due_at' => now()->subDay()->toDateTimeString()]);
        $task->markCompleted();
        $task->save();

        $this->assertFalse(Task::find($task->getKey())->isOverdue());
    }

    // ── Related-entity linkage ────────────────────────────────────────────────

    #[Test]
    public function task_can_be_linked_to_a_lead(): void
    {
        $leadUuid = 'aaaaaaaa-0000-0000-0000-000000000001';
        $this->makeTask(['lead_id' => $leadUuid]);

        $this->assertSame($leadUuid, Task::first()->lead_id);
    }

    #[Test]
    public function task_can_be_linked_to_a_company(): void
    {
        $companyUuid = 'bbbbbbbb-0000-0000-0000-000000000001';
        $this->makeTask(['company_id' => $companyUuid]);

        $this->assertSame($companyUuid, Task::first()->company_id);
    }

    #[Test]
    public function task_can_be_linked_to_a_contact_person(): void
    {
        $contactUuid = 'cccccccc-0000-0000-0000-000000000001';
        $this->makeTask(['contact_person_id' => $contactUuid]);

        $this->assertSame($contactUuid, Task::first()->contact_person_id);
    }

    #[Test]
    public function task_can_be_linked_to_an_opportunity(): void
    {
        $oppUuid = 'dddddddd-0000-0000-0000-000000000001';
        $this->makeTask(['opportunity_id' => $oppUuid]);

        $this->assertSame($oppUuid, Task::first()->opportunity_id);
    }

    // ── Soft deletes ──────────────────────────────────────────────────────────

    #[Test]
    public function soft_deleting_a_task_sets_deleted_at(): void
    {
        $task = $this->makeTask();
        $task->delete();

        $this->assertNotNull(Task::withTrashed()->find($task->getKey())->deleted_at);
    }

    #[Test]
    public function soft_deleted_task_is_excluded_from_default_queries(): void
    {
        $task = $this->makeTask();
        $task->delete();

        $this->assertNull(Task::find($task->getKey()));
        $this->assertNotNull(Task::withTrashed()->find($task->getKey()));
    }

    // ── Index-backed queries ──────────────────────────────────────────────────

    #[Test]
    public function tasks_can_be_filtered_by_status(): void
    {
        $this->makeTask(['title' => 'Pending 1',  'status' => TaskStatus::Pending->value]);
        $this->makeTask(['title' => 'Pending 2',  'status' => TaskStatus::Pending->value]);
        $this->makeTask(['title' => 'Completed 1','status' => TaskStatus::Completed->value]);

        $this->assertCount(2, Task::where('status', TaskStatus::Pending->value)->get());
    }

    #[Test]
    public function tasks_can_be_filtered_by_priority(): void
    {
        $this->makeTask(['title' => 'Urgent 1', 'priority' => TaskPriority::Urgent->value]);
        $this->makeTask(['title' => 'Normal 1', 'priority' => TaskPriority::Normal->value]);
        $this->makeTask(['title' => 'Urgent 2', 'priority' => TaskPriority::Urgent->value]);

        $this->assertCount(2, Task::where('priority', TaskPriority::Urgent->value)->get());
    }

    #[Test]
    public function tasks_can_be_filtered_by_assignee(): void
    {
        $assignee = 'eeeeeeee-0000-0000-0000-000000000001';

        $this->makeTask(['title' => 'Assigned 1', 'assigned_to' => $assignee]);
        $this->makeTask(['title' => 'Assigned 2', 'assigned_to' => $assignee]);
        $this->makeTask(['title' => 'Unassigned']);

        $this->assertCount(2, Task::where('assigned_to', $assignee)->get());
    }

    #[Test]
    public function tasks_can_be_filtered_by_due_date(): void
    {
        $this->makeTask(['title' => 'Due soon',  'due_at' => now()->addDay()->toDateTimeString()]);
        $this->makeTask(['title' => 'Overdue',   'due_at' => now()->subDay()->toDateTimeString()]);
        $this->makeTask(['title' => 'No due date']);

        $overdue = Task::where('due_at', '<', now())->get();
        $this->assertCount(1, $overdue);
        $this->assertSame('Overdue', $overdue->first()->title);
    }

    // ── Eloquent repository round-trips ───────────────────────────────────────

    #[Test]
    public function eloquent_task_repository_saves_and_finds_by_uuid(): void
    {
        $repo = new EloquentTaskRepository();
        $task = new Task();
        $task->forceFill([
            'title'    => 'Repo task',
            'status'   => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
        ]);
        $repo->save($task);

        $found = $repo->findById($task->getKey());
        $this->assertNotNull($found);
        $this->assertSame('Repo task', $found->title);
        $this->assertSame(TaskStatus::InProgress, $found->status);
        $this->assertSame(TaskPriority::High, $found->priority);
    }

    #[Test]
    public function repository_returns_null_for_nonexistent_uuid(): void
    {
        $repo = new EloquentTaskRepository();

        $this->assertNull($repo->findById('00000000-0000-0000-0000-000000000000'));
    }
}
