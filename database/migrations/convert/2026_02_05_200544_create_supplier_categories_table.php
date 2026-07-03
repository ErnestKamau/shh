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
        if (Schema::hasTable('supplier_categories')) {
            return;
        }
        Schema::create('supplier_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_categories_supplier_id_d9c48a36');
            $table->uuid('inventory_sub_category_id')->index('idx_supplier_categories_inventory_sub_category_id_888970f0');
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
        Schema::dropIfExists('supplier_categories');
    }
};
