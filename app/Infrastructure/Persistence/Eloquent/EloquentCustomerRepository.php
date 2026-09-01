<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Shared\Data\PaginatedResult;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\Customer\Customer;
use App\Domain\Customer\ValueObjects\Document;
use App\Infrastructure\Persistence\Eloquent\Models\CustomerModel;

class EloquentCustomerRepository implements CustomerRepository
{
    public function paginate(int $perPage): PaginatedResult
    {
        return PaginatedResultFactory::make(
            CustomerModel::query()->orderBy('id')->paginate($perPage),
            fn (CustomerModel $model): Customer => $this->toDomain($model),
        );
    }

    public function findOrFail(int $id): Customer
    {
        return $this->toDomain($this->findModel($id));
    }

    public function findByDocumentOrFail(string $document): Customer
    {
        $model = CustomerModel::query()->where('document', $document)->first();

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $this->toDomain($model);
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
            : $this->findModel($customer->id);

        $model->fill([
            'name' => $customer->name,
            'document' => $customer->document->value,
            'document_type' => $customer->document->type->value,
        ])->save();

        return $this->toDomain($model);
    }

    public function delete(int $id): void
    {
        $this->findModel($id)->delete();
    }

    private function findModel(int $id): CustomerModel
    {
        $model = CustomerModel::query()->find($id);

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $model;
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
