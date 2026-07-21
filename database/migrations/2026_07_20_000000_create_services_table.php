<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('unit_price');
            $table->timestamps();
        });

        DB::statement('alter table services add constraint services_unit_price_check check (unit_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
