<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderQuery;
use App\Application\Shared\Data\PaginatedResult;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;

final readonly class EloquentServiceOrderQuery implements ServiceOrderQuery
{
    public function __construct(private ServiceOrderMapper $mapper) {}

    public function paginate(int $perPage): PaginatedResult
    {
        return PaginatedResultFactory::make(
            ServiceOrderModel::query()->orderByDesc('id')->paginate($perPage),
            fn (ServiceOrderModel $model): ServiceOrder => $this->mapper->toDomain($model),
        );
    }

    public function findForClientOrFail(string $customerDocument, string $trackingTokenHash): ServiceOrder
    {
        $model = ServiceOrderModel::query()
            ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
            ->where('customers.document', $customerDocument)
            ->where('service_orders.tracking_token_hash', $trackingTokenHash)
            ->select('service_orders.*')
            ->first();

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $this->mapper->toDomain($model);
    }
}
