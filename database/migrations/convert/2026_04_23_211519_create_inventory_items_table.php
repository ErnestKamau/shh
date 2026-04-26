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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_category_id')->index('idx_inventory_items_inventory_category_id_e6085d0c');
            $table->uuid('inventory_sub_category_id')->index('idx_inventory_items_inventory_sub_category_id_600814eb');
            $table->double('stock_in')->default(0);
            $table->double('stock_out')->default(0);
            $table->integer('created_by');
            $table->uuid('supplier_id')->nullable()->index('idx_inventory_items_supplier_id_0d979b3b');
            $table->uuid('inventory_department_id')->default(0)->index('idx_inventory_items_inventory_department_id_a761aa1c');
            $table->integer('edited_by')->default(0);
            $table->string('status')->default('in_inventory');
            $table->timestamps();
            $table->integer('test_score')->nullable();
            $table->uuid('inventory_location_id')->nullable()->default(0)->index('idx_inventory_items_inventory_location_id_5cd79f42');
            $table->string('batch_code')->nullable();
            $table->date('expiry')->nullable()->default('2099-12-31');
            $table->double('price')->nullable();
            $table->integer('received_by')->nullable();
            $table->string('previous_batch_code', 100)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->uuid('inventory_store_id')->nullable()->index('idx_inventory_items_inventory_store_id_fc79e0e4');
            $table->uuid('inventory_store_slot_id')->nullable()->index('idx_inventory_items_inventory_store_slot_id_689593d3');
            $table->string('storage_state_id', 100)->nullable();
            $table->uuid('item_brand_id')->default(0)->index('idx_inventory_items_item_brand_id_fb938958');
            $table->string('lot_no', 100)->nullable();
            $table->date('date_of_manufacture')->nullable();
            $table->foreign(['inventory_category_id'], 'fk_inventory_items_inventory_category_id_a8d08c22')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_department_id'], 'fk_inventory_items_inventory_department_id_ebcd77ec')->references(['id'])->on('inventory_departments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_location_id'], 'fk_inventory_items_inventory_location_id_0b81cb5b')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_store_id'], 'fk_inventory_items_inventory_store_id_a90b6395')->references(['id'])->on('inventory_stores')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_store_slot_id'], 'fk_inventory_items_inventory_store_slot_id_a009a457')->references(['id'])->on('inventory_store_slots')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_sub_category_id'], 'fk_inventory_items_inventory_sub_category_id_08d9e9b3')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['item_brand_id'], 'fk_inventory_items_item_brand_id_2f67ff27')->references(['id'])->on('item_brands')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_inventory_items_supplier_id_9e0aa447')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');








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
