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
        Schema::create('item_brands', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('image');
            $table->uuid('inventory_sub_category_id')->index('idx_item_brands_inventory_sub_category_id_af93289c');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign(['inventory_sub_category_id'], 'fk_item_brands_inventory_sub_category_id_0cdd51ba')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_brands');
    }
};
