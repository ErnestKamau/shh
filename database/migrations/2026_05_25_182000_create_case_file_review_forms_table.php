<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_file_review_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->nullable();
            
            // SAMPLE INFORMATION
            $table->string('lab_no')->nullable();
            $table->string('file_no')->nullable();
            $table->date('date_in')->nullable();
            $table->string('client')->nullable();
            $table->integer('no_of_samples')->nullable();
            $table->boolean('sample_condition_sealed')->default(false);
            $table->boolean('sample_condition_labelled')->default(false);
            $table->string('sample_condition_remark')->nullable();
            $table->string('name_of_analyst')->nullable();
            $table->string('analyst_signature')->nullable();
            
            // SAMPLE SCREENING
            $table->date('screening_date')->nullable();
            $table->string('screening_method')->nullable();
            $table->boolean('screening_sample_type_blood')->default(false);
            $table->boolean('screening_sample_type_object_with_blood')->default(false);
            $table->boolean('screening_sample_type_semen')->default(false);
            $table->boolean('screening_sample_type_object_with_semen')->default(false);
            $table->string('screening_sample_type_others')->nullable();
            $table->string('screening_results_1')->nullable(); // Positive, Negative, N/A
            $table->string('screening_results_2')->nullable(); // Positive, Negative, N/A
            
            // SAMPLE EXTRACTION
            $table->date('extraction_date')->nullable();
            $table->boolean('extraction_method_chelex')->default(false);
            $table->boolean('extraction_method_prepfiler')->default(false);
            $table->string('extraction_method_other')->nullable();
            
            // QUANTIFICATION
            $table->date('quantification_date')->nullable();
            $table->boolean('quantification_no_of_cycles_40')->default(false);
            $table->string('quantification_remarks')->nullable();
            $table->boolean('quantification_kit_used_quant_trio')->default(false);
            
            // POLYMERASE CHAIN REACTION (PCR)
            $table->date('pcr_amplification_date')->nullable();
            $table->boolean('pcr_no_of_cycles_28')->default(false);
            $table->boolean('pcr_no_of_cycles_29')->default(false);
            $table->boolean('pcr_no_of_cycles_30')->default(false);
            $table->boolean('pcr_no_of_cycles_32')->default(false);
            $table->string('pcr_remarks')->nullable();
            $table->boolean('pcr_kit_used_identifiler_plus')->default(false);
            $table->boolean('pcr_kit_used_globalfiler')->default(false);
            $table->boolean('pcr_kit_used_yfiler_plus')->default(false);
            
            // INJECTION & INTERPRETATION
            $table->date('injection_date')->nullable();
            $table->string('injection_run_id')->nullable();
            $table->boolean('injection_instrument_3500')->default(false);
            $table->string('injection_result_positive')->nullable(); // Pass, Fail
            $table->string('injection_result_negative')->nullable(); // Pass, Fail
            $table->string('injection_result_ladder')->nullable(); // Pass, Fail
            $table->string('injection_result_blank')->nullable(); // Pass, Fail
            $table->string('injection_run')->nullable(); // Pass, Fail
            
            // REPORTING
            $table->date('reporting_draft_report_date')->nullable();
            $table->boolean('reporting_reviewed')->default(false);
            $table->boolean('reporting_corrected')->default(false);
            $table->boolean('reporting_attachment_real_time_data')->default(false);
            $table->boolean('reporting_attachment_converge')->default(false);
            $table->boolean('reporting_attachment_statistical_analysis')->default(false);
            $table->text('reporting_remarks')->nullable();
            
            // AUTHENTICATION
            $table->text('reviewer_comments')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_signature')->nullable();
            $table->date('reviewer_date')->nullable();
            
            // MANAGER'S REVIEW & VERIFICATION
            $table->boolean('manager_review_technical')->default(false);
            $table->boolean('manager_review_administrative')->default(false);
            $table->boolean('manager_comments_verified')->default(false);
            $table->boolean('manager_comments_not_verified')->default(false);
            $table->date('manager_date')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_signature')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('case_file_review_forms');
    }
};
