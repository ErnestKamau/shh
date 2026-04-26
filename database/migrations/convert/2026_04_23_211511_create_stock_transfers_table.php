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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('description');
            $table->string('created_by');
            $table->integer('location_id');
            $table->integer('department_id');
            $table->timestamps();
            $table->string('code')->nullable();
            $table->uuid('inventory_location_id')->default(0)->index('idx_stock_transfers_inventory_location_id_109be50e');
            $table->string('status', 100)->default('Pending');
            $table->foreign(['inventory_location_id'], 'fk_stock_transfers_inventory_location_id_c3d6af6b')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
