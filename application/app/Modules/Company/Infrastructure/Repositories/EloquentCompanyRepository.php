<?php

namespace App\Modules\Company\Infrastructure\Repositories;

use App\Modules\Company\Domain\Contracts\CompanyRepository;
use App\Modules\Company\Domain\Models\Company;

class EloquentCompanyRepository implements CompanyRepository
{
    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    public function save(Company $company): void
    {
        $company->save();
    }
}
