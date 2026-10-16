<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Domain\Customer\Customer;

final readonly class GetCustomer
{
    public function __construct(private CustomerRepository $customers) {}

    public function execute(int $id): Customer
    {
        return $this->customers->findOrFail($id);
    }
}
