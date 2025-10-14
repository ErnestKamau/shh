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
        Schema::create('method_sequence_stage_sample_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_stage_data_id');
            $table->unsignedBigInteger('captured_result_id');
            $table->string('result')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            
            $table->foreign('run_stage_data_id', 'fk_ms_samp_res_stage')
                  ->references('id')
                  ->on('method_sequence_run_stage_data')
                  ->onDelete('cascade');
            
            $table->index(['run_stage_data_id', 'captured_result_id'], 'idx_ms_samp_res');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_stage_sample_results');
    }
};
