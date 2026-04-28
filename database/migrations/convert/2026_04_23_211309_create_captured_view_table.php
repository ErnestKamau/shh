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
            $table->uuid('sample_detail_id')->index('idx_captured_view_sample_detail_id_56c98150');
            $table->uuid('sample_header_id')->index('idx_captured_view_sample_header_id_d14abb6f');
            $table->uuid('analyte_id')->index('idx_captured_view_analyte_id_9b4b5e46');
            $table->string('analyte_code');
            $table->uuid('equipment_id')->nullable()->index('idx_captured_view_equipment_id_446bdd4f');
            $table->string('result', 100)->nullable();
            $table->uuid('user_id')->index('idx_captured_view_user_id_d37fc6b6');
            $table->timestamps();
            $table->uuid('analysis_type_id')->nullable()->index('idx_captured_view_analysis_type_id_1251cda3');
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
            $table->uuid('reporting_unit_id')->nullable()->index('idx_captured_view_reporting_unit_id_84b35aab');
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
