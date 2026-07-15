<?php

namespace App\Domain\Customer\ValueObjects;

use App\Domain\Customer\Enums\DocumentType;
use App\Domain\Customer\Exceptions\InvalidDocument;

final readonly class Document
{
    public string $value;

    public DocumentType $type;

    public function __construct(string $value)
    {
        $normalized = preg_replace('/\D/', '', $value) ?? '';

        $this->type = match (strlen($normalized)) {
            11 => DocumentType::Cpf,
            14 => DocumentType::Cnpj,
            default => throw new InvalidDocument('CPF ou CNPJ invalido.'),
        };

        if (! $this->isValid($normalized)) {
            throw new InvalidDocument('CPF ou CNPJ invalido.');
        }

        $this->value = $normalized;
    }

    private function isValid(string $value): bool
    {
        if (preg_match('/^(\d)\1+$/', $value) === 1) {
            return false;
        }

        return match ($this->type) {
            DocumentType::Cpf => $this->isValidCpf($value),
            DocumentType::Cnpj => $this->isValidCnpj($value),
        };
    }

    private function isValidCpf(string $value): bool
    {
        for ($digit = 9; $digit < 11; $digit++) {
            $sum = 0;

            for ($index = 0; $index < $digit; $index++) {
                $sum += (int) $value[$index] * (($digit + 1) - $index);
            }

            $expected = (($sum * 10) % 11) % 10;

            if ((int) $value[$digit] !== $expected) {
                return false;
            }
        }

        return true;
    }

    private function isValidCnpj(string $value): bool
    {
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($digit = 12; $digit < 14; $digit++) {
            $sum = 0;
            $offset = 13 - $digit;

            for ($index = 0; $index < $digit; $index++) {
                $sum += (int) $value[$index] * $weights[$index + $offset];
            }

            $remainder = $sum % 11;
            $expected = $remainder < 2 ? 0 : 11 - $remainder;

            if ((int) $value[$digit] !== $expected) {
                return false;
            }
        }

        return true;
    }
}
