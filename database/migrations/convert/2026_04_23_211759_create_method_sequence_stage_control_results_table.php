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
        Schema::create('method_sequence_stage_control_results', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_stage_data_id')->index('idx_ms_ctrl_res_stage');
            $table->uuid('control_usage_id')->index('fk_ms_ctrl_res_usage');
            $table->string('result')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->foreign(['run_stage_data_id'], 'fk_ms_ctrl_res_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['control_usage_id'], 'fk_ms_ctrl_res_usage')->references(['id'])->on('method_sequence_stage_control_usage')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_stage_control_results');
    }
};
