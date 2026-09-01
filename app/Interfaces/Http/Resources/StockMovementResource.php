<?php

namespace App\Interfaces\Http\Resources;

use App\Application\Inventory\Data\StockMovementData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $movement = $this->movement();

        return [
            'id' => $movement->id,
            'inventory_item_id' => $movement->inventoryItemId,
            'admin_user_id' => $movement->adminUserId,
            'service_order_id' => $movement->serviceOrderId,
            'type' => $movement->type,
            'quantity_change' => $movement->quantityChange,
            'quantity_before' => $movement->quantityBefore,
            'quantity_after' => $movement->quantityAfter,
            'created_at' => $movement->createdAt,
        ];
    }

    private function movement(): StockMovementData
    {
        return $this->resource;
    }
}
