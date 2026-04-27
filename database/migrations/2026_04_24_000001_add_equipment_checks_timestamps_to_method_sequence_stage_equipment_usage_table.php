<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds timestamp columns to track when equipment is turned on/off during analysis runs.
     * These timestamps represent the duration the equipment was in use for a specific analysis stage.
     */
    public function up(): void
    {
        Schema::table('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            // Fix column type - must be signed to match users.id
            DB::statement('ALTER TABLE method_sequence_stage_equipment_usage MODIFY started_by_user_id BIGINT NULL');
            DB::statement('ALTER TABLE method_sequence_stage_equipment_usage MODIFY completed_by_user_id BIGINT NULL');
        });
        
        // Add foreign key constraints
        Schema::table('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            // Try to add foreign keys - they might fail if already exist, that's OK
            try {
                $table->foreign('started_by_user_id', 'fk_mseu_started_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            } catch (\Exception $e) {
                // Foreign key might already exist or DB constraint prevents it
            }
            
            try {
                $table->foreign('completed_by_user_id', 'fk_mseu_completed_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            } catch (\Exception $e) {
                // Foreign key might already exist or DB constraint prevents it
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            $table->dropForeignKey('fk_mseu_started_by');
            $table->dropForeignKey('fk_mseu_completed_by');
            $table->dropIndex('idx_mseu_started_at');
            $table->dropIndex('idx_mseu_completed_at');
            $table->dropColumn(['started_at', 'completed_at', 'started_by_user_id', 'completed_by_user_id']);
        });
    }
};
