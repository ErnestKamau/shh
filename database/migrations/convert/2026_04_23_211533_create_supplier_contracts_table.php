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
            $table->uuid('supplier_id')->index('idx_supplier_contracts_supplier_id_dd81864f');
            $table->date('start');
            $table->date('end');
            $table->string('file');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign(['supplier_id'], 'fk_supplier_contracts_supplier_id_b7cee5b3')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');

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
