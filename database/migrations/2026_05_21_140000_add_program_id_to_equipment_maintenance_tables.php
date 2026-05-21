<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Annual Maintenance
        Schema::table('equipment_annual_maintenances', function (Blueprint $table) {
            $table->uuid('equipment_maintenance_program_id')->nullable()->after('id');
            $table->foreign('equipment_maintenance_program_id', 'fk_annual_program_id')
                  ->references('id')
                  ->on('equipment_maintenance_programs')
                  ->onDelete('cascade');
        });

        // 2. Preventive Maintenance
        Schema::table('equipment_preventive_maintenances', function (Blueprint $table) {
            $table->uuid('equipment_maintenance_program_id')->nullable()->after('id');
            $table->foreign('equipment_maintenance_program_id', 'fk_preventive_program_id')
                  ->references('id')
                  ->on('equipment_maintenance_programs')
                  ->onDelete('cascade');
        });

        // 3. Maintenance Register
        Schema::table('equipment_maintenance_registers', function (Blueprint $table) {
            $table->uuid('equipment_maintenance_program_id')->nullable()->after('id');
            $table->foreign('equipment_maintenance_program_id', 'fk_register_program_id')
                  ->references('id')
                  ->on('equipment_maintenance_programs')
                  ->onDelete('cascade');
        });

        // Auto-create default programs and link existing records
        $this->migrateExistingRecords();
    }

    private function migrateExistingRecords(): void
    {
        // Annual
        $hasAnnual = DB::table('equipment_annual_maintenances')
            ->whereNull('equipment_maintenance_program_id')
            ->exists();
            
        if ($hasAnnual) {
            $programId = Str::uuid()->toString();
            DB::table('equipment_maintenance_programs')->insert([
                'id' => $programId,
                'type' => 'annual',
                'name' => 'Default Annual Program (Legacy)',
                'program_date' => now()->toDateString(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('equipment_annual_maintenances')
                ->whereNull('equipment_maintenance_program_id')
                ->update(['equipment_maintenance_program_id' => $programId]);
        }

        // Preventive
        $hasPreventive = DB::table('equipment_preventive_maintenances')
            ->whereNull('equipment_maintenance_program_id')
            ->exists();
            
        if ($hasPreventive) {
            $programId = Str::uuid()->toString();
            DB::table('equipment_maintenance_programs')->insert([
                'id' => $programId,
                'type' => 'preventive',
                'name' => 'Default Preventive Program (Legacy)',
                'program_date' => now()->toDateString(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('equipment_preventive_maintenances')
                ->whereNull('equipment_maintenance_program_id')
                ->update(['equipment_maintenance_program_id' => $programId]);
        }

        // Register
        $hasRegister = DB::table('equipment_maintenance_registers')
            ->whereNull('equipment_maintenance_program_id')
            ->exists();
            
        if ($hasRegister) {
            $programId = Str::uuid()->toString();
            DB::table('equipment_maintenance_programs')->insert([
                'id' => $programId,
                'type' => 'register',
                'name' => 'Default Maintenance Register (Legacy)',
                'program_date' => now()->toDateString(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('equipment_maintenance_registers')
                ->whereNull('equipment_maintenance_program_id')
                ->update(['equipment_maintenance_program_id' => $programId]);
        }
    }

    public function down(): void
    {
        Schema::table('equipment_maintenance_registers', function (Blueprint $table) {
            $table->dropForeign('fk_register_program_id');
            $table->dropColumn('equipment_maintenance_program_id');
        });

        Schema::table('equipment_preventive_maintenances', function (Blueprint $table) {
            $table->dropForeign('fk_preventive_program_id');
            $table->dropColumn('equipment_maintenance_program_id');
        });

        Schema::table('equipment_annual_maintenances', function (Blueprint $table) {
            $table->dropForeign('fk_annual_program_id');
            $table->dropColumn('equipment_maintenance_program_id');
        });
    }
};
