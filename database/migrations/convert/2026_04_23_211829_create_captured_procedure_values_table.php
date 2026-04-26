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
        Schema::create('captured_procedure_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('captured_result_id')->index('captured_procedure_values_captured_result_id_foreign');
            $table->uuid('procedure_worksheet_step_id')->index('captured_procedure_values_procedure_worksheet_step_id_foreign');
            $table->text('value')->nullable();
            $table->longText('equipment_ids')->nullable();
            $table->longText('measurand_ids')->nullable();
            $table->longText('analyst_ids')->nullable();
            $table->timestamps();
            $table->foreign(['captured_result_id'], 'fk_captured_procedure_values_captured_result_id_0b37e1e1')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_step_id'], 'fk_captured_procedure_values_procedure_worksheet_step_a989f116')->references(['id'])->on('procedure_worksheet_steps')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_procedure_values');
    }
};
