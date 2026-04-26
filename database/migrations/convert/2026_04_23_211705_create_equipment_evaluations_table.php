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
        Schema::create('equipment_evaluations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('equipment_id')->index('idx_equipment_evaluations_equipment_id_c0cd00ef');
            $table->uuid('evaluated_by')->index('idx_equipment_evaluations_evaluated_by_e29cb222');
            $table->date('evaluation_date')->index('idx_equipment_evaluations_evaluation_date_0875559e');
            $table->enum('physical_condition', ['excellent', 'good', 'fair', 'poor', 'failed']);
            $table->date('last_calibration_date')->nullable();
            $table->enum('last_calibration_status', ['pass', 'fail', 'not_applicable'])->nullable();
            $table->decimal('repair_cost_estimate', 15)->nullable();
            $table->decimal('replacement_cost_estimate', 15)->nullable();
            $table->text('impact_on_testing')->nullable();
            $table->enum('recommendation', ['repair', 'dispose', 'continue_use'])->index('idx_equipment_evaluations_recommendation_964e5f1a');
            $table->longText('calibration_history_summary')->nullable();
            $table->string('fault_report_reference')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->uuid('company_id')->index('idx_equipment_evaluations_company_id_b1477b2d');
            $table->timestamps();
            $table->foreign(['equipment_id'], 'fk_equipment_evaluations_equipment_id_3a40c184')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['evaluated_by'], 'fk_equipment_evaluations_evaluated_by_63e6f54d')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['company_id'], 'fk_equipment_evaluations_company_id_f567fb86')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_evaluations');
    }
};
