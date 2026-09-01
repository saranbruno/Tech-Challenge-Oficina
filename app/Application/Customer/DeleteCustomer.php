<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;

final readonly class DeleteCustomer
{
    public function __construct(private CustomerRepository $customers) {}

    public function execute(int $id): void
    {
        $this->customers->delete($id);
    }
}
