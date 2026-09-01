<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Customer\Exceptions\DuplicateCustomerDocument;
use App\Domain\Customer\Customer;
use App\Domain\Customer\ValueObjects\Document;

final readonly class CustomerDataFactory
{
    public function __construct(private CustomerRepository $customers) {}

    public function make(?int $id, string $name, string $document): Customer
    {
        $normalizedDocument = new Document($document);

        if ($this->customers->documentExists($normalizedDocument->value, $id)) {
            throw new DuplicateCustomerDocument('Ja existe um cliente com este CPF ou CNPJ.');
        }

        return new Customer($id, trim($name), $normalizedDocument);
    }
}
