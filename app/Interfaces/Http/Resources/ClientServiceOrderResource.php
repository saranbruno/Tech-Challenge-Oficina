<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientServiceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'service_order_id' => $this->resource->id,
            'status' => $this->resource->status->value,
            'received_at' => $this->resource->receivedAt->format(DATE_ATOM),
            'approval_status' => match ($this->resource->status) {
                ServiceOrderStatus::AwaitingApproval => 'pending',
                ServiceOrderStatus::InExecution, ServiceOrderStatus::Finalized, ServiceOrderStatus::Delivered => 'approved',
                default => 'not_available',
            },
            'services' => array_map(fn ($service): array => [
                'quantity' => $service->quantity,
                'unit_price' => $service->unitPriceSnapshot->cents,
                'subtotal' => $service->subtotal(),
            ], $this->resource->services()),
            'inventory_items' => array_map(fn ($item): array => [
                'type' => $item->typeSnapshot->value,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPriceSnapshot->cents,
                'subtotal' => $item->subtotal(),
            ], $this->resource->inventoryItems()),
            'total_amount' => $this->resource->totalAmount(),
        ];
    }
}
