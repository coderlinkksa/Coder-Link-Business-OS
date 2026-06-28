<?php

namespace App\Modules\CRM\Domain\Enums;

enum ActivityType: string
{
    case Call    = 'call';
    case Meeting = 'meeting';
    case Email   = 'email';
    case Note    = 'note';
    case Other   = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Call    => 'Call',
            self::Meeting => 'Meeting',
            self::Email   => 'Email',
            self::Note    => 'Note',
            self::Other   => 'Other',
        };
    }
}
