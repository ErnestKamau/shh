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
        if (Schema::hasTable('inventory_departments')) {
            return;
        }
        Schema::create('inventory_departments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_inventory_departments_company_id_d43344c3');
            $table->string('module', 100)->nullable();
            $table->integer('active')->nullable()->default(1);
            $table->integer('location_id')->nullable();
            $table->integer('department_head_id')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_departments');
    }
};
