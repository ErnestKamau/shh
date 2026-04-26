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
        Schema::create('request_entity_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('request_id');
            $table->integer('store_id');
            $table->integer('slot_id');
            $table->uuid('inventory_sub_category_id')->index('idx_request_entity_items_inventory_sub_category_id_ee14bd5c');
            $table->decimal('quantity', 10, 0);
            $table->decimal('net_value', 10, 0);
            $table->integer('currency')->nullable();
            $table->timestamps();
            $table->string('action', 100)->default('normal');
            $table->string('status', 100)->default('pending');
            $table->date('gr_expiry')->default('2099-12-31');
            $table->uuid('inventory_item_id')->nullable()->index('idx_request_entity_items_inventory_item_id_142029a7');
            $table->integer('issued_by')->nullable();
            $table->string('lot_no', 100)->nullable();
            $table->text('comments')->nullable();
            $table->integer('ammendment')->default(1);
            $table->integer('request_entity_item_ammended_id')->default(0);
            $table->date('date_of_manufacture')->nullable();
            $table->uuid('item_brand_id')->default(0)->index('idx_request_entity_items_item_brand_id_bcbac781');
            $table->integer('starting_sample')->nullable()->default(1);
            $table->string('catalog_number', 128)->nullable()->index('catalog_number');
            $table->string('uom', 100)->nullable();
            $table->string('test', 32)->nullable();
            $table->text('remarks')->nullable();
            $table->string('shipping_mode', 100)->nullable();
            $table->double('unit_cost');
            $table->foreign(['inventory_item_id'], 'fk_request_entity_items_inventory_item_id_86a0622d')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_sub_category_id'], 'fk_request_entity_items_inventory_sub_category_id_89053d61')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['item_brand_id'], 'fk_request_entity_items_item_brand_id_719ceceb')->references(['id'])->on('item_brands')->onUpdate('no action')->onDelete('cascade');



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
