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
        Schema::create('results', function (Blueprint $table) {
            $table->uuid('id');
            $table->boolean('has_no_result_capture')->default(false);
            $table->uuid('captured_result_id')->index('idx_results_captured_result_id_60fa3a39');
            $table->string('sample_detail_code');
            $table->uuid('sample_detail_id')->index('idx_results_sample_detail_id_4175f0ad');
            $table->uuid('sample_header_id')->index('idx_results_sample_header_id_b79d7016');
            $table->uuid('analyte_id')->index('idx_results_analyte_id_9e1e6468');
            $table->string('analyte_code');
            $table->string('result', 100)->nullable();
            $table->string('guide')->nullable();
            $table->string('comments')->nullable();
            $table->boolean('recheck')->default(false);
            $table->decimal('guide_low', 8, 6)->nullable();
            $table->decimal('guide_high', 8, 6)->nullable();
            $table->string('unit_code')->nullable();
            $table->integer('status_code')->nullable();
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
            $table->uuid('analysis_type_id')->nullable()->index('idx_results_analysis_type_id_f23dc2dc');
            $table->string('seond_guide')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('analyte_status_contracted')->default(false);
            $table->boolean('analyte_accredited')->nullable()->default(false);
            $table->integer('analysis_type_order')->default(0);
            $table->integer('parameters_order')->default(0);
            $table->string('remark_colour', 500)->nullable();
            $table->integer('repeat_results_id')->nullable();
            $table->uuid('lab_section_id')->nullable()->index('idx_results_lab_section_id_ea6aad05');
            $table->boolean('remark_is_manual')->nullable()->default(false);
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->uuid('ltm_method_id')->nullable()->index('idx_results_ltm_method_id_52b9eafb');
            $table->string('scienctific_result')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
