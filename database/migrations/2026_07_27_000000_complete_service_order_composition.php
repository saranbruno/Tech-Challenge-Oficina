<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('total_amount')->default(0);
        });

        Schema::create('service_order_inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->string('type_snapshot', 20);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_snapshot');
            $table->unique(['service_order_id', 'inventory_item_id']);
        });

        DB::statement("alter table service_order_inventory_items add constraint service_order_inventory_items_type_check check (type_snapshot in ('part', 'supply'))");
        DB::statement('alter table service_order_inventory_items add constraint service_order_inventory_items_quantity_check check (quantity > 0)');
        DB::statement('alter table service_order_inventory_items add constraint service_order_inventory_items_unit_price_check check (unit_price_snapshot >= 0)');
        DB::statement('alter table service_orders add constraint service_orders_total_amount_check check (total_amount >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_inventory_items');

        Schema::table('service_orders', function (Blueprint $table): void {
            $table->dropColumn('total_amount');
        });
    }
};
