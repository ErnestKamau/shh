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
        Schema::create('zones', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('key');
            $table->string('value');
            $table->text('description')->nullable();
            $table->string('module')->index('idx_zones_module_0a0f058c');
            $table->uuid('inventory_location_id')->index('idx_zones_inventory_location_id_9d930f4a');
            $table->timestamps();
            $table->foreign(['inventory_location_id'], 'fk_zones_inventory_location_id_1b496aad')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
