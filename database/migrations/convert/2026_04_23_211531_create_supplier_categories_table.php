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
        Schema::create('supplier_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_categories_supplier_id_b1b9aa2e');
            $table->uuid('inventory_sub_category_id')->index('idx_supplier_categories_inventory_sub_category_id_75f92eb7');
            $table->integer('inventory_item_brand_id')->default(0);
            $table->string('supplier_image', 100)->default('/images/no-logo.png');
            $table->timestamps();
            $table->boolean('status')->default(true);
            $table->foreign(['inventory_sub_category_id'], 'fk_supplier_categories_inventory_sub_category_id_a989c7c5')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_supplier_categories_supplier_id_b44737ac')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_categories');
    }
};
