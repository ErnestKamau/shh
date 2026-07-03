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
        if (Schema::hasTable('stock_transfers')) {
            return;
        }
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('description');
            $table->string('created_by');
            $table->integer('location_id');
            $table->integer('department_id');
            $table->timestamps();
            $table->string('code')->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_stock_transfers_inventory_location_id_e3557c0c');
            $table->string('status', 100)->default('Pending');

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
