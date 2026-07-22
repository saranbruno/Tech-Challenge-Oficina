<?php

namespace App\Interfaces\Http\Controllers\Inventory;

use App\Application\Inventory\InventoryService;
use App\Interfaces\Http\Requests\Inventory\AdjustStockRequest;
use App\Interfaces\Http\Requests\Inventory\StoreInventoryItemRequest;
use App\Interfaces\Http\Requests\Inventory\UpdateInventoryItemRequest;
use App\Interfaces\Http\Resources\InventoryItemResource;
use App\Interfaces\Http\Resources\StockMovementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InventoryItemController
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return InventoryItemResource::collection($this->inventory->list($this->perPage($request)));
    }

    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $item = $this->inventory->create(
            $request->string('name')->toString(),
            $request->string('type')->toString(),
            $request->integer('unit_price'),
            $request->integer('quantity_available'),
            $request->user()->getAuthIdentifier(),
        );

        return (new InventoryItemResource($item))->response()->setStatusCode(201);
    }

    public function show(int $inventoryItem): InventoryItemResource
    {
        return new InventoryItemResource($this->inventory->find($inventoryItem));
    }

    public function update(UpdateInventoryItemRequest $request, int $inventoryItem): InventoryItemResource
    {
        return new InventoryItemResource($this->inventory->update(
            $inventoryItem,
            $request->string('name')->toString(),
            $request->string('type')->toString(),
            $request->integer('unit_price'),
        ));
    }

    public function adjustStock(AdjustStockRequest $request, int $inventoryItem): InventoryItemResource
    {
        return new InventoryItemResource($this->inventory->adjustStock(
            $inventoryItem,
            $request->integer('quantity_available'),
            $request->user()->getAuthIdentifier(),
        ));
    }

    public function movements(Request $request, int $inventoryItem): AnonymousResourceCollection
    {
        return StockMovementResource::collection($this->inventory->movements($inventoryItem, $this->perPage($request)));
    }

    public function destroy(int $inventoryItem): Response
    {
        $this->inventory->delete($inventoryItem);

        return response()->noContent();
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
