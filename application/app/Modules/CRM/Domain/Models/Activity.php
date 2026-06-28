<?php

namespace App\Modules\CRM\Domain\Models;

use App\Modules\CRM\Domain\Enums\ActivityType;
use App\Shared\Models\BaseModel;

/**
 * Activities are append-only. They record what happened and are never updated.
 * See CRM_DOMAIN_MODEL.md §12 and DATABASE_ARCHITECTURE.md §7.
 */
class Activity extends BaseModel
{
    protected $table = 'activities';

    protected $fillable = [
        'type',
        'subject',
        'body',
        'occurred_at',
        'lead_id',
        'company_id',
        'contact_person_id',
        'opportunity_id',
    ];

    protected $casts = [
        'type'        => ActivityType::class,
        'occurred_at' => 'datetime',
    ];

    public function hasLinkedRecord(): bool
    {
        return $this->lead_id !== null
            || $this->company_id !== null
            || $this->contact_person_id !== null
            || $this->opportunity_id !== null;
    }
}
