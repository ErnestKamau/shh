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
        if (Schema::hasTable('captured_results')) {
            return;
        }
        Schema::create('captured_results', function (Blueprint $table) {
            $table->uuid('id');
            $table->boolean('has_no_result_capture')->default(false);
            $table->string('sample_detail_code')->index('idx_captured_results_sample_detail_code_8563c754');
            $table->uuid('sample_detail_id')->index('idx_captured_results_sample_detail_id_93e8429a');
            $table->uuid('sample_header_id')->index('idx_captured_results_sample_header_id_230bd10e');
            $table->uuid('analyte_id')->index('idx_captured_results_analyte_id_ffa871cf');
            $table->text('analyte_code')->index('idx_captured_results_analyte_code_797afceb');
            $table->uuid('equipment_id')->nullable()->index('idx_captured_results_equipment_id_da17d246');
            $table->text('result')->nullable();
            $table->uuid('user_id')->index('idx_captured_results_user_id_dc037a10');
            $table->timestamps();
            $table->uuid('analysis_type_id')->nullable()->index('idx_captured_results_analysis_type_id_f985361c');
            $table->integer('operator_id')->nullable()->index('idx_captured_results_operator_id_a55badb3');
            $table->uuid('method_id')->nullable();
            $table->dateTime('machine_update_date')->nullable();
            $table->boolean('analyte_status_contracted')->default(false);
            $table->text('remark')->nullable();
            $table->boolean('analyte_accredited')->nullable()->default(false);
            $table->uuid('main_standard_id')->nullable();
            $table->uuid('secondary_standard_id')->nullable();
            $table->text('main_value')->nullable();
            $table->text('secondary_value')->nullable();
            $table->integer('analysis_type_order')->default(0);
            $table->integer('parameters_order')->default(0);
            $table->string('remark_colour', 300)->nullable();
            $table->string('result_reporting_symbol', 100)->nullable();
            $table->integer('repeat_captured_id')->nullable();
            $table->uuid('lab_section_id')->nullable();
            $table->boolean('remark_is_manual')->nullable()->default(false);
            $table->uuid('reporting_unit_id')->nullable()->index('idx_captured_results_reporting_unit_id_35af21e5');
            $table->string('measure_uncertanity', 100)->nullable();
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->string('third_value')->nullable();
            $table->text('sec_remark')->nullable();
            $table->text('third_remark')->nullable();
            $table->uuid('third_standard_id')->nullable();
            $table->uuid('ltm_method_id')->nullable();
            $table->text('scienctific_result')->nullable();
            $table->text('superscript_number')->nullable();
            $table->text('superscript_negative')->nullable();
            $table->text('supercsript_base')->nullable();
            $table->uuid('stage_header_id')->nullable()->index('idx_captured_results_stage_header_id_4ef95697');
            $table->uuid('analysis_element_id')->nullable()->index('idx_captured_results_analysis_element_id_8f9afa99');
            $table->uuid('formular_id')->nullable();
            $table->uuid('method_sequence_id')->nullable()->index('idx_captured_results_method_sequence_id_7b3ffef9');
            $table->uuid('procedure_worksheet_id')->nullable()->index('idx_captured_results_procedure_worksheet_id_dfa40fc0');
            $table->boolean('worksheet_posted')->default(false)->index('idx_captured_results_worksheet_posted_7d5c8ff9');
            $table->boolean('has_procedure_worksheet')->default(false);
            $table->uuid('batch_attachment_id')->nullable()->index('idx_captured_results_batch_attachment_id_c20314b1');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_results');
    }
};
