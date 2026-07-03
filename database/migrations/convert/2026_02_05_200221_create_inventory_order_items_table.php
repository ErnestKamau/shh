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
        Schema::create('inventory_order_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_order_id')->index('idx_inventory_order_items_inventory_order_id_30eeeb5f');
            $table->uuid('inventory_category_id')->index('idx_inventory_order_items_inventory_category_id_a50121e9');
            $table->uuid('inventory_sub_category_id')->index('idx_inventory_order_items_inventory_sub_category_id_474de5de');
            $table->double('quantity');
            $table->boolean('fulfilled')->default(false);
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_order_items');
    }
};
