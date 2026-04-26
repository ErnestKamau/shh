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
        Schema::create('analysis_elements', function (Blueprint $table) {
            $table->uuid('id')->index('id');
            $table->uuid('analyte_id')->index('analyte_id');
            $table->integer('decimal_places')->nullable()->default(2);
            $table->string('reporting_symbol')->nullable();
            $table->string('reporting_unit')->nullable();
            $table->boolean('non_detectable')->nullable();
            $table->boolean('non_accredited')->nullable()->default(false);
            $table->boolean('active')->nullable()->default(true);
            $table->uuid('company_id')->nullable()->default(1)->index('idx_analysis_elements_company_id_5143f914');
            $table->uuid('analysis_type_id')->index('analysis_type_id');
            $table->boolean('show_on_report')->nullable()->default(true);
            $table->timestamps();
            $table->uuid('procedure_worksheet_id')->nullable()->index('idx_analysis_elements_procedure_worksheet_id_0d343afd');
            $table->uuid('equipment_id')->nullable()->default(0)->index('equipment_id');
            $table->integer('method')->nullable()->index('method');
            $table->smallInteger('is_manual')->nullable()->default(0);
            $table->string('operator_id', 100)->nullable()->index('operator_id');
            $table->double('significant_figures')->nullable()->default(3);
            $table->double('lod')->nullable();
            $table->double('hod')->nullable();
            $table->integer('level')->nullable()->index('idx_analysis_elements_level_32902328');
            $table->integer('reporting_time')->nullable()->default(0);
            $table->integer('lab_section_id')->nullable()->index('lab_section_id');
            $table->boolean('remark_is_manual')->nullable()->default(false);
            $table->boolean('result_is_calculated')->default(false);
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->integer('ltm_method_id')->nullable();
            $table->boolean('recommend_remedies')->default(false);
            $table->uuid('remedy_header_id')->nullable()->index('analysis_elements_remedy_header_id_foreign');
            $table->unsignedBigInteger('formular_id')->nullable();
            $table->boolean('has_method_sequence')->default(false);
            $table->uuid('method_sequence_id')->nullable()->index('idx_analysis_elements_method_sequence_id_2b06e6a5');
            $table->foreign(['method_sequence_id'], 'fk_analysis_elements_method_sequence_id_a8e0950f')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['remedy_header_id'], 'fk_analysis_elements_remedy_header_id_ba5aa4bf')->references(['id'])->on('remedy_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analysis_type_id'], 'fk_analysis_elements_analysis_type_id_bba30d9d')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_analysis_elements_analyte_id_eec905de')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_analysis_elements_company_id_b4f06a92')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_analysis_elements_equipment_id_b3fd69bd')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['procedure_worksheet_id'], 'fk_analysis_elements_procedure_worksheet_id_c6c26d38')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('set null');












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
