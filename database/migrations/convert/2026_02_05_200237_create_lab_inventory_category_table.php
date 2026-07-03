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
        if (Schema::hasTable('lab_inventory_category')) {
            return;
        }
        Schema::create('lab_inventory_category', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_lab_inventory_category_company_id_96850420');
            $table->uuid('inventory_location_id')->nullable()->index('idx_lab_inventory_category_inventory_location_id_2c0bf215');
            $table->uuid('inventory_category_id')->nullable()->index('idx_lab_inventory_category_inventory_category_id_9dc464b1');
            $table->boolean('active')->default(true);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_inventory_category');
    }
};
