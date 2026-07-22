<?php

namespace App\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'inventory_item_id' => $this->resource->inventory_item_id,
            'admin_user_id' => $this->resource->admin_user_id,
            'type' => $this->resource->type,
            'quantity_change' => $this->resource->quantity_change,
            'quantity_before' => $this->resource->quantity_before,
            'quantity_after' => $this->resource->quantity_after,
            'created_at' => $this->resource->created_at,
        ];
    }
}
