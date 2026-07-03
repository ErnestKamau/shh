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
        if (Schema::hasTable('sample_headers')) {
            return;
        }
        Schema::create('sample_headers', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id')->nullable()->index()->comment('ID of the submission form instance that created this sample');
            $table->string('batch_code')->index('idx_sample_headers_batch_code_0299bbe8');
            $table->dateTime('receipt_date')->nullable();
            $table->dateTime('date_collected')->nullable();
            $table->uuid('crm_customer_id')->index('idx_sample_headers_crm_customer_id_12f599f3');
            $table->text('crm_unit_name');
            $table->uuid('sample_type_id')->index('idx_sample_headers_sample_type_id_68d352c3');
            $table->text('reference_number')->nullable();
            $table->string('status');
            $table->boolean('has_method_deviation')->default(false);
            $table->text('method_deviation_reason')->nullable();
            $table->boolean('sample_detail_processed')->default(false);
            $table->boolean('is_routine');
            $table->double('routine_frequency');
            $table->timestamps();
            $table->integer('is_amendment')->nullable()->default(1);
            $table->smallInteger('schedule_sent')->nullable()->default(0);
            $table->integer('sample_tracking_stage')->nullable();
            $table->text('description')->nullable();
            $table->text('document_number')->nullable();
            $table->text('importer_address')->nullable();
            $table->uuid('receiving_officer')->nullable();
            $table->uuid('sampling_officer')->nullable();
            $table->string('priority', 100)->default('Normal');
            $table->text('reason_for_submission')->nullable();
            $table->text('how_sample_was_obtained')->nullable();
            $table->uuid('specialist_analyst_id')->nullable()->index('idx_sample_headers_specialist_analyst_id_b2f076f2');
            $table->string('declared_commodity_code', 512)->nullable();
            $table->text('net_quantity_and_unit_of_quantity')->nullable();
            $table->text('sample_appearance_description')->nullable();
            $table->text('use_of_goods')->nullable();
            $table->string('kra_office_ref')->nullable();
            $table->string('kra_office_station')->nullable();
            $table->text('where_sample_was_obtained')->nullable();
            $table->text('declared_amount')->nullable();
            $table->decimal('final_declared_amount', 18, 0)->nullable();
            $table->text('radio_active_levels')->nullable();
            $table->date('date_expected')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('verify_user_id')->nullable();
            $table->uuid('approve_user_id')->nullable();
            $table->date('processing_date')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('email_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('isactive')->default(true);
            $table->text('ammendment_number')->nullable();
            $table->boolean('customer_paid')->default(false);
            $table->string('payment_reference_no')->nullable();
            $table->date('verification_email_date')->nullable();
            $table->date('approval_email_date')->nullable();
            $table->text('sampling_officer_name')->nullable();
            $table->text('receiving_officer_name')->nullable();
            $table->text('submit_by')->nullable();
            $table->text('batch_report_url')->nullable();
            $table->string('current_account_status')->default('');
            $table->uuid('quote_id')->nullable();
            $table->integer('user_agreement')->nullable();
            $table->boolean('in_ammendment_proccess')->default(false);
            $table->boolean('in_ammendment_process')->default(false);
            $table->boolean('begin_proccess')->nullable()->default(false);
            $table->boolean('begin_process')->nullable()->default(false);
            $table->integer('week')->nullable();
            $table->integer('year')->nullable();
            $table->string('batch_type', 500)->nullable();
            $table->boolean('import_sample')->nullable()->default(false);
            $table->boolean('packlist_proccessed')->nullable()->default(false);
            $table->boolean('require_mu')->nullable()->default(false);
            $table->boolean('is_exception')->nullable()->default(false);
            $table->string('payment_done_by', 500)->nullable();
            $table->boolean('sampled_by_company_personnel')->default(true);
            $table->date('in_lab_date')->nullable();
            $table->uuid('crm_unit_id')->nullable()->index('idx_sample_headers_crm_unit_id_7debc3b1');
            $table->uuid('company_sub_unit_id')->nullable()->index('idx_sample_headers_company_sub_unit_id_ff8c14ff');
            $table->integer('other_id')->nullable();
            $table->string('batch_scope')->nullable();
            $table->string('customer_survey')->nullable();
            $table->boolean('is_qc_batch')->nullable()->default(false);
            $table->uuid('qc_type_id')->nullable()->index('idx_sample_headers_qc_type_id_3530fbec');
            $table->uuid('qc_scheme_id')->nullable()->index('idx_sample_headers_qc_scheme_id_9b80a0eb');
            $table->boolean('repeat_batch_id')->nullable()->default(false);
            $table->integer('repeat_sample_id')->nullable();
            $table->string('quote_no', 100)->nullable();
            $table->boolean('lab_capable')->nullable()->default(false);
            $table->boolean('batch_subcontracted_client_approval')->nullable()->default(false);
            $table->boolean('can_be_subcontracted')->nullable();
            $table->boolean('client_instruction_clear')->nullable()->default(false);
            $table->text('batch_instructions')->nullable();
            $table->text('condition_quality_sample')->nullable();
            $table->dateTime('declaration_customer_approval_date')->nullable();
            $table->text('declaration_customer_signature')->nullable();
            $table->string('declaration_customer_contact_name')->nullable();
            $table->dateTime('declaration_customer_review_approval_date')->nullable();
            $table->text('invoice_amount')->nullable();
            $table->uuid('sampling_method_id')->nullable();
            $table->integer('days_of_analysis')->nullable();
            $table->dateTime('schedule_analysis_sent')->nullable();
            $table->date('report_verified_date')->nullable();
            $table->string('lab_section_ids', 100)->nullable();
            $table->string('c_focus_ids_clustered')->nullable();
            $table->text('cluster_amount')->nullable();
            $table->text('cluster_balance')->nullable();
            $table->string('cluster_vat', 100)->nullable();
            $table->text('cluster_amount_paid')->nullable();
            $table->uuid('crm_contact_id')->nullable()->index('idx_sample_headers_crm_contact_id_a3d90292');
            $table->integer('report_status')->nullable()->default(0)->index('idx_sample_headers_report_status_8b332fb8');
            $table->string('prelim_batch_status', 100)->nullable();
            $table->integer('prelim_report_status')->nullable()->default(0);
            $table->integer('schedule_analysis_sender')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->text('case_id')->nullable();
            $table->tinyInteger('has_kenas')->nullable()->default(1);
            $table->uuid('lab_id')->nullable()->index('idx_sample_headers_lab_id_97b696fe');
            $table->date('retention_date')->nullable();
            $table->text('batch_report_online_url')->nullable();
            $table->text('schedule_customer_email')->nullable();
            $table->boolean('ftp_not_sync')->nullable()->default(false);
            $table->text('excel_result_url')->nullable();
            $table->uuid('sample_header_staging_id')->nullable()->index('idx_sample_headers_sample_header_staging_id_fade9037');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_headers');
    }
};
