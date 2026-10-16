<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Shared\Data\PaginatedResult;

final readonly class ListCustomers
{
    public function __construct(private CustomerRepository $customers) {}

    public function execute(int $perPage): PaginatedResult
    {
        return $this->customers->paginate($perPage);
    }
}
