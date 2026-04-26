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
            $table->uuid('inventory_store_slot_id')->index('idx_inventory_store_slot_contents_inventory_store_slot_2a97e6f8');
            $table->uuid('inventory_item_id')->index('idx_inventory_store_slot_contents_inventory_item_id_64654cfb');
            $table->timestamps();
            $table->uuid('inventory_sub_category_id')->nullable()->index('idx_inventory_store_slot_contents_inventory_sub_catego_59c2c67e');
            $table->foreign(['inventory_item_id'], 'fk_inventory_store_slot_contents_inventory_item_id_20182cef')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_store_slot_id'], 'fk_inventory_store_slot_contents_inventory_store_slot_51af8236')->references(['id'])->on('inventory_store_slots')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_sub_category_id'], 'fk_inventory_store_slot_contents_inventory_sub_categor_fd607d60')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('set null');



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
