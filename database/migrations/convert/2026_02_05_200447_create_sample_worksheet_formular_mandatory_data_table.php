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
            $table->uuid('worksheet_formular_id')->index('idx_sample_worksheet_formular_mandatory_data_worksheet_f7a4cfa4');
            $table->uuid('formula_mandatory_field_id')->index('idx_sample_worksheet_formular_mandatory_data_formula_m_1122f6cb');
            $table->text('field_value')->nullable();
            $table->timestamps();

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
