<?php

namespace App\Interfaces\Http\Controllers\ServiceOrder;

use App\Application\ServiceOrder\ApproveClientServiceOrderBudget;
use App\Application\ServiceOrder\TrackServiceOrder;
use App\Interfaces\Http\Requests\ServiceOrder\TrackServiceOrderRequest;
use App\Interfaces\Http\Resources\ClientServiceOrderResource;

class ClientServiceOrderController
{
    public function __construct(
        private readonly ApproveClientServiceOrderBudget $approveClientServiceOrderBudget,
        private readonly TrackServiceOrder $trackServiceOrder,
    ) {}

    public function show(TrackServiceOrderRequest $request): ClientServiceOrderResource
    {
        return new ClientServiceOrderResource($this->trackServiceOrder->execute(
            $request->string('customer_document')->toString(),
            $request->string('tracking_token')->toString(),
        ));
    }

    public function approve(TrackServiceOrderRequest $request): ClientServiceOrderResource
    {
        return new ClientServiceOrderResource($this->approveClientServiceOrderBudget->execute(
            $request->string('customer_document')->toString(),
            $request->string('tracking_token')->toString(),
            new \DateTimeImmutable,
        ));
    }
}
