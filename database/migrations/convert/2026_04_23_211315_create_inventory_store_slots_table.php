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
            $table->uuid('inventory_store_id')->index('idx_inventory_store_slots_inventory_store_id_77e068a0');
            $table->timestamps();
            $table->foreign(['inventory_store_id'], 'fk_inventory_store_slots_inventory_store_id_d7fdda03')->references(['id'])->on('inventory_stores')->onUpdate('no action')->onDelete('cascade');

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
