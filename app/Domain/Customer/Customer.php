<?php

namespace App\Domain\Customer;

use App\Domain\Customer\ValueObjects\Document;
use App\Domain\Customer\ValueObjects\Email;
use App\Domain\Customer\ValueObjects\Phone;

final readonly class Customer
{
    public function __construct(
        public ?int $id,
        public string $name,
        public Document $document,
        public ?Email $email = null,
        public ?Phone $phone = null,
    ) {}
}
