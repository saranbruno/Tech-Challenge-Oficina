<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'customer_id' => $this->resource->customerId,
            'vehicle_id' => $this->resource->vehicleId,
            'status' => $this->resource->status->value,
            'received_at' => $this->resource->receivedAt->format(DATE_ATOM),
            'services' => array_map(fn ($service): array => [
                'service_id' => $service->serviceId,
                'quantity' => $service->quantity,
                'unit_price' => $service->unitPriceSnapshot->cents,
                'subtotal' => $service->subtotal(),
            ], $this->resource->services()),
            'inventory_items' => array_map(fn ($inventoryItem): array => [
                'inventory_item_id' => $inventoryItem->inventoryItemId,
                'type' => $inventoryItem->typeSnapshot->value,
                'quantity' => $inventoryItem->quantity,
                'unit_price' => $inventoryItem->unitPriceSnapshot->cents,
                'subtotal' => $inventoryItem->subtotal(),
            ], $this->resource->inventoryItems()),
            'total_amount' => $this->resource->totalAmount(),
        ];
    }
}
