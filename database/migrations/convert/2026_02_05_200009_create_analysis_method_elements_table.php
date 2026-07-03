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
        Schema::create('analysis_method_elements', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('analysis_method_id')->index('idx_analysis_method_elements_analysis_method_id_d073d585');
            $table->uuid('analyte_id')->index('idx_analysis_method_elements_analyte_id_f8a3310c');
            $table->double('quantity');
            $table->boolean('active');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_analysis_method_elements_company_id_ea2ad488');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_method_elements');
    }
};
