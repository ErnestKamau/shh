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
        Schema::create('general_requisition_request_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('item_id');
            $table->string('item_description');
            $table->string('product_no')->nullable();
            $table->integer('request_id');
            $table->string('purpose')->nullable();
            $table->decimal('qty', 8, 3);
            $table->decimal('last_unit_price', 8, 3)->nullable()->default(0);
            $table->decimal('ext_cost', 8, 3)->nullable();
            $table->string('supplier')->nullable();
            $table->uuid('supplier_id')->nullable()->index('idx_general_requisition_request_items_supplier_id_14ecfebf');
            $table->date('supplier_assignment_date')->nullable();
            $table->timestamps();
            $table->foreign(['supplier_id'], 'fk_general_requisition_request_items_supplier_id_ff6f4027')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_requisition_request_items');
    }
};
