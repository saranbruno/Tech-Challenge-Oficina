<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Application\Inventory\Exceptions\InventoryItemHasMovements;
use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Inventory\InventoryItem;
use App\Domain\Inventory\ValueObjects\StockQuantity;
use App\Domain\Service\ValueObjects\UnitPrice;
use App\Infrastructure\Persistence\Eloquent\Models\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Illuminate\Support\Facades\DB;

class EloquentInventoryItemRepository implements InventoryItemRepository
{
    public function paginate(int $perPage): mixed
    {
        return InventoryItemModel::query()->orderBy('id')->paginate($perPage);
    }

    public function findOrFail(int $id): InventoryItem
    {
        return $this->toDomain(InventoryItemModel::query()->findOrFail($id));
    }

    public function create(InventoryItem $item, int $adminId): InventoryItem
    {
        return DB::transaction(function () use ($item, $adminId): InventoryItem {
            $model = new InventoryItemModel;
            $this->fill($model, $item)->save();

            if ($item->quantityAvailable->value > 0) {
                $this->recordMovement($model->getKey(), $adminId, 'initial_stock', 0, $item->quantityAvailable->value);
            }

            return $this->toDomain($model);
        });
    }

    public function update(InventoryItem $item): InventoryItem
    {
        $model = InventoryItemModel::query()->findOrFail($item->id);
        $this->fill($model, $item)->save();

        return $this->toDomain($model);
    }

    public function adjustStock(int $id, int $quantity, int $adminId): InventoryItem
    {
        return DB::transaction(function () use ($id, $quantity, $adminId): InventoryItem {
            $model = InventoryItemModel::query()->lockForUpdate()->findOrFail($id);
            $before = $model->quantity_available;
            $model->quantity_available = (new StockQuantity($quantity))->value;
            $model->save();
            $this->recordMovement($id, $adminId, 'manual_adjustment', $before, $quantity);

            return $this->toDomain($model);
        });
    }

    public function movements(int $id, int $perPage): mixed
    {
        InventoryItemModel::query()->findOrFail($id);

        return StockMovementModel::query()
            ->where('inventory_item_id', $id)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function delete(int $id): void
    {
        $item = InventoryItemModel::query()->findOrFail($id);

        if (StockMovementModel::query()->where('inventory_item_id', $id)->exists()) {
            throw new InventoryItemHasMovements('O item possui historico de estoque e nao pode ser excluido.');
        }

        $item->delete();
    }

    private function fill(InventoryItemModel $model, InventoryItem $item): InventoryItemModel
    {
        return $model->fill([
            'name' => $item->name,
            'type' => $item->type->value,
            'unit_price' => $item->unitPrice->cents,
            'quantity_available' => $item->quantityAvailable->value,
        ]);
    }

    private function recordMovement(int $itemId, int $adminId, string $type, int $before, int $after): void
    {
        StockMovementModel::query()->create([
            'inventory_item_id' => $itemId,
            'admin_user_id' => $adminId,
            'type' => $type,
            'quantity_change' => $after - $before,
            'quantity_before' => $before,
            'quantity_after' => $after,
        ]);
    }

    private function toDomain(InventoryItemModel $model): InventoryItem
    {
        return new InventoryItem(
            $model->getKey(),
            $model->name,
            InventoryItemType::from($model->type),
            new UnitPrice($model->unit_price),
            new StockQuantity($model->quantity_available),
        );
    }
}
