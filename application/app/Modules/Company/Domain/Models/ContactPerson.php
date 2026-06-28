<?php

namespace App\Modules\Company\Domain\Models;

use App\Modules\Company\Domain\Enums\ContactRole;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPerson extends BaseModel
{
    protected $table = 'contact_persons';

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'role',
        'email',
        'phone',
        'is_primary',
        'assigned_to',
    ];

    protected $casts = [
        'role'       => ContactRole::class,
        'is_primary' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
