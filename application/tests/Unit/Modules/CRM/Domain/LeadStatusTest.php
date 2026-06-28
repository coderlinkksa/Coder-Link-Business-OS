<?php

namespace Tests\Unit\Modules\CRM\Domain;

use App\Modules\CRM\Domain\Enums\LeadStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadStatusTest extends TestCase
{
    #[Test]
    public function it_has_exactly_five_statuses(): void
    {
        $this->assertCount(5, LeadStatus::cases());
    }

    #[Test]
    public function statuses_can_be_created_from_string_value(): void
    {
        $this->assertSame(LeadStatus::New,          LeadStatus::from('new'));
        $this->assertSame(LeadStatus::Contacted,    LeadStatus::from('contacted'));
        $this->assertSame(LeadStatus::Qualified,    LeadStatus::from('qualified'));
        $this->assertSame(LeadStatus::Converted,    LeadStatus::from('converted'));
        $this->assertSame(LeadStatus::Disqualified, LeadStatus::from('disqualified'));
    }

    #[Test]
    public function each_status_has_a_human_readable_label(): void
    {
        $this->assertSame('New',          LeadStatus::New->label());
        $this->assertSame('Contacted',    LeadStatus::Contacted->label());
        $this->assertSame('Qualified',    LeadStatus::Qualified->label());
        $this->assertSame('Converted',    LeadStatus::Converted->label());
        $this->assertSame('Disqualified', LeadStatus::Disqualified->label());
    }

    #[Test]
    public function converted_and_disqualified_are_terminal(): void
    {
        $this->assertTrue(LeadStatus::Converted->isTerminal());
        $this->assertTrue(LeadStatus::Disqualified->isTerminal());
        $this->assertFalse(LeadStatus::New->isTerminal());
        $this->assertFalse(LeadStatus::Contacted->isTerminal());
        $this->assertFalse(LeadStatus::Qualified->isTerminal());
    }

    #[Test]
    public function only_qualified_lead_can_be_converted(): void
    {
        $this->assertTrue(LeadStatus::Qualified->canConvert());
        $this->assertFalse(LeadStatus::New->canConvert());
        $this->assertFalse(LeadStatus::Contacted->canConvert());
        $this->assertFalse(LeadStatus::Converted->canConvert());
        $this->assertFalse(LeadStatus::Disqualified->canConvert());
    }

    #[Test]
    public function new_contacted_and_qualified_leads_can_be_disqualified(): void
    {
        $this->assertTrue(LeadStatus::New->canDisqualify());
        $this->assertTrue(LeadStatus::Contacted->canDisqualify());
        $this->assertTrue(LeadStatus::Qualified->canDisqualify());
        $this->assertFalse(LeadStatus::Converted->canDisqualify());
        $this->assertFalse(LeadStatus::Disqualified->canDisqualify());
    }

    #[Test]
    public function lifecycle_transitions_follow_the_crm_pipeline(): void
    {
        $this->assertTrue(LeadStatus::New->canTransitionTo(LeadStatus::Contacted));
        $this->assertTrue(LeadStatus::New->canTransitionTo(LeadStatus::Disqualified));
        $this->assertFalse(LeadStatus::New->canTransitionTo(LeadStatus::Qualified));

        $this->assertTrue(LeadStatus::Contacted->canTransitionTo(LeadStatus::Qualified));
        $this->assertFalse(LeadStatus::Contacted->canTransitionTo(LeadStatus::Converted));

        $this->assertTrue(LeadStatus::Qualified->canTransitionTo(LeadStatus::Converted));
        $this->assertFalse(LeadStatus::Converted->canTransitionTo(LeadStatus::Disqualified));
    }
}
