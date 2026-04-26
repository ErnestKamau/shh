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
        Schema::create('inventory_departments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->default(0)->index('idx_inventory_departments_company_id_5c527882');
            $table->string('module', 100)->nullable();
            $table->integer('active')->nullable()->default(1);
            $table->integer('location_id')->nullable();
            $table->integer('department_head_id')->nullable();
            $table->foreign(['company_id'], 'fk_inventory_departments_company_id_a20c72c8')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');

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
