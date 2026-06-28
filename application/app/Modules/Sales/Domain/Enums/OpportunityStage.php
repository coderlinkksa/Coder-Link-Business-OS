<?php

namespace App\Modules\Sales\Domain\Enums;

enum OpportunityStage: string
{
    case Qualification        = 'qualification';
    case DiscoveryMeeting     = 'discovery_meeting';
    case RequirementsGathering = 'requirements_gathering';
    case SolutionDesign       = 'solution_design';
    case ProposalSent         = 'proposal_sent';
    case Negotiation          = 'negotiation';
    case Won                  = 'won';
    case Lost                 = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Qualification        => 'Qualification',
            self::DiscoveryMeeting     => 'Discovery Meeting',
            self::RequirementsGathering => 'Requirements Gathering',
            self::SolutionDesign       => 'Solution Design',
            self::ProposalSent         => 'Proposal Sent',
            self::Negotiation          => 'Negotiation',
            self::Won                  => 'Won',
            self::Lost                 => 'Lost',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Won, self::Lost], true);
    }

    public function isWon(): bool
    {
        return $this === self::Won;
    }

    public function isLost(): bool
    {
        return $this === self::Lost;
    }

    public function canTransitionTo(self $next): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return match ($this) {
            self::Qualification        => in_array($next, [self::DiscoveryMeeting, self::Lost], true),
            self::DiscoveryMeeting     => in_array($next, [self::RequirementsGathering, self::Lost], true),
            self::RequirementsGathering => in_array($next, [self::SolutionDesign, self::Lost], true),
            self::SolutionDesign       => in_array($next, [self::ProposalSent, self::Lost], true),
            self::ProposalSent         => in_array($next, [self::Negotiation, self::Won, self::Lost], true),
            self::Negotiation          => in_array($next, [self::Won, self::Lost], true),
            default                    => false,
        };
    }
}
