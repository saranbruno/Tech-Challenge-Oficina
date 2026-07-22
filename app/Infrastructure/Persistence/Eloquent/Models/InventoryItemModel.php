<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'type', 'unit_price', 'quantity_available'])]
class InventoryItemModel extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';
}
