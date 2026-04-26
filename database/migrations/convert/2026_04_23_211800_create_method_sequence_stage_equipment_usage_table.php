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
            $table->uuid('run_stage_data_id')->index('idx_ms_equip_stage');
            $table->uuid('equipment_id')->index('idx_method_sequence_stage_equipment_usage_equipment_id_bf07ecd0');
            $table->string('equipment_name');
            $table->timestamps();
            $table->foreign(['run_stage_data_id'], 'fk_ms_equip_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_method_sequence_stage_equipment_usage_equipment_id_a0617a22')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');

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
