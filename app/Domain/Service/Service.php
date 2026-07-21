<?php

namespace App\Domain\Service;

use App\Domain\Service\ValueObjects\UnitPrice;
use DomainException;

final readonly class Service
{
    public string $name;

    public function __construct(
        public ?int $id,
        string $name,
        public UnitPrice $unitPrice,
    ) {
        $this->name = trim($name);

        if ($this->name === '') {
            throw new DomainException('O nome do servico e obrigatorio.');
        }
    }
}
