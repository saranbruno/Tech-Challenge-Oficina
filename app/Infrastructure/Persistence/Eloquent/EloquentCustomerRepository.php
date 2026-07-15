<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Domain\Customer\Customer;
use App\Domain\Customer\ValueObjects\Document;
use App\Infrastructure\Persistence\Eloquent\Models\CustomerModel;

class EloquentCustomerRepository implements CustomerRepository
{
    public function paginate(int $perPage): mixed
    {
        return CustomerModel::query()->orderBy('id')->paginate($perPage);
    }

    public function findOrFail(int $id): Customer
    {
        return $this->toDomain(CustomerModel::query()->findOrFail($id));
    }

    public function documentExists(string $document, ?int $exceptId = null): bool
    {
        return CustomerModel::query()
            ->where('document', $document)
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function save(Customer $customer): Customer
    {
        $model = $customer->id === null
            ? new CustomerModel
            : CustomerModel::query()->findOrFail($customer->id);

        $model->fill([
            'name' => $customer->name,
            'document' => $customer->document->value,
            'document_type' => $customer->document->type->value,
        ])->save();

        return $this->toDomain($model);
    }

    public function delete(int $id): void
    {
        CustomerModel::query()->findOrFail($id)->delete();
    }

    private function toDomain(CustomerModel $model): Customer
    {
        return new Customer(
            $model->getKey(),
            $model->name,
            new Document($model->document),
        );
    }
}
