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
        Schema::create('qc_results', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('captured_result_id')->index('idx_qc_results_captured_result_id_33514bf5');
            $table->string('sample_detail_code');
            $table->uuid('sample_detail_id')->index('idx_qc_results_sample_detail_id_16e179f9');
            $table->uuid('sample_header_id')->index('idx_qc_results_sample_header_id_65687b6e');
            $table->uuid('analyte_id')->index('idx_qc_results_analyte_id_2445a217');
            $table->string('analyte_code');
            $table->string('result', 100)->nullable();
            $table->string('guide')->nullable();
            $table->string('comments')->nullable();
            $table->boolean('recheck')->default(false);
            $table->decimal('guide_low', 8, 6)->nullable();
            $table->decimal('guide_high', 8, 6)->nullable();
            $table->string('unit_code')->nullable();
            $table->string('status_code', 50)->nullable();
            $table->boolean('is_qc_processed')->default(false);
            $table->unsignedBigInteger('analyte_processed_id')->nullable();
            $table->string('reporting_symbol')->nullable();
            $table->boolean('qc')->nullable();
            $table->decimal('correct_target', 8, 6)->nullable();
            $table->decimal('standard_target', 8, 6)->nullable();
            $table->string('recommendations')->nullable();
            $table->decimal('initial_result', 8, 6)->nullable();
            $table->string('initial_reporting_symbol')->nullable();
            $table->decimal('very_low_guide', 8, 6)->nullable();
            $table->decimal('very_high_guide', 8, 6)->nullable();
            $table->timestamps();
            $table->uuid('analysis_type_id')->nullable()->index('idx_qc_results_analysis_type_id_c15f8739');
            $table->string('seond_guide')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('analyte_status_contracted')->default(false);
            $table->boolean('analyte_accredited')->nullable()->default(false);
            $table->integer('analysis_type_order')->default(0);
            $table->integer('parameters_order')->default(0);
            $table->string('remark_colour', 500)->nullable();
            $table->uuid('qc_scheme_id')->nullable()->index('idx_qc_results_qc_scheme_id_65cb93f4');
            $table->uuid('qc_type_id')->nullable()->index('idx_qc_results_qc_type_id_0e9332d0');
            $table->string('standard_value', 100)->nullable();
            $table->uuid('result_id')->nullable()->index('idx_qc_results_result_id_f640e32c');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_results');
    }
};
