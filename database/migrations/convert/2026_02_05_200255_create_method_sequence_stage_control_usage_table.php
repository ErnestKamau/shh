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
        if (Schema::hasTable('method_sequence_stage_control_usage')) {
            return;
        }
        Schema::create('method_sequence_stage_control_usage', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_stage_data_id')->index('idx_method_sequence_stage_control_usage_run_stage_data_1820889a');
            $table->unsignedBigInteger('control_id');
            $table->string('control_name');
            $table->decimal('volume', 10)->nullable();
            $table->string('unit')->nullable();
            $table->string('batch_number')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_stage_control_usage');
    }
};
