<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Domain\Customer\Customer;

final readonly class UpdateCustomer
{
    public function __construct(
        private CustomerRepository $customers,
        private CustomerDataFactory $factory,
    ) {}

    public function execute(int $id, string $name, string $document): Customer
    {
        $this->customers->findOrFail($id);

        return $this->customers->save($this->factory->make($id, $name, $document));
    }
}
