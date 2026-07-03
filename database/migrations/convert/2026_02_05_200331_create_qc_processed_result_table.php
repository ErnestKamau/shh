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
        if (Schema::hasTable('qc_processed_result')) {
            return;
        }
        Schema::create('qc_processed_result', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_type_id')->index('idx_qc_processed_result_sample_type_id_1c7cdfb4');
            $table->uuid('analysis_type_id')->index('idx_qc_processed_result_analysis_type_id_4e3a3669');
            $table->uuid('analyte_id')->index('idx_qc_processed_result_analyte_id_d75f8ada');
            $table->integer('method_id');
            $table->uuid('standard_id')->index('idx_qc_processed_result_standard_id_2f361a38');
            $table->uuid('standard_value_id')->index('idx_qc_processed_result_standard_value_id_4cb4574d');
            $table->double('robust_standard_deviation')->nullable();
            $table->double('robust_mean')->nullable();
            $table->double('robust_median')->nullable();
            $table->double('robust_cv')->nullable();
            $table->double('robust_cv_percentage')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_processed_result');
    }
};
