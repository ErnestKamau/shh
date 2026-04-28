<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('description')->default('No Description');
            $table->uuid('supplier_id')->index('idx_supplier_contracts_supplier_id_4b93fab3');
            $table->date('start');
            $table->date('end');
            $table->string('file');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_contracts');
    }
};
