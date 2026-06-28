<?php

namespace App\Modules\CRM\Domain\Models;

use App\Modules\CRM\Domain\Enums\TaskPriority;
use App\Modules\CRM\Domain\Enums\TaskStatus;
use App\Modules\CRM\Domain\Exceptions\TaskAlreadyCompletedException;
use App\Shared\Models\BaseModel;

class Task extends BaseModel
{
    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'lead_id',
        'company_id',
        'contact_person_id',
        'opportunity_id',
        'assigned_to',
    ];

    protected $casts = [
        'status'       => TaskStatus::class,
        'priority'     => TaskPriority::class,
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function markCompleted(): void
    {
        if ($this->status->isTerminal()) {
            throw new TaskAlreadyCompletedException($this->id);
        }

        $this->status       = TaskStatus::Completed;
        $this->completed_at = now();
    }

    public function isOverdue(): bool
    {
        return ! $this->status->isTerminal()
            && $this->due_at !== null
            && $this->due_at->isPast();
    }
}
