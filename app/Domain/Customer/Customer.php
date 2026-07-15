<?php

namespace App\Domain\Customer;

use App\Domain\Customer\ValueObjects\Document;

final readonly class Customer
{
    public function __construct(
        public ?int $id,
        public string $name,
        public Document $document,
    ) {}
}
