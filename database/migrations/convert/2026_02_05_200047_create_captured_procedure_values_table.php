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
        if (Schema::hasTable('captured_procedure_values')) {
            return;
        }
        Schema::create('captured_procedure_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('captured_result_id')->index('idx_captured_procedure_values_captured_result_id_a0149ae3');
            $table->uuid('procedure_worksheet_step_id')->index('idx_captured_procedure_values_procedure_worksheet_step_e8ce3c71');
            $table->text('value')->nullable();
            $table->longText('equipment_ids')->nullable();
            $table->longText('measurand_ids')->nullable();
            $table->longText('analyst_ids')->nullable();
            $table->timestamps();
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
