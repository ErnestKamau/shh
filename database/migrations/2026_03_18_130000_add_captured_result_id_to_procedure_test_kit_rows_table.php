<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_test_kit_rows', function (Blueprint $table) {
            // Scope kit rows to a specific captured result (sample/analyte instance).
            // Nullable keeps backward compatibility with existing shared rows.
            $table->bigInteger('captured_result_id')->nullable()->after('procedure_worksheet_id');

            $table->foreign('captured_result_id')
                ->references('id')
                ->on('captured_results')
                ->onDelete('cascade');

            $table->index(['procedure_worksheet_id', 'captured_result_id', 'row_index'], 'procedure_test_kit_rows_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::table('procedure_test_kit_rows', function (Blueprint $table) {
            $table->dropIndex('procedure_test_kit_rows_scope_idx');
            $table->dropForeign(['captured_result_id']);
            $table->dropColumn('captured_result_id');
        });
    }
};

