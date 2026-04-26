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
        Schema::create('supplier_quote_notes', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_quote_notes_supplier_id_032b8e67');
            $table->integer('registered_by');
            $table->integer('request_id');
            $table->integer('quotation_id');
            $table->integer('request_item_id');
            $table->text('description');
            $table->timestamps();
            $table->foreign(['supplier_id'], 'fk_supplier_quote_notes_supplier_id_c6ca0f26')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_quote_notes');
    }
};
