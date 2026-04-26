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
        Schema::create('sample_worksheet_formular_step_data', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('worksheet_formular_id')->index('idx_wkst_step_formula');
            $table->uuid('formula_step_id')->index('idx_sample_worksheet_formular_step_data_formula_step_i_82c2213e');
            $table->text('step_value')->nullable();
            $table->uuid('overridden_lookup_table_id')->nullable()->index('fk_override_lookup');
            $table->timestamps();
            $table->foreign(['overridden_lookup_table_id'], 'fk_override_lookup')->references(['id'])->on('lookup_tables')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['worksheet_formular_id'], 'fk_wkst_step_formula')->references(['id'])->on('sample_captured_worksheet_formulas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_step_id'], 'fk_sample_worksheet_formular_step_data_formula_step_id_753dd4c6')->references(['id'])->on('formula_steps')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_worksheet_formular_step_data');
    }
};
