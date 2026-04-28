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
        Schema::create('inventory_orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('order_number');
            $table->uuid('supplier_id')->nullable()->index('idx_inventory_orders_supplier_id_c8d566a6');
            $table->uuid('created_by')->nullable()->index('idx_inventory_orders_created_by');
            $table->string('status')->default('not_fulfilled');
            $table->timestamps();
            $table->uuid('company_id')->default(1)->index('idx_inventory_orders_company_id_2915dc6d');
            $table->string('comments', 512)->nullable();


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_orders');
    }
};
