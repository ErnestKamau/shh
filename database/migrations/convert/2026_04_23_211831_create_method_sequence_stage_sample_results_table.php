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
            $table->uuid('id');
            $table->uuid('run_stage_data_id');
            $table->uuid('captured_result_id')->index('idx_method_sequence_stage_sample_results_captured_resu_113cf2bb');
            $table->string('result')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->index(['run_stage_data_id', 'captured_result_id'], 'idx_ms_samp_res');
            $table->foreign(['run_stage_data_id'], 'fk_ms_samp_res_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_method_sequence_stage_sample_results_captured_resul_de3fa0d2')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

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
