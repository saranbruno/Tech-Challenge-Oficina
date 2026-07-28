<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['service_order_id', 'inventory_item_id', 'type_snapshot', 'quantity', 'unit_price_snapshot'])]
class ServiceOrderInventoryItemModel extends Model
{
    public $timestamps = false;

    protected $table = 'service_order_inventory_items';
}
