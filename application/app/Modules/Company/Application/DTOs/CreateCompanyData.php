<?php

namespace App\Modules\Company\Application\DTOs;

use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;

final readonly class CreateCompanyData
{
    public function __construct(
        public string        $name,
        public CompanyType   $type,
        public CompanyStatus $status   = CompanyStatus::New,
        public ?string       $industry = null,
        public ?string       $phone    = null,
        public ?string       $email    = null,
        public ?string       $website  = null,
        public ?string       $address  = null,
        public ?string       $city     = null,
        public ?string       $country  = null,
        public int|string|null $assignedTo = null,
    ) {}
}
