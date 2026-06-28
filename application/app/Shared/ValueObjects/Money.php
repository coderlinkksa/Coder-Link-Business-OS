<?php

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\ValidationException;

final class Money
{
    private int $amount; // stored in minor units (halalas for SAR)
    private string $currency;

    public function __construct(int $amount, string $currency = 'SAR')
    {
        if ($amount < 0) {
            throw new ValidationException('Money amount cannot be negative.');
        }

        $this->amount   = $amount;
        $this->currency = strtoupper($currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function formatted(): string
    {
        return number_format($this->amount / 100, 2) . ' ' . $this->currency;
    }
}
