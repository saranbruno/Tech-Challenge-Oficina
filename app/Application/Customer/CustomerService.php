<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Customer\Exceptions\DuplicateCustomerDocument;
use App\Domain\Customer\Customer;
use App\Domain\Customer\ValueObjects\Document;

final readonly class CustomerService
{
    public function __construct(private CustomerRepository $customers) {}

    public function list(int $perPage): mixed
    {
        return $this->customers->paginate($perPage);
    }

    public function find(int $id): Customer
    {
        return $this->customers->findOrFail($id);
    }

    public function create(string $name, string $document): Customer
    {
        return $this->persist(null, $name, $document);
    }

    public function update(int $id, string $name, string $document): Customer
    {
        $this->customers->findOrFail($id);

        return $this->persist($id, $name, $document);
    }

    public function delete(int $id): void
    {
        $this->customers->delete($id);
    }

    private function persist(?int $id, string $name, string $document): Customer
    {
        $normalizedDocument = new Document($document);

        if ($this->customers->documentExists($normalizedDocument->value, $id)) {
            throw new DuplicateCustomerDocument('Ja existe um cliente com este CPF ou CNPJ.');
        }

        return $this->customers->save(new Customer($id, trim($name), $normalizedDocument));
    }
}
