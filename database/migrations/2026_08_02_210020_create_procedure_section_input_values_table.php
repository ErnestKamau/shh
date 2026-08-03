<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_section_input_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procedure_worksheet_id');
            $table->string('batch_id');
            $table->uuid('captured_result_id')->nullable(); // null = shared (not per-sample)
            $table->string('section_key');
            $table->string('row_key');
            $table->string('column_key');
            $table->text('value')->nullable();
            $table->string('uom_id')->nullable(); // reporting_units.id for reagent_input columns
            $table->timestamp('stock_deducted_at')->nullable();

            $table->timestamps();

            $table->foreign('procedure_worksheet_id')
                ->references('id')
                ->on('procedure_worksheets')
                ->onDelete('cascade');

            // Unique constraint: one value per (worksheet, batch, captured_result_or_null, section, row, column)
            $table->unique(
                ['procedure_worksheet_id', 'batch_id', 'section_key', 'row_key', 'column_key', 'captured_result_id'],
                'psiv_unique'
            );

            $table->index(['procedure_worksheet_id', 'batch_id', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_section_input_values');
    }
};
