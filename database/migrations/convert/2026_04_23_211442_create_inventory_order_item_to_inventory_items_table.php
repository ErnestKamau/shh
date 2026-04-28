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
        Schema::create('inventory_order_item_to_inventory_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_order_id')->index('idx_inventory_order_item_to_inventory_items_inventory_60ff44cc');
            $table->uuid('inventory_item_id')->index('idx_inventory_order_item_to_inventory_items_inventory_4111052b');
            $table->uuid('inventory_order_item_id')->index('idx_inventory_order_item_to_inventory_items_inventory_268e6b08');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_order_item_to_inventory_items');
    }
};
