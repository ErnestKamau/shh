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
        if (Schema::hasTable('module_pre_configs_07')) {
            return;
        }
        Schema::create('module_pre_configs_07', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('type');
            $table->string('description', 1024)->nullable();
            $table->timestamps();
            $table->string('module')->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_module_pre_configs_07_inventory_location_id_a0dbb193');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_pre_configs_07');
    }
};
