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
        Schema::create('stock_takings', function (Blueprint $table) {
            $table->uuid('id');
            $table->text('description');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('status')->default('In Preparation');
            $table->string('stores');
            $table->string('store_names');
            $table->integer('approved_by')->nullable();
            $table->uuid('inventory_location_id')->nullable()->default(0)->index('idx_stock_takings_inventory_location_id_eca0ec3e');
            $table->timestamps();
            $table->string('code', 100)->nullable();
            $table->smallInteger('stores_frozen')->default(0);
            $table->boolean('reviewed')->nullable();
            $table->foreign(['inventory_location_id'], 'fk_stock_takings_inventory_location_id_6aff6a64')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_takings');
    }
};
