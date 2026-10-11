<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderQuery;
use App\Application\Shared\Data\PaginatedResult;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;

final readonly class EloquentServiceOrderQuery implements ServiceOrderQuery
{
    private const OPERATIONAL_STATUSES = [
        'in_execution',
        'awaiting_approval',
        'in_diagnosis',
        'received',
    ];

    public function __construct(private ServiceOrderMapper $mapper) {}

    public function paginate(int $perPage): PaginatedResult
    {
        return PaginatedResultFactory::make(
            ServiceOrderModel::query()
                ->whereIn('status', self::OPERATIONAL_STATUSES)
                ->orderByRaw("case status when 'in_execution' then 1 when 'awaiting_approval' then 2 when 'in_diagnosis' then 3 when 'received' then 4 end")
                ->orderBy('received_at')
                ->orderBy('id')
                ->paginate($perPage),
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
