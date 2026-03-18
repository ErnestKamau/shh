<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_test_kit_values', function (Blueprint $table) {
            // Scope kit values to a specific captured result (sample/analyte instance).
            // Nullable keeps backward compatibility for worksheet-level defaults.
            $table->bigInteger('captured_result_id')->nullable()->after('procedure_test_kit_column_id');

            $table->foreign('captured_result_id')
                ->references('id')
                ->on('captured_results')
                ->onDelete('cascade');

            // Prevent duplicates per instance+cell.
            $table->unique(
                ['captured_result_id', 'procedure_test_kit_row_id', 'procedure_test_kit_column_id'],
                'procedure_test_kit_values_instance_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('procedure_test_kit_values', function (Blueprint $table) {
            $table->dropUnique('procedure_test_kit_values_instance_unique');
            $table->dropForeign(['captured_result_id']);
            $table->dropColumn('captured_result_id');
        });
    }
};

