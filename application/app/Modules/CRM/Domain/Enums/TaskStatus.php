<?php

namespace App\Modules\CRM\Domain\Enums;

enum TaskStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public function canTransitionTo(self $next): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return match ($this) {
            self::Pending    => in_array($next, [self::InProgress, self::Completed, self::Cancelled], true),
            self::InProgress => in_array($next, [self::Completed, self::Cancelled], true),
            default          => false,
        };
    }
}
