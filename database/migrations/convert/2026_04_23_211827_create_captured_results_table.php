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
        Schema::create('captured_results', function (Blueprint $table) {
            $table->uuid('id');
            $table->boolean('has_no_result_capture')->default(false);
            $table->string('sample_detail_code')->index('sample_detail_code');
            $table->uuid('sample_detail_id')->index('sample_detail_id');
            $table->uuid('sample_header_id')->index('sample_header_id');
            $table->uuid('analyte_id')->index('analyte_id');
            $table->text('analyte_code')->index('analyte_code');
            $table->uuid('equipment_id')->nullable()->default(0)->index('idx_captured_results_equipment_id_e90a810e');
            $table->text('result')->nullable();
            $table->uuid('user_id')->index('idx_captured_results_user_id_75eae62a');
            $table->timestamps();
            $table->uuid('analysis_type_id')->nullable()->index('analysis_type_id');
            $table->integer('operator_id')->nullable()->index('operator_id');
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
            $table->uuid('reporting_unit_id')->nullable()->index('idx_captured_results_reporting_unit_id_b58dc3d9');
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
            $table->uuid('stage_header_id')->nullable()->index('idx_captured_results_stage_header_id_0fa123be');
            $table->uuid('analysis_element_id')->nullable()->index('idx_captured_results_analysis_element_id_7e046a90');
            $table->uuid('formular_id')->nullable();
            $table->uuid('method_sequence_id')->nullable()->index('idx_captured_results_method_sequence_id_8b2d519a');
            $table->uuid('procedure_worksheet_id')->nullable()->index('idx_captured_results_procedure_worksheet_id_bf9d8f27');
            $table->boolean('worksheet_posted')->default(false)->index('idx_captured_results_worksheet_posted_67d6fc4d');
            $table->boolean('has_procedure_worksheet')->default(false);
            $table->uuid('batch_attachment_id')->nullable()->index('batch_attachment_id');
            $table->foreign(['analysis_element_id'], 'fk_captured_results_analysis_element_id_60f5f36e')->references(['id'])->on('analysis_elements')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analysis_type_id'], 'fk_captured_results_analysis_type_id_58a53b2e')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analyte_id'], 'fk_captured_results_analyte_id_9470012c')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['batch_attachment_id'], 'fk_captured_results_batch_attachment_id_69c1960f')->references(['id'])->on('batch_attachments')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_captured_results_equipment_id_21ffab5e')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['method_sequence_id'], 'fk_captured_results_method_sequence_id_003f0cf3')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['procedure_worksheet_id'], 'fk_captured_results_procedure_worksheet_id_fa59b1b1')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['reporting_unit_id'], 'fk_captured_results_reporting_unit_id_8dadf385')->references(['id'])->on('reporting_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_captured_results_sample_detail_id_1440397f')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_captured_results_sample_header_id_602cf157')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stage_header_id'], 'fk_captured_results_stage_header_id_81f6c2e4')->references(['id'])->on('stage_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_captured_results_user_id_20ffa6bc')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('method_id')->references('id')->on('methods')->onDelete('set null');
            $table->foreign('main_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('secondary_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('lab_section_id')->references('id')->on('sample_analysis_stages')->onDelete('set null');
            $table->foreign('third_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('ltm_method_id')->references('id')->on('methods')->onDelete('set null');
            $table->foreign('formular_id')->references('id')->on('formulars')->onDelete('set null');












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
