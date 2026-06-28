<?php

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\ValidationException;

final class EmailAddress
{
    private string $value;

    public function __construct(string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email address: {$value}");
        }

        $this->value = strtolower(trim($value));
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
