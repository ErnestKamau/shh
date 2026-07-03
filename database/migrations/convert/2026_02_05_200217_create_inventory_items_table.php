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
        if (Schema::hasTable('inventory_items')) {
            return;
        }
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_category_id')->index('idx_inventory_items_inventory_category_id_3bf426b6');
            $table->uuid('inventory_sub_category_id')->index('idx_inventory_items_inventory_sub_category_id_edf131a7');
            $table->double('stock_in')->default(0);
            $table->double('stock_out')->default(0);
            $table->uuid('created_by')->nullable()->index('idx_inventory_items_created_by_32347ce2');
            $table->uuid('supplier_id')->nullable()->index('idx_inventory_items_supplier_id_5668e2c1');
            $table->uuid('inventory_department_id')->nullable()->index('idx_inventory_items_inventory_department_id_c41f2723');
            $table->integer('edited_by')->default(0);
            $table->string('status')->default('in_inventory');
            $table->timestamps();
            $table->integer('test_score')->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_inventory_items_inventory_location_id_020de1bb');
            $table->string('batch_code')->nullable();
            $table->date('expiry')->nullable()->default('2099-12-31');
            $table->double('price')->nullable();
            $table->integer('received_by')->nullable();
            $table->string('previous_batch_code', 100)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->uuid('inventory_store_id')->nullable()->index('idx_inventory_items_inventory_store_id_ef828511');
            $table->uuid('inventory_store_slot_id')->nullable()->index('idx_inventory_items_inventory_store_slot_id_e7de1a2c');
            $table->string('storage_state_id', 100)->nullable();
            $table->uuid('item_brand_id')->nullable()->index('idx_inventory_items_item_brand_id_039b19f5');
            $table->string('lot_no', 100)->nullable();
            $table->date('date_of_manufacture')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
