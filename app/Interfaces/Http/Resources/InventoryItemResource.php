<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Inventory\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->item();

        return [
            'id' => $item->id,
            'name' => $item->name,
            'type' => $item->type->value,
            'unit_price' => $item->unitPrice->cents,
            'quantity_available' => $item->quantityAvailable->value,
        ];
    }

    private function item(): InventoryItem
    {
        return $this->resource;
    }
}
