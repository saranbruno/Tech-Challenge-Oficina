<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type', 20);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity_available');
            $table->timestamps();
        });

        DB::statement("alter table inventory_items add constraint inventory_items_type_check check (type in ('part', 'supply'))");
        DB::statement('alter table inventory_items add constraint inventory_items_unit_price_check check (unit_price >= 0)');
        DB::statement('alter table inventory_items add constraint inventory_items_quantity_check check (quantity_available >= 0)');

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('admin_user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 30);
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("alter table stock_movements add constraint stock_movements_type_check check (type in ('initial_stock', 'manual_adjustment'))");
        DB::statement('alter table stock_movements add constraint stock_movements_balance_check check (quantity_after = quantity_before + quantity_change and quantity_after >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
    }
};
