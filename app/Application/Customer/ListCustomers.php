<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;

final readonly class ListCustomers
{
    public function __construct(private CustomerRepository $customers) {}

    public function execute(int $perPage): mixed
    {
        return $this->customers->paginate($perPage);
    }
}
