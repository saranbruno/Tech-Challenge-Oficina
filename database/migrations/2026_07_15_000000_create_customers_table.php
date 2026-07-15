<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('document', 14)->unique();
            $table->string('document_type', 4);
            $table->timestamps();

        });

        DB::statement("alter table customers add constraint customers_document_type_check check (document_type in ('cpf', 'cnpj'))");
        DB::statement('alter table customers add constraint customers_document_length_check check (char_length(document) in (11, 14))');
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
