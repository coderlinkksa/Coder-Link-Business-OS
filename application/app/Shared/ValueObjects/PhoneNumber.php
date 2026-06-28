<?php

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\ValidationException;

final class PhoneNumber
{
    private string $value;

    public function __construct(string $value)
    {
        $cleaned = preg_replace('/\s+/', '', $value);

        if (empty($cleaned)) {
            throw new ValidationException('Phone number cannot be empty.');
        }

        $this->value = $cleaned;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
