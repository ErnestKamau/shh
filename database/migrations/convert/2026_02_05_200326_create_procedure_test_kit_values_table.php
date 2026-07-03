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
        if (Schema::hasTable('procedure_test_kit_values')) {
            return;
        }
        Schema::create('procedure_test_kit_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_test_kit_row_id')->index('idx_procedure_test_kit_values_procedure_test_kit_row_i_472682fa');
            $table->uuid('procedure_test_kit_column_id')->index('idx_procedure_test_kit_values_procedure_test_kit_colum_1b2283bb');
            $table->uuid('captured_result_id')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['captured_result_id', 'procedure_test_kit_row_id', 'procedure_test_kit_column_id'], 'procedure_test_kit_values_instance_unique');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_test_kit_values');
    }
};
