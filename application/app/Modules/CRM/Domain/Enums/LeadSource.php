<?php

namespace App\Modules\CRM\Domain\Enums;

enum LeadSource: string
{
    case Website     = 'website';
    case Referral    = 'referral';
    case Direct      = 'direct';
    case SocialMedia = 'social_media';
    case Other       = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Website     => 'Website',
            self::Referral    => 'Referral',
            self::Direct      => 'Direct',
            self::SocialMedia => 'Social Media',
            self::Other       => 'Other',
        };
    }
}
