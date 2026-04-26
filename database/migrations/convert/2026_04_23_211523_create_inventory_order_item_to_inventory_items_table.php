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
            $table->uuid('inventory_order_id')->index('idx_inventory_order_item_to_inventory_items_inventory_04c8a773');
            $table->uuid('inventory_item_id')->index('idx_inventory_order_item_to_inventory_items_inventory_84f35dbb');
            $table->uuid('inventory_order_item_id')->index('idx_inventory_order_item_to_inventory_items_inventory_8976cb67');
            $table->timestamps();
            $table->foreign(['inventory_item_id'], 'fk_inventory_order_item_to_inventory_items_inventory_i_cbf53323')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_order_id'], 'fk_inventory_order_item_to_inventory_items_inventory_o_a57f4645')->references(['id'])->on('inventory_orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_order_item_id'], 'fk_inventory_order_item_to_inventory_items_inventory_o_6d755b0d')->references(['id'])->on('inventory_order_items')->onUpdate('no action')->onDelete('cascade');



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
