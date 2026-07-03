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
        Schema::create('inventory_supplier_ratings', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_inventory_supplier_ratings_supplier_id_77802e83');
            $table->uuid('inventory_item_id')->index('idx_inventory_supplier_ratings_inventory_item_id_5450923d');
            $table->integer('rating');
            $table->string('title');
            $table->string('comments');
            $table->integer('rating_by');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_supplier_ratings');
    }
};
