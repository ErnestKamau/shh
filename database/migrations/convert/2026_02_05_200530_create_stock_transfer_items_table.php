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
        if (Schema::hasTable('stock_transfer_items')) {
            return;
        }
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('stock_transfer_id')->index('idx_stock_transfer_items_stock_transfer_id_2963e253');
            $table->bigInteger('local_item_id');
            $table->bigInteger('local_store_id');
            $table->bigInteger('local_store_slot_id');
            $table->bigInteger('target_item_id');
            $table->bigInteger('target_store_id');
            $table->bigInteger('target_store_slot_id');
            $table->double('target_quantity');
            $table->integer('local_inventory_item_id')->nullable();
            $table->integer('target_inventory_item_id')->nullable();
            $table->date('expiry')->nullable();
            $table->integer('local_state_id')->nullable();
            $table->integer('target_state_id')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
