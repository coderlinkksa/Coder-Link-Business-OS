<?php

namespace Tests\Feature\Modules\CRM;

use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    // ── POST /api/tasks ───────────────────────────────────────────────────────

    #[Test]
    public function it_creates_a_task_and_returns_201(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Schedule follow-up call',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'status', 'priority',
                    'due_at', 'completed_at', 'lead_id', 'company_id',
                    'contact_person_id', 'opportunity_id', 'assigned_to', 'created_at',
                ],
            ])
            ->assertJsonPath('data.title', 'Schedule follow-up call')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.priority', 'normal')
            ->assertJsonPath('data.completed_at', null);
    }

    #[Test]
    public function it_persists_the_task_to_the_database(): void
    {
        $this->postJson('/api/tasks', [
            'title'    => 'Prepare proposal',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('tasks', [
            'title'    => 'Prepare proposal',
            'priority' => 'high',
            'status'   => 'pending',
        ]);
    }

    #[Test]
    public function it_returns_a_uuid_as_the_task_id(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'UUID task',
        ]);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id,
        );
    }

    #[Test]
    public function it_defaults_priority_to_normal_when_not_provided(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Default priority task',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.priority', 'normal');
    }

    #[Test]
    public function it_accepts_all_optional_task_fields(): void
    {
        $leadUuid     = 'aaaaaaaa-0000-0000-0000-000000000001';
        $assigneeUuid = 'bbbbbbbb-0000-0000-0000-000000000001';

        $response = $this->postJson('/api/tasks', [
            'title'       => 'Full task',
            'description' => 'Send the contract for review.',
            'priority'    => 'urgent',
            'due_at'      => '2026-12-31 17:00:00',
            'lead_id'     => $leadUuid,
            'assigned_to' => $assigneeUuid,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.description', 'Send the contract for review.')
            ->assertJsonPath('data.priority', 'urgent')
            ->assertJsonPath('data.lead_id', $leadUuid)
            ->assertJsonPath('data.assigned_to', $assigneeUuid);

        $this->assertNotNull($response->json('data.due_at'));
    }

    #[Test]
    public function it_can_link_to_all_entity_types(): void
    {
        $leadUuid    = 'aaaaaaaa-0000-0000-0000-000000000001';
        $companyUuid = 'bbbbbbbb-0000-0000-0000-000000000001';
        $contactUuid = 'cccccccc-0000-0000-0000-000000000001';
        $oppUuid     = 'dddddddd-0000-0000-0000-000000000001';

        $response = $this->postJson('/api/tasks', [
            'title'             => 'Multi-linked task',
            'lead_id'           => $leadUuid,
            'company_id'        => $companyUuid,
            'contact_person_id' => $contactUuid,
            'opportunity_id'    => $oppUuid,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.lead_id', $leadUuid)
            ->assertJsonPath('data.company_id', $companyUuid)
            ->assertJsonPath('data.contact_person_id', $contactUuid)
            ->assertJsonPath('data.opportunity_id', $oppUuid);
    }

    #[Test]
    public function it_returns_422_when_title_is_missing(): void
    {
        $response = $this->postJson('/api/tasks', [
            'priority' => 'high',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function it_returns_422_when_priority_is_invalid(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Bad Priority Task',
            'priority' => 'extreme',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    #[Test]
    public function it_returns_422_when_assigned_to_is_not_a_uuid(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'       => 'Bad assignee',
            'assigned_to' => 'not-a-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    }

    #[Test]
    public function it_returns_422_when_due_at_format_is_wrong(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'  => 'Bad due date',
            'due_at' => '2026-12-31',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_at']);
    }

    // ── POST /api/tasks/{id}/complete ─────────────────────────────────────────

    #[Test]
    public function it_completes_a_task_and_returns_200(): void
    {
        $task = Task::create([
            'title'    => 'Task to complete',
            'status'   => TaskStatus::Pending->value,
            'priority' => TaskPriority::Normal->value,
        ]);

        $response = $this->postJson("/api/tasks/{$task->getKey()}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        $this->assertNotNull($response->json('data.completed_at'));
    }

    #[Test]
    public function it_persists_completed_status_to_the_database(): void
    {
        $task = Task::create([
            'title'    => 'DB Complete Task',
            'status'   => TaskStatus::Pending->value,
            'priority' => TaskPriority::Normal->value,
        ]);

        $this->postJson("/api/tasks/{$task->getKey()}/complete");

        $this->assertDatabaseHas('tasks', [
            'id'     => $task->getKey(),
            'status' => 'completed',
        ]);
    }

    #[Test]
    public function it_returns_completed_at_timestamp_in_the_response(): void
    {
        $task = Task::create([
            'title'    => 'Timestamped Completion',
            'status'   => TaskStatus::Pending->value,
            'priority' => TaskPriority::Normal->value,
        ]);

        $before   = now()->subSecond()->toISOString();
        $response = $this->postJson("/api/tasks/{$task->getKey()}/complete");
        $after    = now()->addSecond()->toISOString();

        $completedAt = $response->json('data.completed_at');
        $this->assertNotNull($completedAt);
        $this->assertTrue($completedAt >= $before && $completedAt <= $after);
    }

    #[Test]
    public function it_returns_404_when_task_does_not_exist(): void
    {
        $response = $this->postJson('/api/tasks/00000000-0000-0000-0000-000000000000/complete');

        $response->assertStatus(404)
            ->assertJsonPath('message', fn ($v) => str_contains($v, 'not found'));
    }

    #[Test]
    public function it_returns_409_when_task_is_already_completed(): void
    {
        $task = Task::create([
            'title'    => 'Already Done',
            'status'   => TaskStatus::Completed->value,
            'priority' => TaskPriority::Normal->value,
        ]);

        $response = $this->postJson("/api/tasks/{$task->getKey()}/complete");

        $response->assertStatus(409);
    }
}
