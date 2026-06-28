<?php

namespace App\Modules\Company\Domain\Enums;

enum ContactRole: string
{
    case Primary        = 'primary';
    case DecisionMaker  = 'decision_maker';
    case Technical      = 'technical';
    case Billing        = 'billing';
    case Other          = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Primary       => 'Primary Contact',
            self::DecisionMaker => 'Decision Maker',
            self::Technical     => 'Technical Contact',
            self::Billing       => 'Billing Contact',
            self::Other         => 'Other',
        };
    }
}
