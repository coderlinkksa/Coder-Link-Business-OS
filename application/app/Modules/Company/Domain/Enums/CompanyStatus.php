<?php

namespace App\Modules\Company\Domain\Enums;

enum CompanyStatus: string
{
    case New      = 'new';
    case Active   = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New      => 'New',
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::New      => in_array($next, [self::Active, self::Archived], true),
            self::Active   => in_array($next, [self::Inactive, self::Archived], true),
            self::Inactive => in_array($next, [self::Active, self::Archived], true),
            self::Archived => false,
        };
    }
}
