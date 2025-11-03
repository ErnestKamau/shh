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
        Schema::create('method_sequence_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sample_header_id');
            $table->unsignedBigInteger('method_sequence_id');
            $table->integer('run_number');
            $table->string('run_name');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->unsignedBigInteger('started_by_user_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['sample_header_id', 'method_sequence_id'], 'idx_ms_runs_batch_seq');
            $table->index('run_number', 'idx_ms_runs_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_runs');
    }
};
