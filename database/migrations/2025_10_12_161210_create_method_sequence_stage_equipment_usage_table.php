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
            $table->id();
            $table->unsignedBigInteger('run_stage_data_id');
            $table->unsignedBigInteger('equipment_id');
            $table->string('equipment_name');
            $table->timestamps();
            
            $table->foreign('run_stage_data_id', 'fk_ms_equip_stage')
                  ->references('id')
                  ->on('method_sequence_run_stage_data')
                  ->onDelete('cascade');
            
            $table->index('run_stage_data_id', 'idx_ms_equip_stage');
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
