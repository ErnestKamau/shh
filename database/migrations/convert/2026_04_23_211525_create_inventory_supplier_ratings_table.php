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
            $table->uuid('supplier_id')->index('idx_inventory_supplier_ratings_supplier_id_2e7821f9');
            $table->uuid('inventory_item_id')->index('idx_inventory_supplier_ratings_inventory_item_id_c5ce8471');
            $table->integer('rating');
            $table->string('title');
            $table->string('comments');
            $table->integer('rating_by');
            $table->timestamps();
            $table->foreign(['inventory_item_id'], 'fk_inventory_supplier_ratings_inventory_item_id_686c132c')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_inventory_supplier_ratings_supplier_id_f72c0d49')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');


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
