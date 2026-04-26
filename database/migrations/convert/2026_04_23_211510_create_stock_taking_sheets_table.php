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
        Schema::create('stock_taking_sheets', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('stock_taking_id')->index('idx_stock_taking_sheets_stock_taking_id_636eb7c3');
            $table->uuid('inventory_sub_category_id')->index('idx_stock_taking_sheets_inventory_sub_category_id_d3d3bbe3');
            $table->string('code');
            $table->string('store_name');
            $table->integer('store_id');
            $table->string('slot_name');
            $table->integer('slot_id');
            $table->double('system_quantity');
            $table->double('available_quantity')->nullable();
            $table->timestamps();
            $table->text('comments')->nullable();
            $table->integer('adjusted_inventory_item_id')->nullable();
            $table->foreign(['inventory_sub_category_id'], 'fk_stock_taking_sheets_inventory_sub_category_id_a0c29164')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stock_taking_id'], 'fk_stock_taking_sheets_stock_taking_id_96e75830')->references(['id'])->on('stock_takings')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_taking_sheets');
    }
};
