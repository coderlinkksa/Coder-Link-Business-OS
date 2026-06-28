<?php

namespace App\Modules\Company\Domain\Enums;

enum CompanyType: string
{
    case Lead     = 'lead';
    case Customer = 'customer';
    case Partner  = 'partner';
    case Vendor   = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Lead     => 'Lead',
            self::Customer => 'Customer',
            self::Partner  => 'Partner',
            self::Vendor   => 'Vendor',
        };
    }

    public function isRevenueBearing(): bool
    {
        return in_array($this, [self::Customer, self::Partner], true);
    }
}
