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
        if (Schema::hasTable('sample_workflow_decontamination_log_items')) {
            return;
        }

        Schema::create('sample_workflow_decontamination_log_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('decontamination_log_id');
            $table->uuid('lab_section_id');
            $table->uuid('lab_decontamination_area_id');
            $table->boolean('swabbing');
            $table->timestamps();

            $table->index('decontamination_log_id');
            $table->index('lab_section_id');
            $table->index('lab_decontamination_area_id');
            $table->unique(
                ['decontamination_log_id', 'lab_decontamination_area_id'],
                'sample_workflow_decon_log_items_unique'
            );

            $table->foreign('decontamination_log_id')
                ->references('id')
                ->on('sample_workflow_decontamination_logs')
                ->cascadeOnDelete();

            $table->foreign('lab_section_id')
                ->references('id')
                ->on('lab_sections')
                ->cascadeOnDelete();

            $table->foreign('lab_decontamination_area_id')
                ->references('id')
                ->on('lab_decontamination_areas')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_workflow_decontamination_log_items');
    }
};
