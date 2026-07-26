<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_snapshot');
            $table->unique(['service_order_id', 'service_id']);
        });

        DB::statement('alter table service_order_services add constraint service_order_services_quantity_check check (quantity > 0)');
        DB::statement('alter table service_order_services add constraint service_order_services_unit_price_check check (unit_price_snapshot >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_services');
    }
};
