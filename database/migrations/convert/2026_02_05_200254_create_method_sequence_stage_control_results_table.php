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
        if (Schema::hasTable('method_sequence_stage_control_results')) {
            return;
        }
        Schema::create('method_sequence_stage_control_results', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_stage_data_id')->index('idx_method_sequence_stage_control_results_run_stage_da_1e406930');
            $table->uuid('control_usage_id')->index('idx_method_sequence_stage_control_results_control_usag_b20c4db1');
            $table->string('result')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
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
