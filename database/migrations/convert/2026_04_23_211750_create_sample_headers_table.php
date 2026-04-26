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
        Schema::create('sample_headers', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_instance_id')->nullable()->index()->comment('ID of the submission form instance that created this sample');
            $table->string('batch_code')->index('batch_code');
            $table->dateTime('receipt_date')->nullable();
            $table->dateTime('date_collected')->nullable();
            $table->uuid('crm_customer_id')->index('crm_customer_id');
            $table->text('crm_unit_name');
            $table->uuid('sample_type_id')->index('sample_type_id');
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
            $table->uuid('specialist_analyst_id')->nullable()->index('specialist_analyst_id');
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
            $table->uuid('invoice_id')->default(0);
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
            $table->uuid('crm_unit_id')->nullable()->default(0)->index('crm_unit_id');
            $table->uuid('company_sub_unit_id')->nullable()->index('sample_headers_company_sub_unit_id_foreign');
            $table->integer('other_id')->nullable();
            $table->string('batch_scope')->nullable();
            $table->string('customer_survey')->nullable();
            $table->boolean('is_qc_batch')->nullable()->default(false);
            $table->uuid('qc_type_id')->nullable()->index('idx_sample_headers_qc_type_id_148d1f4f');
            $table->uuid('qc_scheme_id')->nullable()->index('idx_sample_headers_qc_scheme_id_60d40b23');
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
            $table->uuid('crm_contact_id')->nullable()->index('crm_contact_id');
            $table->integer('report_status')->nullable()->default(0)->index('report_status');
            $table->string('prelim_batch_status', 100)->nullable();
            $table->integer('prelim_report_status')->nullable()->default(0);
            $table->integer('schedule_analysis_sender')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->text('case_id')->nullable();
            $table->tinyInteger('has_kenas')->nullable()->default(1);
            $table->uuid('lab_id')->nullable()->index('idx_sample_headers_lab_id_97c6cb5f');
            $table->date('retention_date')->nullable();
            $table->text('batch_report_online_url')->nullable();
            $table->text('schedule_customer_email')->nullable();
            $table->boolean('ftp_not_sync')->nullable()->default(false);
            $table->text('excel_result_url')->nullable();
            $table->uuid('sample_header_staging_id')->nullable()->index('idx_sample_headers_sample_header_staging_id_25a9ac98');
            $table->foreign(['company_sub_unit_id'], 'fk_sample_headers_company_sub_unit_id_82288b8d')->references(['id'])->on('crm_company_sub_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_customer_id'], 'fk_sample_headers_crm_customer_id_3906f6bc')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lab_id'], 'fk_sample_headers_lab_id_04539a95')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['qc_scheme_id'], 'fk_sample_headers_qc_scheme_id_f28ed5af')->references(['id'])->on('qc_scheme')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['qc_type_id'], 'fk_sample_headers_qc_type_id_b96262aa')->references(['id'])->on('qc_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_staging_id'], 'fk_sample_headers_sample_header_staging_id_e7894d03')->references(['id'])->on('sample_header_staging')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_type_id'], 'fk_sample_headers_sample_type_id_37009c3a')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['submission_form_instance_id'], 'fk_sample_headers_submission_form_instance_id_0102931b')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('set null');
            $table->foreign('receiving_officer')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sampling_officer')->references('id')->on('users')->onDelete('set null');
            $table->foreign('specialist_analyst_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verify_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approve_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('customer_invoice')->onDelete('set null');
            $table->foreign('quote_id')->references('id')->on('quotation_headers')->onDelete('set null');
            $table->foreign('crm_unit_id')->references('id')->on('crm_company_units')->onDelete('set null');
            $table->foreign('qc_scheme_id')->references('id')->on('qc_scheme')->onDelete('set null');
            $table->foreign('qc_type_id')->references('id')->on('qc_types')->onDelete('set null');
            $table->foreign('sampling_method_id')->references('id')->on('analysis_methods')->onDelete('set null');
            $table->foreign('crm_contact_id')->references('id')->on('crm_customer_contacts')->onDelete('set null');







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
