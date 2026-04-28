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
            $table->uuid('procedure_worksheet_id')->index('idx_procedure_test_kit_rows_procedure_worksheet_id_0abca53d');
            $table->uuid('captured_result_id')->nullable()->index('idx_procedure_test_kit_rows_captured_result_id_f6c8b8fc');
            $table->integer('row_index')->default(1);
            $table->timestamps();

            $table->index(['procedure_worksheet_id', 'captured_result_id', 'row_index'], 'idx_procedure_test_kit_rows_procedure_worksheet_id_cap_b71658f4');
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
