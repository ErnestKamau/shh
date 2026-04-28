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
        Schema::create('lab_category_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('category_id');
            $table->integer('reagent_id');
            $table->integer('unit_measure_id');
            $table->string('amount_used')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->uuid('inventory_sub_category_id')->nullable()->index('idx_lab_category_items_inventory_sub_category_id_1876f5e7');
            $table->uuid('inventory_category_id')->nullable()->index('idx_lab_category_items_inventory_category_id_9a382a08');
            $table->boolean('active')->default(true);
            $table->uuid('inventory_item_id')->nullable()->index('idx_lab_category_items_inventory_item_id_eb6ef5f5');
            $table->uuid('item_brand_id')->nullable()->index('idx_lab_category_items_item_brand_id_29209aa4');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_category_items');
    }
};
