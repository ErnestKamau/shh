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
        Schema::create('inventory_store_slots', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('inventory_store_id')->index('idx_inventory_store_slots_inventory_store_id_bb6de7c2');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_store_slots');
    }
};
