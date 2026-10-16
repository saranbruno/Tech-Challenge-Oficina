<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Domain\Customer\Customer;

final readonly class CreateCustomer
{
    public function __construct(
        private CustomerRepository $customers,
        private CustomerDataFactory $factory,
    ) {}

    public function execute(
        string $name,
        string $document,
        ?string $email = null,
        ?string $phone = null,
    ): Customer {
        return $this->customers->save($this->factory->make(null, $name, $document, $email, $phone));
    }
}
