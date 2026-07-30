<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('alter table stock_movements drop constraint stock_movements_type_check');

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->foreignId('service_order_id')->nullable()->after('admin_user_id')->constrained()->restrictOnDelete();
            $table->unique(['service_order_id', 'inventory_item_id']);
            $table->foreignId('admin_user_id')->nullable()->change();
        });

        DB::statement("alter table stock_movements add constraint stock_movements_type_check check (type in ('initial_stock', 'manual_adjustment', 'service_order_consumption'))");
        DB::statement("alter table stock_movements add constraint stock_movements_source_check check ((type = 'service_order_consumption' and service_order_id is not null and admin_user_id is null) or (type in ('initial_stock', 'manual_adjustment') and service_order_id is null and admin_user_id is not null))");
    }

    public function down(): void
    {
        DB::statement('alter table stock_movements drop constraint stock_movements_source_check');
        DB::statement('alter table stock_movements drop constraint stock_movements_type_check');

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropUnique(['service_order_id', 'inventory_item_id']);
            $table->dropConstrainedForeignId('service_order_id');
            $table->foreignId('admin_user_id')->nullable(false)->change();
        });

        DB::statement("alter table stock_movements add constraint stock_movements_type_check check (type in ('initial_stock', 'manual_adjustment'))");
    }
};
