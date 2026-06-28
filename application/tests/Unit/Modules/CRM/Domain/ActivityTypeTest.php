<?php

namespace Tests\Unit\Modules\CRM\Domain;

use App\Modules\CRM\Domain\Enums\ActivityType;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityTypeTest extends TestCase
{
    #[Test]
    public function it_has_exactly_five_types(): void
    {
        $this->assertCount(5, ActivityType::cases());
    }

    #[Test]
    public function types_can_be_created_from_string_value(): void
    {
        $this->assertSame(ActivityType::Call,    ActivityType::from('call'));
        $this->assertSame(ActivityType::Meeting, ActivityType::from('meeting'));
        $this->assertSame(ActivityType::Email,   ActivityType::from('email'));
        $this->assertSame(ActivityType::Note,    ActivityType::from('note'));
        $this->assertSame(ActivityType::Other,   ActivityType::from('other'));
    }

    #[Test]
    public function each_type_has_a_human_readable_label(): void
    {
        $this->assertSame('Call',    ActivityType::Call->label());
        $this->assertSame('Meeting', ActivityType::Meeting->label());
        $this->assertSame('Email',   ActivityType::Email->label());
        $this->assertSame('Note',    ActivityType::Note->label());
        $this->assertSame('Other',   ActivityType::Other->label());
    }
}
