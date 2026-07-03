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
        if (Schema::hasTable('analysis_elements')) {
            return;
        }
        Schema::create('analysis_elements', function (Blueprint $table) {
            $table->uuid('id')->index('idx_analysis_elements_id_e9d0159b');
            $table->uuid('analyte_id')->index('idx_analysis_elements_analyte_id_29329f9e');
            $table->integer('decimal_places')->nullable()->default(2);
            $table->string('reporting_symbol')->nullable();
            $table->string('reporting_unit')->nullable();
            $table->boolean('non_detectable')->nullable();
            $table->boolean('non_accredited')->nullable()->default(false);
            $table->boolean('active')->nullable()->default(true);
            $table->uuid('company_id')->nullable()->index('idx_analysis_elements_company_id_70b3bf0f');
            $table->uuid('analysis_type_id')->index('idx_analysis_elements_analysis_type_id_dd2296e0');
            $table->boolean('show_on_report')->nullable()->default(true);
            $table->timestamps();
            $table->uuid('procedure_worksheet_id')->nullable()->index('idx_analysis_elements_procedure_worksheet_id_411805d7');
            $table->uuid('equipment_id')->nullable()->index('idx_analysis_elements_equipment_id_16853057');
            $table->uuid('method')->nullable()->index('idx_analysis_elements_method_abea260f');
            $table->smallInteger('is_manual')->nullable()->default(0);
            $table->string('operator_id', 100)->nullable()->index('idx_analysis_elements_operator_id_bd9d93e9');
            $table->double('significant_figures')->nullable()->default(3);
            $table->double('lod')->nullable();
            $table->double('hod')->nullable();
            $table->integer('level')->nullable()->index('idx_analysis_elements_level_562a3e6d');
            $table->integer('reporting_time')->nullable()->default(0);
            $table->integer('lab_section_id')->nullable()->index('idx_analysis_elements_lab_section_id_daf24b5e');
            $table->boolean('remark_is_manual')->nullable()->default(false);
            $table->boolean('result_is_calculated')->default(false);
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->uuid('ltm_method_id')->nullable();
            $table->boolean('recommend_remedies')->default(false);
            $table->uuid('remedy_header_id')->nullable()->index('idx_analysis_elements_remedy_header_id_590ed162');
            $table->uuid('formular_id')->nullable();
            $table->boolean('has_method_sequence')->default(false);
            $table->uuid('method_sequence_id')->nullable()->index('idx_analysis_elements_method_sequence_id_86894b32');

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_elements');
    }
};
