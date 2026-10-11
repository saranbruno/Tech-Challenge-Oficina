<?php

namespace App\Application\Customer\Contracts;

use App\Application\Shared\Data\PaginatedResult;
use App\Domain\Customer\Customer;

interface CustomerRepository
{
    public function paginate(int $perPage): PaginatedResult;

    public function findOrFail(int $id): Customer;

    public function findByDocumentOrFail(string $document): Customer;

    public function documentExists(string $document, ?int $exceptId = null): bool;

    public function save(Customer $customer): Customer;

    public function delete(int $id): void;
}
