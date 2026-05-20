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
        // 1. GCLA ANNUAL MAINTENANCE PROGRAM (TSU/F/06)
        Schema::create('equipment_annual_maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->date('serviced_date')->nullable();
            $table->string('status')->nullable();
            $table->date('next_service')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->onDelete('cascade');
        });

        // 2. GCLA EQUIPMENT PREVENTIVE MAINTENANCE PROGRAM (TSU/F/05)
        Schema::create('equipment_preventive_maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->date('year_start')->nullable(); // Select beginning of the year
            $table->string('q1_status')->nullable();
            $table->string('q2_status')->nullable();
            $table->string('q3_status')->nullable();
            $table->string('q4_status')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->onDelete('cascade');
        });

        // 3. GCLA DSM - EQUIPMENT MAINTENANCE REGISTER
        Schema::create('equipment_maintenance_registers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->string('year')->nullable(); // e.g., 2024/2025
            $table->string('service_provider')->nullable();
            $table->string('service_type')->nullable();
            $table->decimal('cost_usd', 15, 2)->nullable();
            $table->decimal('cost_tzs', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance_registers');
        Schema::dropIfExists('equipment_preventive_maintenances');
        Schema::dropIfExists('equipment_annual_maintenances');
    }
};
