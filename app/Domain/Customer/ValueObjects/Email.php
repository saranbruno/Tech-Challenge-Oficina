<?php

namespace App\Domain\Customer\ValueObjects;

use App\Domain\Customer\Exceptions\InvalidEmail;

final readonly class Email
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (strlen($normalized) > 254 || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmail('O e-mail informado e invalido.');
        }

        $this->value = $normalized;
    }
}
