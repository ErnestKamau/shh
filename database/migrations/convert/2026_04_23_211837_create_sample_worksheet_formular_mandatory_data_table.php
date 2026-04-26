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
        Schema::create('sample_worksheet_formular_mandatory_data', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('worksheet_formular_id')->index('idx_wkst_mand_formula');
            $table->uuid('formula_mandatory_field_id')->index('idx_sample_worksheet_formular_mandatory_data_formula_m_3f38946a');
            $table->text('field_value')->nullable();
            $table->timestamps();
            $table->foreign(['worksheet_formular_id'], 'fk_wkst_mand_formula')->references(['id'])->on('sample_captured_worksheet_formulas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_mandatory_field_id'], 'fk_sample_worksheet_formular_mandatory_data_formula_ma_b1dc5bcc')->references(['id'])->on('formula_mandatory_fields')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_worksheet_formular_mandatory_data');
    }
};
