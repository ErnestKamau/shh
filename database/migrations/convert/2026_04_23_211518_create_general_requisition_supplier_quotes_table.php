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
        Schema::create('general_requisition_supplier_quotes', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_general_requisition_supplier_quotes_supplier_id_8bd3806b');
            $table->integer('request_item_id');
            $table->integer('request_id');
            $table->decimal('amount', 8, 3);
            $table->boolean('is_approved')->nullable()->default(true);
            $table->timestamps();
            $table->foreign(['supplier_id'], 'fk_general_requisition_supplier_quotes_supplier_id_df67df4f')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_requisition_supplier_quotes');
    }
};
