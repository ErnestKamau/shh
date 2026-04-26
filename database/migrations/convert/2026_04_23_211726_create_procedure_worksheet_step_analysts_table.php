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
        Schema::create('procedure_worksheet_step_analysts', function (Blueprint $table) {
            $table->uuid('id');
            $table->bigInteger('batch_id');
            $table->uuid('analyte_id')->index('idx_procedure_worksheet_step_analysts_analyte_id_7c2dcb63');
            $table->uuid('procedure_worksheet_id')->index('idx_procedure_worksheet_step_analysts_procedure_worksh_01d853ba');
            $table->uuid('procedure_worksheet_step_id')->index('idx_procedure_worksheet_step_analysts_procedure_worksh_c70ed5fa');
            $table->longText('analyst_ids')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'analyte_id', 'procedure_worksheet_id', 'procedure_worksheet_step_id'], 'pws_step_analysts_unique');
            $table->foreign(['analyte_id'], 'fk_procedure_worksheet_step_analysts_analyte_id_bd94285f')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_worksheet_step_analysts_procedure_workshe_a59794e0')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_step_id'], 'fk_procedure_worksheet_step_analysts_procedure_workshe_b11f1c3b')->references(['id'])->on('procedure_worksheet_steps')->onUpdate('no action')->onDelete('cascade');



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
