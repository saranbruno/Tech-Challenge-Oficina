<?php

namespace App\Application\Customer;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Customer\Exceptions\DuplicateCustomerDocument;
use App\Domain\Customer\Customer;
use App\Domain\Customer\ValueObjects\Document;
use App\Domain\Customer\ValueObjects\Email;
use App\Domain\Customer\ValueObjects\Phone;

final readonly class CustomerDataFactory
{
    public function __construct(private CustomerRepository $customers) {}

    public function make(
        ?int $id,
        string $name,
        string $document,
        ?string $email = null,
        ?string $phone = null,
    ): Customer {
        $normalizedDocument = new Document($document);

        if ($this->customers->documentExists($normalizedDocument->value, $id)) {
            throw new DuplicateCustomerDocument('Ja existe um cliente com este CPF ou CNPJ.');
        }

        return new Customer(
            $id,
            trim($name),
            $normalizedDocument,
            $this->email($email),
            $this->phone($phone),
        );
    }

    private function email(?string $email): ?Email
    {
        return $email === null || trim($email) === '' ? null : new Email($email);
    }

    private function phone(?string $phone): ?Phone
    {
        return $phone === null || trim($phone) === '' ? null : new Phone($phone);
    }
}
