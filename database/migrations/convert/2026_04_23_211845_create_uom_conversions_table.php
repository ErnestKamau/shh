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
        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('uom1', 100);
            $table->string('uom2', 100);
            $table->double('ratio');
            $table->timestamps();
            $table->uuid('inventory_location_id')->nullable()->index('idx_uom_conversions_inventory_location_id_423e5f84');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
    }
};
