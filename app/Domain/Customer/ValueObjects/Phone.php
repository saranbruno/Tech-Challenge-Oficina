<?php

namespace App\Domain\Customer\ValueObjects;

use App\Domain\Customer\Exceptions\InvalidPhone;

final readonly class Phone
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = preg_replace('/[\s().-]+/', '', trim($value)) ?? '';

        if (preg_match('/^[0-9]{10,11}$/', $normalized) === 1) {
            $normalized = '+55'.$normalized;
        } elseif (preg_match('/^55[0-9]{10,11}$/', $normalized) === 1) {
            $normalized = '+'.$normalized;
        }

        if (preg_match('/^\+[1-9][0-9]{7,14}$/', $normalized) !== 1) {
            throw new InvalidPhone('O telefone informado deve possuir formato internacional valido.');
        }

        $this->value = $normalized;
    }
}
