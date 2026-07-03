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
        Schema::create('inventory_store_slot_contents', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_store_slot_id')->index('idx_inventory_store_slot_contents_inventory_store_slot_2c8ee973');
            $table->uuid('inventory_item_id')->index('idx_inventory_store_slot_contents_inventory_item_id_8a890ff7');
            $table->timestamps();
            $table->uuid('inventory_sub_category_id')->nullable()->index('idx_inventory_store_slot_contents_inventory_sub_catego_7fef88e0');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_store_slot_contents');
    }
};
