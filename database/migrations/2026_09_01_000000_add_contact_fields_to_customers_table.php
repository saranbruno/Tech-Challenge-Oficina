<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->string('email')->nullable();
            $table->string('phone', 16)->nullable();
        });

        DB::statement("alter table customers add constraint customers_phone_format_check check (phone is null or phone ~ '^\\+[1-9][0-9]{7,14}$')");
    }

    public function down(): void
    {
        DB::statement('alter table customers drop constraint if exists customers_phone_format_check');

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn(['email', 'phone']);
        });
    }
};
