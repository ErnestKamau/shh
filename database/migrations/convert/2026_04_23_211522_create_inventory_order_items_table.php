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
            $table->uuid('inventory_order_id')->index('idx_inventory_order_items_inventory_order_id_8483d8fb');
            $table->uuid('inventory_category_id')->index('idx_inventory_order_items_inventory_category_id_dec8dc1d');
            $table->uuid('inventory_sub_category_id')->index('idx_inventory_order_items_inventory_sub_category_id_68951241');
            $table->double('quantity');
            $table->boolean('fulfilled')->default(false);
            $table->timestamps();
            $table->foreign(['inventory_category_id'], 'fk_inventory_order_items_inventory_category_id_e792e9b3')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_order_id'], 'fk_inventory_order_items_inventory_order_id_78b82c90')->references(['id'])->on('inventory_orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_sub_category_id'], 'fk_inventory_order_items_inventory_sub_category_id_9a6fd5d4')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');



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
