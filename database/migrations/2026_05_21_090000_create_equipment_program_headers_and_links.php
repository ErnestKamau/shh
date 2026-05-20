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
        Schema::create('equipment_annual_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->date('program_date');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamps();
        });

        Schema::create('equipment_preventive_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->date('program_date');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamps();
        });

        Schema::create('equipment_maintenance_register_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->date('program_date');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamps();
        });

        Schema::table('equipment_annual_maintenances', function (Blueprint $table) {
            $table->uuid('equipment_annual_program_id')->nullable()->after('equipment_id');
            $table->foreign('equipment_annual_program_id', 'eq_annual_prog_fk')
                ->references('id')
                ->on('equipment_annual_programs')
                ->onDelete('set null');
        });

        Schema::table('equipment_preventive_maintenances', function (Blueprint $table) {
            $table->uuid('equipment_preventive_program_id')->nullable()->after('equipment_id');
            $table->foreign('equipment_preventive_program_id', 'eq_prev_prog_fk')
                ->references('id')
                ->on('equipment_preventive_programs')
                ->onDelete('set null');
        });

        Schema::table('equipment_maintenance_registers', function (Blueprint $table) {
            $table->uuid('equipment_maintenance_register_program_id')->nullable()->after('equipment_id');
            $table->foreign('equipment_maintenance_register_program_id', 'eq_reg_prog_fk')
                ->references('id')
                ->on('equipment_maintenance_register_programs')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_maintenance_registers', function (Blueprint $table) {
            $table->dropForeign('eq_reg_prog_fk');
            $table->dropColumn('equipment_maintenance_register_program_id');
        });

        Schema::table('equipment_preventive_maintenances', function (Blueprint $table) {
            $table->dropForeign('eq_prev_prog_fk');
            $table->dropColumn('equipment_preventive_program_id');
        });

        Schema::table('equipment_annual_maintenances', function (Blueprint $table) {
            $table->dropForeign('eq_annual_prog_fk');
            $table->dropColumn('equipment_annual_program_id');
        });

        Schema::dropIfExists('equipment_maintenance_register_programs');
        Schema::dropIfExists('equipment_preventive_programs');
        Schema::dropIfExists('equipment_annual_programs');
    }
};
