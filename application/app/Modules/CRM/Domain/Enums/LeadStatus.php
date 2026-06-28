<?php

namespace App\Modules\CRM\Domain\Enums;

enum LeadStatus: string
{
    case New          = 'new';
    case Contacted    = 'contacted';
    case Qualified    = 'qualified';
    case Converted    = 'converted';
    case Disqualified = 'disqualified';

    public function label(): string
    {
        return match ($this) {
            self::New          => 'New',
            self::Contacted    => 'Contacted',
            self::Qualified    => 'Qualified',
            self::Converted    => 'Converted',
            self::Disqualified => 'Disqualified',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Converted, self::Disqualified], true);
    }

    public function canConvert(): bool
    {
        return $this === self::Qualified;
    }

    public function canDisqualify(): bool
    {
        return in_array($this, [self::New, self::Contacted, self::Qualified], true);
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::New          => in_array($next, [self::Contacted, self::Disqualified], true),
            self::Contacted    => in_array($next, [self::Qualified, self::Disqualified], true),
            self::Qualified    => in_array($next, [self::Converted, self::Disqualified], true),
            self::Converted    => false,
            self::Disqualified => false,
        };
    }
}
