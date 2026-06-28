<?php

namespace App\Modules\Company\Domain\Contracts;

use App\Modules\Company\Domain\Models\Company;

interface CompanyRepository
{
    public function findById(int $id): ?Company;

    public function save(Company $company): void;
}
