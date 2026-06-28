<?php

namespace App\Modules\Company\Domain\Models;

use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'type',
        'status',
        'industry',
        'phone',
        'email',
        'website',
        'address',
        'city',
        'country',
        'assigned_to',
    ];

    protected $casts = [
        'type'   => CompanyType::class,
        'status' => CompanyStatus::class,
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(ContactPerson::class, 'company_id');
    }

    public function primaryContact(): ?ContactPerson
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    public function isActive(): bool
    {
        return $this->status === CompanyStatus::Active;
    }

    public function fullAddress(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->country,
        ]));
    }
}
