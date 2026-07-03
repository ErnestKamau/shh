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
        if (Schema::hasTable('zones')) {
            return;
        }
        Schema::create('zones', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('key');
            $table->string('value');
            $table->text('description')->nullable();
            $table->string('module')->index('idx_zones_module_cff8850d');
            $table->uuid('inventory_location_id')->index('idx_zones_inventory_location_id_4b49513a');
            $table->timestamps();

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
