<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'customer_id',
    'vehicle_id',
    'status',
    'total_amount',
    'received_at',
    'diagnosis_started_at',
    'awaiting_approval_at',
    'execution_started_at',
    'finalized_at',
    'delivered_at',
    'cancelled_at',
])]
class ServiceOrderModel extends Model
{
    protected $table = 'service_orders';

    protected function casts(): array
    {
        return [
            'received_at' => 'immutable_datetime',
            'diagnosis_started_at' => 'immutable_datetime',
            'awaiting_approval_at' => 'immutable_datetime',
            'execution_started_at' => 'immutable_datetime',
            'finalized_at' => 'immutable_datetime',
            'delivered_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }
}
