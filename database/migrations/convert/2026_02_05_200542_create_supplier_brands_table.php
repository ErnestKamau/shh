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
        if (Schema::hasTable('supplier_brands')) {
            return;
        }
        Schema::create('supplier_brands', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_brands_supplier_id_f5cecc96');
            $table->uuid('inventory_sub_category_id')->nullable()->index('idx_supplier_brands_inventory_sub_category_id_273e0d7b');
            $table->integer('inventory_item_brand_id')->default(0);
            $table->string('supplier_image', 100)->default('/images/no-logo.png');
            $table->timestamps();
            $table->boolean('status')->default(true);

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
