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
        Schema::create('method_sequence_run_stage_data', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_id');
            $table->uuid('stage_id');
            $table->date('date_in')->nullable();
            $table->time('time_in')->nullable();
            $table->uuid('started_by_user_id')->nullable();
            $table->date('date_out')->nullable();
            $table->time('time_out')->nullable();
            $table->uuid('completed_by_user_id')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->decimal('safe_duration_hours')->nullable();
            $table->decimal('duration_hours')->nullable();
            $table->boolean('safe_duration_alert_sent')->default(false);
            $table->boolean('duration_alert_sent')->default(false);
            $table->timestamps();

            $table->index(['run_id', 'stage_id'], 'idx_method_sequence_run_stage_data_run_id_stage_id_292bf45c');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_run_stage_data');
    }
};
