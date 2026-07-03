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
        if (Schema::hasTable('inventory_stores')) {
            return;
        }
        Schema::create('inventory_stores', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('company_id')->index('idx_inventory_stores_company_id_6bc6b75a');
            $table->uuid('inventory_location_id')->index('idx_inventory_stores_inventory_location_id_bdb10c30');
            $table->timestamps();
            $table->string('type_of_store', 100)->default('inventory_store');
            $table->boolean('is_frozen')->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stores');
    }
};
