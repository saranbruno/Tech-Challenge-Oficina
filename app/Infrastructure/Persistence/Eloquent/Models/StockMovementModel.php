<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['inventory_item_id', 'admin_user_id', 'type', 'quantity_change', 'quantity_before', 'quantity_after'])]
class StockMovementModel extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'stock_movements';
}
