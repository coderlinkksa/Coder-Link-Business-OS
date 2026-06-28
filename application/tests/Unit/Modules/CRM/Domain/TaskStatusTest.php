<?php

namespace Tests\Unit\Modules\CRM\Domain;

use App\Modules\CRM\Domain\Enums\TaskStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskStatusTest extends TestCase
{
    #[Test]
    public function it_has_exactly_four_statuses(): void
    {
        $this->assertCount(4, TaskStatus::cases());
    }

    #[Test]
    public function statuses_can_be_created_from_string_value(): void
    {
        $this->assertSame(TaskStatus::Pending,    TaskStatus::from('pending'));
        $this->assertSame(TaskStatus::InProgress, TaskStatus::from('in_progress'));
        $this->assertSame(TaskStatus::Completed,  TaskStatus::from('completed'));
        $this->assertSame(TaskStatus::Cancelled,  TaskStatus::from('cancelled'));
    }

    #[Test]
    public function each_status_has_a_human_readable_label(): void
    {
        $this->assertSame('Pending',     TaskStatus::Pending->label());
        $this->assertSame('In Progress', TaskStatus::InProgress->label());
        $this->assertSame('Completed',   TaskStatus::Completed->label());
        $this->assertSame('Cancelled',   TaskStatus::Cancelled->label());
    }

    #[Test]
    public function completed_and_cancelled_are_terminal(): void
    {
        $this->assertTrue(TaskStatus::Completed->isTerminal());
        $this->assertTrue(TaskStatus::Cancelled->isTerminal());
        $this->assertFalse(TaskStatus::Pending->isTerminal());
        $this->assertFalse(TaskStatus::InProgress->isTerminal());
    }

    #[Test]
    public function only_completed_reports_as_completed(): void
    {
        $this->assertTrue(TaskStatus::Completed->isCompleted());
        $this->assertFalse(TaskStatus::Pending->isCompleted());
        $this->assertFalse(TaskStatus::InProgress->isCompleted());
        $this->assertFalse(TaskStatus::Cancelled->isCompleted());
    }

    #[Test]
    public function terminal_statuses_cannot_transition(): void
    {
        foreach (TaskStatus::cases() as $next) {
            $this->assertFalse(TaskStatus::Completed->canTransitionTo($next));
            $this->assertFalse(TaskStatus::Cancelled->canTransitionTo($next));
        }
    }

    #[Test]
    public function pending_task_can_start_complete_or_cancel(): void
    {
        $this->assertTrue(TaskStatus::Pending->canTransitionTo(TaskStatus::InProgress));
        $this->assertTrue(TaskStatus::Pending->canTransitionTo(TaskStatus::Completed));
        $this->assertTrue(TaskStatus::Pending->canTransitionTo(TaskStatus::Cancelled));
        $this->assertFalse(TaskStatus::Pending->canTransitionTo(TaskStatus::Pending));
    }

    #[Test]
    public function in_progress_task_can_complete_or_cancel(): void
    {
        $this->assertTrue(TaskStatus::InProgress->canTransitionTo(TaskStatus::Completed));
        $this->assertTrue(TaskStatus::InProgress->canTransitionTo(TaskStatus::Cancelled));
        $this->assertFalse(TaskStatus::InProgress->canTransitionTo(TaskStatus::Pending));
    }
}
