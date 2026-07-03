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
        if (Schema::hasTable('procedure_worksheet_step_analysts')) {
            return;
        }
        Schema::create('procedure_worksheet_step_analysts', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('batch_id')->index('idx_procedure_worksheet_step_analysts_batch_id_86836924');
            $table->uuid('analyte_id')->index('idx_procedure_worksheet_step_analysts_analyte_id_91350cd9');
            $table->uuid('procedure_worksheet_id')->index('idx_procedure_worksheet_step_analysts_procedure_worksh_2b736ee6');
            $table->uuid('procedure_worksheet_step_id')->index('idx_procedure_worksheet_step_analysts_procedure_worksh_2b793940');
            $table->longText('analyst_ids')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'analyte_id', 'procedure_worksheet_id', 'procedure_worksheet_step_id'], 'pws_step_analysts_unique');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_worksheet_step_analysts');
    }
};
