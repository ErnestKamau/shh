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
        Schema::create('supplier_brands', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_brands_supplier_id_4a263968');
            $table->uuid('inventory_sub_category_id')->default(0)->index('idx_supplier_brands_inventory_sub_category_id_838faf6b');
            $table->integer('inventory_item_brand_id')->default(0);
            $table->string('supplier_image', 100)->default('/images/no-logo.png');
            $table->timestamps();
            $table->boolean('status')->default(true);
            $table->foreign(['inventory_sub_category_id'], 'fk_supplier_brands_inventory_sub_category_id_6d8e4317')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_supplier_brands_supplier_id_f3092c76')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_brands');
    }
};
