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
        Schema::create('procedure_test_kit_rows', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('procedure_test_kit_rows_procedure_worksheet_id_foreign');
            $table->uuid('captured_result_id')->nullable()->index('procedure_test_kit_rows_captured_result_id_foreign');
            $table->integer('row_index')->default(1);
            $table->timestamps();

            $table->index(['procedure_worksheet_id', 'captured_result_id', 'row_index'], 'procedure_test_kit_rows_scope_idx');
            $table->foreign(['captured_result_id'], 'fk_procedure_test_kit_rows_captured_result_id_458b1822')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_test_kit_rows_procedure_worksheet_id_4b8a412f')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_test_kit_rows');
    }
};
