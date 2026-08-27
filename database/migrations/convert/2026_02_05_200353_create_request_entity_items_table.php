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
        if (Schema::hasTable('request_entity_items')) {
            return;
        }
        Schema::create('request_entity_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('request_id');
            $table->integer('store_id');
            $table->integer('slot_id');
            $table->uuid('inventory_sub_category_id')->index('idx_request_entity_items_inventory_sub_category_id_237f5dcc');
            $table->decimal('quantity', 10, 0);
            $table->decimal('net_value', 10, 0);
            $table->integer('currency')->nullable();
            $table->timestamps();
            $table->string('action', 100)->default('normal');
            $table->string('status', 100)->default('pending');
            $table->date('gr_expiry')->default('2099-12-31');
            $table->uuid('inventory_item_id')->nullable()->index('idx_request_entity_items_inventory_item_id_6091be78');
            $table->integer('issued_by')->nullable();
            $table->string('lot_no', 100)->nullable();
            $table->text('comments')->nullable();
            $table->integer('ammendment')->default(1);
            $table->integer('request_entity_item_ammended_id')->default(0);
            $table->date('date_of_manufacture')->nullable();
            $table->uuid('item_brand_id')->nullable()->index('idx_request_entity_items_item_brand_id_2c58da8d');
            $table->uuid('starting_sample')->nullable();
            $table->string('catalog_number', 128)->nullable()->index('idx_request_entity_items_catalog_number_eb68ee4f');
            $table->string('uom', 100)->nullable();
            $table->string('test', 32)->nullable();
            $table->text('remarks')->nullable();
            $table->string('shipping_mode', 100)->nullable();
            $table->double('unit_cost');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_entity_items');
    }
};
