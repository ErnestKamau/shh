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
            $table->uuid('inventory_sub_category_id')->nullable()->index('idx_lab_category_items_inventory_sub_category_id_22f2af73');
            $table->uuid('inventory_category_id')->nullable()->index('idx_lab_category_items_inventory_category_id_3c0f34d1');
            $table->boolean('active')->default(true);
            $table->uuid('inventory_item_id')->nullable()->index('idx_lab_category_items_inventory_item_id_e146f769');
            $table->uuid('item_brand_id')->nullable()->index('idx_lab_category_items_item_brand_id_51291739');
            $table->foreign(['inventory_category_id'], 'fk_lab_category_items_inventory_category_id_5cd33ec1')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_item_id'], 'fk_lab_category_items_inventory_item_id_61c735c4')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_sub_category_id'], 'fk_lab_category_items_inventory_sub_category_id_f980538e')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['item_brand_id'], 'fk_lab_category_items_item_brand_id_d4391140')->references(['id'])->on('item_brands')->onUpdate('no action')->onDelete('set null');




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
