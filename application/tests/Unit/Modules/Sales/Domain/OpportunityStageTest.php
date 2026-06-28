<?php

namespace Tests\Unit\Modules\Sales\Domain;

use App\Modules\Sales\Domain\Enums\OpportunityStage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityStageTest extends TestCase
{
    #[Test]
    public function it_has_exactly_eight_stages(): void
    {
        $this->assertCount(8, OpportunityStage::cases());
    }

    #[Test]
    public function stages_can_be_created_from_string_value(): void
    {
        $this->assertSame(OpportunityStage::Qualification,         OpportunityStage::from('qualification'));
        $this->assertSame(OpportunityStage::DiscoveryMeeting,      OpportunityStage::from('discovery_meeting'));
        $this->assertSame(OpportunityStage::RequirementsGathering, OpportunityStage::from('requirements_gathering'));
        $this->assertSame(OpportunityStage::SolutionDesign,        OpportunityStage::from('solution_design'));
        $this->assertSame(OpportunityStage::ProposalSent,          OpportunityStage::from('proposal_sent'));
        $this->assertSame(OpportunityStage::Negotiation,           OpportunityStage::from('negotiation'));
        $this->assertSame(OpportunityStage::Won,                   OpportunityStage::from('won'));
        $this->assertSame(OpportunityStage::Lost,                  OpportunityStage::from('lost'));
    }

    #[Test]
    public function each_stage_has_a_human_readable_label(): void
    {
        $this->assertSame('Qualification',          OpportunityStage::Qualification->label());
        $this->assertSame('Discovery Meeting',      OpportunityStage::DiscoveryMeeting->label());
        $this->assertSame('Requirements Gathering', OpportunityStage::RequirementsGathering->label());
        $this->assertSame('Solution Design',        OpportunityStage::SolutionDesign->label());
        $this->assertSame('Proposal Sent',          OpportunityStage::ProposalSent->label());
        $this->assertSame('Negotiation',            OpportunityStage::Negotiation->label());
        $this->assertSame('Won',                    OpportunityStage::Won->label());
        $this->assertSame('Lost',                   OpportunityStage::Lost->label());
    }

    #[Test]
    public function won_and_lost_are_terminal_stages(): void
    {
        $this->assertTrue(OpportunityStage::Won->isTerminal());
        $this->assertTrue(OpportunityStage::Lost->isTerminal());
    }

    #[Test]
    public function pipeline_stages_are_not_terminal(): void
    {
        $pipelineStages = [
            OpportunityStage::Qualification,
            OpportunityStage::DiscoveryMeeting,
            OpportunityStage::RequirementsGathering,
            OpportunityStage::SolutionDesign,
            OpportunityStage::ProposalSent,
            OpportunityStage::Negotiation,
        ];

        foreach ($pipelineStages as $stage) {
            $this->assertFalse($stage->isTerminal(), "{$stage->value} should not be terminal");
        }
    }

    #[Test]
    public function terminal_stages_cannot_transition_to_anything(): void
    {
        foreach (OpportunityStage::cases() as $next) {
            $this->assertFalse(OpportunityStage::Won->canTransitionTo($next));
            $this->assertFalse(OpportunityStage::Lost->canTransitionTo($next));
        }
    }

    #[Test]
    public function pipeline_follows_the_approved_sequence(): void
    {
        $this->assertTrue(OpportunityStage::Qualification->canTransitionTo(OpportunityStage::DiscoveryMeeting));
        $this->assertTrue(OpportunityStage::DiscoveryMeeting->canTransitionTo(OpportunityStage::RequirementsGathering));
        $this->assertTrue(OpportunityStage::RequirementsGathering->canTransitionTo(OpportunityStage::SolutionDesign));
        $this->assertTrue(OpportunityStage::SolutionDesign->canTransitionTo(OpportunityStage::ProposalSent));
        $this->assertTrue(OpportunityStage::ProposalSent->canTransitionTo(OpportunityStage::Negotiation));
        $this->assertTrue(OpportunityStage::Negotiation->canTransitionTo(OpportunityStage::Won));
        $this->assertTrue(OpportunityStage::Negotiation->canTransitionTo(OpportunityStage::Lost));
    }

    #[Test]
    public function any_pipeline_stage_can_transition_to_lost(): void
    {
        $pipelineStages = [
            OpportunityStage::Qualification,
            OpportunityStage::DiscoveryMeeting,
            OpportunityStage::RequirementsGathering,
            OpportunityStage::ProposalSent,
            OpportunityStage::Negotiation,
        ];

        foreach ($pipelineStages as $stage) {
            $this->assertTrue(
                $stage->canTransitionTo(OpportunityStage::Lost),
                "{$stage->value} should be able to go Lost",
            );
        }
    }

    #[Test]
    public function proposal_can_be_accepted_directly_without_negotiation(): void
    {
        $this->assertTrue(OpportunityStage::ProposalSent->canTransitionTo(OpportunityStage::Won));
    }

    #[Test]
    public function stages_cannot_skip_forward_in_the_pipeline(): void
    {
        $this->assertFalse(OpportunityStage::Qualification->canTransitionTo(OpportunityStage::ProposalSent));
        $this->assertFalse(OpportunityStage::Qualification->canTransitionTo(OpportunityStage::Won));
        $this->assertFalse(OpportunityStage::DiscoveryMeeting->canTransitionTo(OpportunityStage::Negotiation));
    }
}
