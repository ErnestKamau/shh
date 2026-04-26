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

            $table->index(['run_id', 'stage_id'], 'idx_ms_run_stage');
            $table->foreign(['run_id'], 'fk_ms_run_stage_run')->references(['id'])->on('method_sequence_runs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stage_id'], 'fk_ms_run_stage_stage_id')->references(['id'])->on('method_sequence_stages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['started_by_user_id'], 'fk_ms_run_stage_started_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['completed_by_user_id'], 'fk_ms_run_stage_completed_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
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
