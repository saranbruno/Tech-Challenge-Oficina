<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->string('status', 30);
            $table->timestamp('received_at');
            $table->timestamp('diagnosis_started_at')->nullable();
            $table->timestamp('awaiting_approval_at')->nullable();
            $table->timestamp('execution_started_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        DB::statement("alter table service_orders add constraint service_orders_status_check check (status in ('received', 'in_diagnosis', 'awaiting_approval', 'in_execution', 'finalized', 'delivered', 'cancelled'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
