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
        Schema::create('procedure_test_kit_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_test_kit_row_id')->index('procedure_test_kit_values_procedure_test_kit_row_id_foreign');
            $table->uuid('procedure_test_kit_column_id')->index('procedure_test_kit_values_procedure_test_kit_column_id_foreign');
            $table->uuid('captured_result_id')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['captured_result_id', 'procedure_test_kit_row_id', 'procedure_test_kit_column_id'], 'procedure_test_kit_values_instance_unique');
            $table->foreign(['captured_result_id'], 'fk_procedure_test_kit_values_captured_result_id_5eb540d3')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_test_kit_column_id'], 'fk_procedure_test_kit_values_procedure_test_kit_column_440c979c')->references(['id'])->on('procedure_test_kit_columns')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_test_kit_row_id'], 'fk_procedure_test_kit_values_procedure_test_kit_row_id_95a59ff6')->references(['id'])->on('procedure_test_kit_rows')->onUpdate('no action')->onDelete('cascade');
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
