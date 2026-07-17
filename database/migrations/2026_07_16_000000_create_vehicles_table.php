<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('license_plate', 7)->unique();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year');
            $table->timestamps();
        });

        DB::statement("alter table vehicles add constraint vehicles_license_plate_format_check check (license_plate ~ '^[A-Z]{3}([0-9]{4}|[0-9][A-Z][0-9]{2})$')");
        DB::statement('alter table vehicles add constraint vehicles_year_check check (year between 1886 and 9999)');
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
