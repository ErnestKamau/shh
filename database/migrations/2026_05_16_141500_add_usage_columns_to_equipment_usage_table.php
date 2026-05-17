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
        Schema::table('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            if (!Schema::hasColumn('method_sequence_stage_equipment_usage', 'started_at')) {
                $table->timestamp('started_at')->nullable();
            }
            if (!Schema::hasColumn('method_sequence_stage_equipment_usage', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (!Schema::hasColumn('method_sequence_stage_equipment_usage', 'started_by_user_id')) {
                $table->uuid('started_by_user_id')->nullable();
            }
            if (!Schema::hasColumn('method_sequence_stage_equipment_usage', 'completed_by_user_id')) {
                $table->uuid('completed_by_user_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'completed_at', 'started_by_user_id', 'completed_by_user_id']);
        });
    }
};
