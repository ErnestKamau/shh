<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_evaluations', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->unsignedInteger('equipment_id');
            $table->bigInteger('evaluated_by');
            $table->date('evaluation_date');
            $table->enum('physical_condition', ['excellent', 'good', 'fair', 'poor', 'failed']);
            $table->date('last_calibration_date')->nullable();
            $table->enum('last_calibration_status', ['pass', 'fail', 'not_applicable'])->nullable();
            $table->decimal('repair_cost_estimate', 15, 2)->nullable();
            $table->decimal('replacement_cost_estimate', 15, 2)->nullable();
            $table->text('impact_on_testing')->nullable();
            $table->enum('recommendation', ['repair', 'dispose', 'continue_use']);
            $table->json('calibration_history_summary')->nullable();
            $table->string('fault_report_reference')->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->integer('company_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->foreign('evaluated_by')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('equipment_id');
            $table->index('evaluated_by');
            $table->index('evaluation_date');
            $table->index('recommendation');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_evaluations');
    }
}
