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
            $table->uuid('captured_result_id')->index('idx_method_sequence_stage_sample_results_captured_resu_049bab0f');
            $table->string('result')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->index(['run_stage_data_id', 'captured_result_id'], 'idx_method_sequence_stage_sample_results_run_stage_dat_41e69bfa');

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
