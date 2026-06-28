<?php

namespace App\Modules\Company\Application\DTOs;

use App\Modules\Company\Domain\Enums\ContactRole;

final readonly class CreateContactData
{
    public function __construct(
        public int         $companyId,
        public string      $firstName,
        public string      $lastName,
        public ContactRole $role,
        public ?string     $email     = null,
        public ?string     $phone     = null,
        public bool        $isPrimary = false,
        public ?int        $assignedTo = null,
    ) {}
}
