<?php

namespace App\Modules\CRM\Domain\Models;

use App\Modules\CRM\Domain\Enums\LeadSource;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Exceptions\LeadAlreadyConvertedException;
use App\Shared\Models\BaseModel;

class Lead extends BaseModel
{
    protected $table = 'leads';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'service_requested',
        'source',
        'status',
        'company_id',
        'contact_person_id',
        'assigned_to',
        'notes',
        'lost_reason',
        'converted_at',
    ];

    protected $casts = [
        'source'       => LeadSource::class,
        'status'       => LeadStatus::class,
        'converted_at' => 'datetime',
    ];

    public function markConverted(): void
    {
        if ($this->status === LeadStatus::Converted) {
            throw new LeadAlreadyConvertedException($this->id);
        }

        $this->status       = LeadStatus::Converted;
        $this->converted_at = now();
    }

    public function hasContactInfo(): bool
    {
        return ($this->email !== null && $this->email !== '')
            || ($this->phone !== null && $this->phone !== '');
    }
}
