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
        Schema::create('captured_view', function (Blueprint $table) {
            $table->bigInteger('id')->default(0);
            $table->string('sample_detail_code');
            $table->uuid('sample_detail_id')->index('idx_captured_view_sample_detail_id_2debf9ab');
            $table->uuid('sample_header_id')->index('idx_captured_view_sample_header_id_a1e2b652');
            $table->uuid('analyte_id')->index('idx_captured_view_analyte_id_a5e49ac7');
            $table->string('analyte_code');
            $table->uuid('equipment_id')->nullable()->default(0)->index('idx_captured_view_equipment_id_245c9330');
            $table->string('result', 100)->nullable();
            $table->uuid('user_id')->index('idx_captured_view_user_id_d5c7b193');
            $table->timestamps();
            $table->uuid('analysis_type_id')->nullable()->index('idx_captured_view_analysis_type_id_f14dd44b');
            $table->integer('operator_id')->nullable();
            $table->integer('method_id')->nullable();
            $table->dateTime('machine_update_date')->nullable();
            $table->boolean('analyte_status_contracted')->default(false);
            $table->string('remark', 100)->nullable();
            $table->boolean('analyte_accredited')->nullable()->default(false);
            $table->integer('main_standard_id')->nullable()->default(0);
            $table->integer('secondary_standard_id')->nullable();
            $table->string('main_value', 500)->nullable();
            $table->string('secondary_value', 500)->nullable();
            $table->integer('analysis_type_order')->default(0);
            $table->integer('parameters_order')->default(0);
            $table->string('remark_colour', 300)->nullable();
            $table->string('result_reporting_symbol', 100)->nullable();
            $table->integer('repeat_captured_id')->nullable();
            $table->integer('lab_section_id')->nullable();
            $table->boolean('remark_is_manual')->nullable()->default(false);
            $table->uuid('reporting_unit_id')->nullable()->index('idx_captured_view_reporting_unit_id_44da80f0');
            $table->string('measure_uncertanity', 100)->nullable();
            $table->boolean('is_pesticide')->nullable()->default(false);
            $table->string('third_value')->nullable();
            $table->string('sec_remark', 100)->nullable();
            $table->string('third_remark', 100)->nullable();
            $table->integer('third_standard_id')->nullable();
            $table->integer('ltm_method_id')->nullable();
            $table->string('scienctific_result')->nullable();
            $table->string('superscript_number', 100)->nullable();
            $table->boolean('superscript_negative')->nullable()->default(false);
            $table->string('supercsript_base', 100)->nullable();
            $table->string('batch_code');
            $table->string('crm_customer_name');
            $table->string('sample_type_name');
            $table->string('analysis_type_name');
            $table->string('method_name')->nullable();
            $table->string('main_standard_name')->nullable();
            $table->foreign(['analysis_type_id'], 'fk_captured_view_analysis_type_id_96611ddd')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analyte_id'], 'fk_captured_view_analyte_id_708f95df')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_captured_view_equipment_id_582990f4')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['reporting_unit_id'], 'fk_captured_view_reporting_unit_id_e6c3b7e4')->references(['id'])->on('reporting_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_captured_view_sample_detail_id_25f57e7d')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_captured_view_sample_header_id_2457014c')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_captured_view_user_id_5b454118')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');














        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_view');
    }
};
