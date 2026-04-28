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
        Schema::create('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('run_stage_data_id')->index('idx_method_sequence_stage_equipment_usage_run_stage_da_26d34b38');
            $table->uuid('equipment_id')->index('idx_method_sequence_stage_equipment_usage_equipment_id_b5a88548');
            $table->string('equipment_name');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_stage_equipment_usage');
    }
};
