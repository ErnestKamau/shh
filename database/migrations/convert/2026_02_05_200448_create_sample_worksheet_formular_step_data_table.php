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
            $table->uuid('worksheet_formular_id')->index('idx_sample_worksheet_formular_step_data_worksheet_form_9d8b28dd');
            $table->uuid('formula_step_id')->index('idx_sample_worksheet_formular_step_data_formula_step_i_4a66bc01');
            $table->text('step_value')->nullable();
            $table->uuid('overridden_lookup_table_id')->nullable()->index('idx_sample_worksheet_formular_step_data_overridden_loo_fb33b2fb');
            $table->timestamps();

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
