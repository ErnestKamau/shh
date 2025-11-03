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
        Schema::create('method_sequence_run_samples', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('captured_result_id');
            $table->unsignedBigInteger('sample_detail_id');
            $table->unsignedBigInteger('sample_header_id');
            $table->timestamps();
            
            $table->foreign('run_id', 'fk_ms_run_samples_run')
                  ->references('id')
                  ->on('method_sequence_runs')
                  ->onDelete('cascade');
            
            $table->index('run_id', 'idx_ms_run_samples_run');
            $table->index('captured_result_id', 'idx_ms_run_samples_captured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_run_samples');
    }
};
