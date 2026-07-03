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
        if (Schema::hasTable('inventory_locations')) {
            return;
        }
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->integer('level')->default(1);
            $table->uuid('inventory_location_id')->nullable()->index('idx_inventory_locations_inventory_location_id_7e467d9b');
            $table->timestamps();
            $table->smallInteger('active')->nullable()->default(1);
            $table->uuid('company_id')->nullable()->index('idx_inventory_locations_company_id_33f62ac8');
            $table->integer('currency')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};
