<?php

namespace App\Interfaces\Http\Controllers\Inventory;

use App\Application\Inventory\AdjustInventoryStock;
use App\Application\Inventory\CreateInventoryItem;
use App\Application\Inventory\DeleteInventoryItem;
use App\Application\Inventory\GetInventoryItem;
use App\Application\Inventory\ListInventoryItems;
use App\Application\Inventory\ListInventoryMovements;
use App\Application\Inventory\UpdateInventoryItem;
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
    public function index(Request $request, ListInventoryItems $listInventoryItems): AnonymousResourceCollection
    {
        return InventoryItemResource::collection($listInventoryItems->execute($this->perPage($request)));
    }

    public function store(StoreInventoryItemRequest $request, CreateInventoryItem $createInventoryItem): JsonResponse
    {
        $item = $createInventoryItem->execute(
            $request->string('name')->toString(),
            $request->string('type')->toString(),
            $request->integer('unit_price'),
            $request->integer('quantity_available'),
            $request->user()->getAuthIdentifier(),
        );

        return (new InventoryItemResource($item))->response()->setStatusCode(201);
    }

    public function show(int $inventoryItem, GetInventoryItem $getInventoryItem): InventoryItemResource
    {
        return new InventoryItemResource($getInventoryItem->execute($inventoryItem));
    }

    public function update(UpdateInventoryItemRequest $request, int $inventoryItem, UpdateInventoryItem $updateInventoryItem): InventoryItemResource
    {
        return new InventoryItemResource($updateInventoryItem->execute(
            $inventoryItem,
            $request->string('name')->toString(),
            $request->string('type')->toString(),
            $request->integer('unit_price'),
        ));
    }

    public function adjustStock(AdjustStockRequest $request, int $inventoryItem, AdjustInventoryStock $adjustInventoryStock): InventoryItemResource
    {
        return new InventoryItemResource($adjustInventoryStock->execute(
            $inventoryItem,
            $request->integer('quantity_available'),
            $request->user()->getAuthIdentifier(),
        ));
    }

    public function movements(Request $request, int $inventoryItem, ListInventoryMovements $listInventoryMovements): AnonymousResourceCollection
    {
        return StockMovementResource::collection($listInventoryMovements->execute($inventoryItem, $this->perPage($request)));
    }

    public function destroy(int $inventoryItem, DeleteInventoryItem $deleteInventoryItem): Response
    {
        $deleteInventoryItem->execute($inventoryItem);

        return response()->noContent();
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
