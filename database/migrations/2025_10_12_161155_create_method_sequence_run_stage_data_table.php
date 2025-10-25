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
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('stage_id');
            $table->date('date_in')->nullable();
            $table->time('time_in')->nullable();
            $table->unsignedBigInteger('started_by_user_id')->nullable();
            $table->date('date_out')->nullable();
            $table->time('time_out')->nullable();
            $table->unsignedBigInteger('completed_by_user_id')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
            
            $table->foreign('run_id', 'fk_ms_run_stage_run')
                  ->references('id')
                  ->on('method_sequence_runs')
                  ->onDelete('cascade');
            
            $table->index(['run_id', 'stage_id'], 'idx_ms_run_stage');
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
