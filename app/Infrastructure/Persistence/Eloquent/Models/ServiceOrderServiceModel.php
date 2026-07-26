<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['service_order_id', 'service_id', 'quantity', 'unit_price_snapshot'])]
class ServiceOrderServiceModel extends Model
{
    public $timestamps = false;

    protected $table = 'service_order_services';
}
