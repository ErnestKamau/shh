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
            $table->uuid('equipment_id')->index('idx_equipment_evaluations_equipment_id_9cfbe358');
            $table->uuid('evaluated_by')->index('idx_equipment_evaluations_evaluated_by_839115da');
            $table->date('evaluation_date')->index('idx_equipment_evaluations_evaluation_date_5131bebe');
            $table->enum('physical_condition', ['excellent', 'good', 'fair', 'poor', 'failed']);
            $table->date('last_calibration_date')->nullable();
            $table->enum('last_calibration_status', ['pass', 'fail', 'not_applicable'])->nullable();
            $table->decimal('repair_cost_estimate', 15)->nullable();
            $table->decimal('replacement_cost_estimate', 15)->nullable();
            $table->text('impact_on_testing')->nullable();
            $table->enum('recommendation', ['repair', 'dispose', 'continue_use'])->index('idx_equipment_evaluations_recommendation_9206b430');
            $table->longText('calibration_history_summary')->nullable();
            $table->string('fault_report_reference')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->uuid('company_id')->index('idx_equipment_evaluations_company_id_f386c501');
            $table->timestamps();

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
