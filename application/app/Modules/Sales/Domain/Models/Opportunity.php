<?php

namespace App\Modules\Sales\Domain\Models;

use App\Modules\Sales\Domain\Enums\OpportunityStage;
use App\Shared\Models\BaseModel;

class Opportunity extends BaseModel
{
    protected $table = 'opportunities';

    protected $fillable = [
        'company_id',
        'lead_id',
        'contact_person_id',
        'title',
        'stage',
        'value_minor_units',
        'probability',
        'expected_close_date',
        'loss_reason',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'stage'               => OpportunityStage::class,
        'expected_close_date' => 'date',
    ];

    public function isOpen(): bool
    {
        return ! $this->stage->isTerminal();
    }
}
