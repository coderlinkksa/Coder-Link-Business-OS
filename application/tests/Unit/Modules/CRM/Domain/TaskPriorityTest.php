<?php

namespace Tests\Unit\Modules\CRM\Domain;

use App\Modules\CRM\Domain\Enums\TaskPriority;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskPriorityTest extends TestCase
{
    #[Test]
    public function it_has_exactly_four_priorities(): void
    {
        $this->assertCount(4, TaskPriority::cases());
    }

    #[Test]
    public function priorities_can_be_created_from_string_value(): void
    {
        $this->assertSame(TaskPriority::Low,    TaskPriority::from('low'));
        $this->assertSame(TaskPriority::Normal, TaskPriority::from('normal'));
        $this->assertSame(TaskPriority::High,   TaskPriority::from('high'));
        $this->assertSame(TaskPriority::Urgent, TaskPriority::from('urgent'));
    }

    #[Test]
    public function each_priority_has_a_human_readable_label(): void
    {
        $this->assertSame('Low',    TaskPriority::Low->label());
        $this->assertSame('Normal', TaskPriority::Normal->label());
        $this->assertSame('High',   TaskPriority::High->label());
        $this->assertSame('Urgent', TaskPriority::Urgent->label());
    }

    #[Test]
    public function only_urgent_reports_as_urgent(): void
    {
        $this->assertTrue(TaskPriority::Urgent->isUrgent());
        $this->assertFalse(TaskPriority::High->isUrgent());
        $this->assertFalse(TaskPriority::Normal->isUrgent());
        $this->assertFalse(TaskPriority::Low->isUrgent());
    }
}
