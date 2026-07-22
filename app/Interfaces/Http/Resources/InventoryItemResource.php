<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Inventory\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->resource;

        return [
            'id' => $item instanceof InventoryItem ? $item->id : $item->getKey(),
            'name' => $item->name,
            'type' => $item instanceof InventoryItem ? $item->type->value : $item->type,
            'unit_price' => $item instanceof InventoryItem ? $item->unitPrice->cents : $item->unit_price,
            'quantity_available' => $item instanceof InventoryItem ? $item->quantityAvailable->value : $item->quantity_available,
        ];
    }
}
