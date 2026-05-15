<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->safeTable('ai_action_logs', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_ai_action_logs_user_id_e475dafb')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('ai_analytics_logs', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_ai_analytics_logs_user_id_2b7ea81e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('ai_chat_attachments', function (Blueprint $table) {
            $table->foreign(['ai_conversation_id'], 'fk_ai_chat_att_convo')->references(['id'])->on('ai_conversations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ai_message_id'], 'fk_ai_chat_att_msg')->references(['id'])->on('ai_messages')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('ai_conversations', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_ai_conversations_user_id_99a7c1e5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ai_messages', function (Blueprint $table) {
            $table->foreign(['ai_conversation_id'], 'fk_ai_messages_ai_conversation_id_c3ddd1d0')->references(['id'])->on('ai_conversations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_message_id'], 'fk_ai_messages_parent_message_id_33553798')->references(['id'])->on('ai_messages')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('analysis_elements', function (Blueprint $table) {
            $table->foreign(['method_sequence_id'], 'fk_analysis_elements_method_sequence_id_a8e0950f')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['remedy_header_id'], 'fk_analysis_elements_remedy_header_id_ba5aa4bf')->references(['id'])->on('remedy_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analysis_type_id'], 'fk_analysis_elements_analysis_type_id_bba30d9d')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_analysis_elements_analyte_id_eec905de')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_analysis_elements_company_id_b4f06a92')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_analysis_elements_equipment_id_b3fd69bd')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['procedure_worksheet_id'], 'fk_analysis_elements_procedure_worksheet_id_c6c26d38')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('analysis_guides', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_analysis_guides_analysis_type_id_e15f1068')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_analysis_guides_analyte_id_cb7db970')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_id'], 'fk_analysis_guides_standard_id_b2c82c24')->references(['id'])->on('standards')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['standard_value_id'], 'fk_analysis_guides_standard_value_id_a7efc6ec')->references(['id'])->on('standard_values')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('analysis_method_elements', function (Blueprint $table) {
            $table->foreign(['analysis_method_id'], 'fk_analysis_method_elements_analysis_method_id_9a29a709')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_analysis_method_elements_analyte_id_437636e5')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_analysis_method_elements_company_id_85f7ffa2')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('analysis_methods', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_analysis_methods_company_id_800d5758')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_analysis_methods_sample_header_id_1ab3181c')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('analysis_type_invoicable_item', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_analysis_type_invoicable_item_analysis_type_id_83c7af8b')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['invoicable_item_id'], 'fk_analysis_type_invoicable_item_invoicable_item_id_b9ecb23e')->references(['id'])->on('invoicable_items')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('analysis_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_analysis_types_company_id_1ce9c588')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lab_id'], 'fk_analysis_types_lab_id_fd913615')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_analysis_types_procedure_worksheet_id_0710fe93')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_type_id'], 'fk_analysis_types_sample_type_id_b9228794')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('analytes', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_analytes_company_id_d5cb8dcd')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_analytes_equipment_id_85658cec')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('approvals', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_approvals_inventory_location_id_f6594aa9')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['role_id'], 'fk_approvals_role_id_a3ff9e9b')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('attachment_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_attachment_types_company_id_b814397a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_attachments', function (Blueprint $table) {
            $table->foreign(['attachment_type_id'], 'fk_audit_attachments_attachment_type_id_1ae5dc97')->references(['id'])->on('attachment_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_audit_attachments_company_id_a2bf5550')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_checklist_audit', function (Blueprint $table) {
            $table->foreign(['audit_checklist_id'], 'fk_audit_checklist_audit_audit_checklist_id_ca5c2996')->references(['id'])->on('audit_checklists')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['audit_id'], 'fk_audit_checklist_audit_audit_id_5ad9b77a')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_checklist_items', function (Blueprint $table) {
            $table->foreign(['audit_checklist_id'], 'fk_audit_checklist_items_audit_checklist_id_35b1b989')->references(['id'])->on('audit_checklists')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_checklists', function (Blueprint $table) {
            $table->foreign(['audit_type_id'], 'fk_audit_checklists_audit_type_id_78022d85')->references(['id'])->on('audit_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_audit_checklists_company_id_52b583e1')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_audit_checklists_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('audit_email_templates', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_audit_email_templates_company_id_243ca6fd')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_module_findings', function (Blueprint $table) {
            $table->foreign(['audit_id'], 'fk_audit_module_findings_audit_id_c06ca483')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['finding_category_id'], 'fk_audit_module_findings_finding_category_id_6d005db6')->references(['id'])->on('finding_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_level_id'], 'fk_audit_module_findings_risk_level_id_e0f3c164')->references(['id'])->on('risk_levels')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_audit_module_findings_status_id_89f8ae96')->references(['id'])->on('finding_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_audit_module_findings_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('audit_notification_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_audit_notification_types_company_id_316b9c3d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_notifications', function (Blueprint $table) {
            $table->foreign(['notification_type_id'], 'fk_audit_notifications_notification_type_id_d6765bc3')->references(['id'])->on('audit_notification_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_audit_notifications_company_id_45aa558e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_audit_statuses_company_id_e142bc09')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_team_members', function (Blueprint $table) {
            $table->foreign(['audit_id'], 'fk_audit_team_members_audit_id_e79981bc')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'fk_audit_team_members_role_id_8db54710')->references(['id'])->on('audit_team_roles')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_audit_team_members_user_id_9f975f5e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_team_roles', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_audit_team_roles_company_id_8479fdfe')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_audit_types_company_id_87624143')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audit_workflow_approvers', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_audit_workflow_approvers_user_id_e43ca3f6')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_audit_workflow_approvers_company_id_44b60215')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('audits', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_audits_user_id_19ab19d2')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('batch_ammendments', function (Blueprint $table) {
            $table->foreign(['batch_id'], 'fk_batch_ammendments_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by_id'], 'fk_batch_ammendments_created_by_id')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('batch_approval_checklist', function (Blueprint $table) {
            $table->foreign(['approval_id'], 'fk_batch_approval_checklist_approval_id_e81b8627')->references(['id'])->on('approvals')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('batch_attachment_annotations', function (Blueprint $table) {
            $table->foreign(['batch_attachment_id'], 'fk_batch_attachment_annotations_batch_attachment_id_15e491e9')->references(['id'])->on('batch_attachments')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('batch_attachments', function (Blueprint $table) {
            $table->foreign(['batch_id'], 'fk_batch_attachments_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('batch_comments', function (Blueprint $table) {
            $table->foreign(['sample_header_id'], 'fk_batch_comments_sample_header_id_6ca4c5ac')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_batch_comments_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('batch_labsection_approval', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_batch_labsection_approval_user_id_51dabab5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['batch_id'], 'fk_batch_labsection_approval_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('batch_notifications', function (Blueprint $table) {
            $table->foreign(['batch_id'], 'fk_batch_notifications_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_batch_notifications_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('batch_sequences', function (Blueprint $table) {
            $table->foreign(['submission_form_instance_id'], 'batch_seq_instance_id_foreign')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('calendar_events', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_calendar_events_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('calendarevents_notifications', function (Blueprint $table) {
            $table->foreign(['calendar_event_id'], 'fk_calendarevents_notifications_calendar_event_id_fc31581d')->references(['id'])->on('calendar_events')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('capa_action_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_capa_action_types_company_id_aa4c2386')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('capa_categories', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_capa_categories_company_id_7ca826ae')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('capa_priorities', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_capa_priorities_company_id_757b9b17')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('capa_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_capa_statuses_company_id_83e5513e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('captured_procedure_config_values', function (Blueprint $table) {
            $table->foreign(['captured_result_id'], 'fk_captured_procedure_config_values_captured_result_id_157a0db9')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_config_field_id'], 'fk_captured_procedure_config_values_procedure_config_f_e9d35992')->references(['id'])->on('procedure_config_fields')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_captured_procedure_config_values_procedure_workshee_3a3e78c6')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('captured_procedure_values', function (Blueprint $table) {
            $table->foreign(['captured_result_id'], 'fk_captured_procedure_values_captured_result_id_0b37e1e1')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_step_id'], 'fk_captured_procedure_values_procedure_worksheet_step_a989f116')->references(['id'])->on('procedure_worksheet_steps')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('captured_results', function (Blueprint $table) {
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
            $table->foreign('method_id')->references('id')->on('analysis_methods')->onDelete('set null');
            $table->foreign('main_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('secondary_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('lab_section_id')->references('id')->on('sample_analysis_stages')->onDelete('set null');
            $table->foreign('third_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('ltm_method_id')->references('id')->on('analysis_methods')->onDelete('set null');
            $table->foreign('formular_id')->references('id')->on('formulas')->onDelete('set null');
        });

        $this->safeTable('captured_view', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_captured_view_analysis_type_id_96611ddd')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analyte_id'], 'fk_captured_view_analyte_id_708f95df')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_captured_view_equipment_id_582990f4')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['reporting_unit_id'], 'fk_captured_view_reporting_unit_id_e6c3b7e4')->references(['id'])->on('reporting_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_captured_view_sample_detail_id_25f57e7d')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_captured_view_sample_header_id_2457014c')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_captured_view_user_id_5b454118')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('certificate_template_element_holders', function (Blueprint $table) {
            $table->foreign(['parent_holder_id'], 'fk_certificate_template_element_holders_parent_holder_730437f6')->references(['id'])->on('certificate_template_element_holders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['certificate_template_section_id'], 'ct_elem_holders_section_fk')->references(['id'])->on('certificate_template_sections')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('certificate_template_elements', function (Blueprint $table) {
            $table->foreign(['certificate_template_section_id'], 'ct_elements_section_fk')->references(['id'])->on('certificate_template_sections')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['certificate_template_element_holder_id'], 'fk_certificate_template_elements_certificate_template_dfc97abd')->references(['id'])->on('certificate_template_element_holders')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('certificate_template_reports', function (Blueprint $table) {
            $table->foreign(['generated_by'], 'ct_reports_generated_by_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['submission_form_instance_id'], 'ct_reports_instance_fk')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['certificate_template_id'], 'ct_reports_template_fk')->references(['id'])->on('certificate_templates')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('certificate_template_sections', function (Blueprint $table) {
            $table->foreign(['parent_section_id'], 'ct_sections_parent_fk')->references(['id'])->on('certificate_template_sections')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['certificate_template_id'], 'ct_sections_template_fk')->references(['id'])->on('certificate_templates')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('certificate_templates', function (Blueprint $table) {
            $table->foreign(['created_by'], 'ct_templates_created_by_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['submission_form_id'], 'ct_templates_submission_form_fk')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('chain_of_custodies', function (Blueprint $table) {
            $table->foreign(['sample_header_id'], 'fk_chain_of_custodies_sample_header_id_addc66de')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('chain_of_custody_complaints', function (Blueprint $table) {
            $table->foreign(['complaint_id'], 'fk_chain_of_custody_complaints_complaint_id_781161b7')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('chat_message', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_chat_message_company_id_b892717e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['conversation_id'], 'fk_chat_message_conversation_id_2f3ae54c')->references(['id'])->on('conversation')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('companies', function (Blueprint $table) {
            $table->foreign(['country_id'], 'fk_companies_country_id_34dce04f')->references(['id'])->on('countries')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('company_products', function (Blueprint $table) {
            $table->foreign(['crm_company_unit_id'], 'fk_company_products_crm_company_unit_id_34fa1d2f')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('complaintattachments', function (Blueprint $table) {
            $table->foreign(['complaint_id'], 'fk_complaintattachments_complaint_id_4cebbd95')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('complaintnotes', function (Blueprint $table) {
            $table->foreign(['complaint_id'], 'fk_complaintnotes_complaint_id_204e6b5b')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('complaints', function (Blueprint $table) {
            $table->foreign(['closed_by'], 'fk_complaints_closed_by_88d82921')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['escalated_from_user_id'], 'fk_complaints_escalated_from_user_id_b3bcc4ed')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['escalated_to_user_id'], 'fk_complaints_escalated_to_user_id_71f14b27')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['intake_approved_by'], 'fk_complaints_intake_approved_by_cf863675')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ticket_category_id'], 'fk_complaints_ticket_category_id_6c44710b')->references(['id'])->on('ticket_categories')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('complaintsresolutions', function (Blueprint $table) {
            $table->foreign(['resolved_by_user_id'], 'fk_complaintsresolutions_resolved_by_user_id_3d6a7e9b')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['complaint_id'], 'fk_complaintsresolutions_complaint_id_9de8d7d2')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('compliance_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_compliance_statuses_company_id_b0216bae')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('conversation', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_conversation_company_id_cd133da9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('corrective_actions', function (Blueprint $table) {
            $table->foreign(['action_type_id'], 'fk_corrective_actions_action_type_id_ec968186')->references(['id'])->on('capa_action_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['capa_category_id'], 'fk_corrective_actions_capa_category_id_56b8c0c6')->references(['id'])->on('capa_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['non_conformance_id'], 'fk_corrective_actions_non_conformance_id_b8706d63')->references(['id'])->on('non_conformances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['priority_id'], 'fk_corrective_actions_priority_id_d17bed10')->references(['id'])->on('capa_priorities')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_corrective_actions_status_id_8e89b6b0')->references(['id'])->on('capa_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_corrective_actions_company_id_9d86642a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_corrective_actions_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        if (Schema::hasTable('crm_areas') && Schema::hasTable('users')) {
            $this->safeTable('crm_areas', function (Blueprint $table) {
                $table->foreign(['created_by'], 'fk_crm_areas_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            });
        }

        $this->safeTable('crm_company_sections', function (Blueprint $table) {
            $table->foreign(['crm_customer_id'], 'fk_crm_company_sections_crm_customer_id_81f517eb')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_crm_company_sections_company_id_6771526d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('crm_company_sub_units', function (Blueprint $table) {
            $table->foreign(['crm_company_unit_id'], 'fk_crm_company_sub_units_crm_company_unit_id_b912448f')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_crm_company_sub_units_crm_customer_id_fe5a66b5')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('crm_company_units', function (Blueprint $table) {
            $table->foreign(['crm_company_section_id'], 'fk_crm_company_units_crm_company_section_id_51fb10eb')->references(['id'])->on('crm_company_sections')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_crm_company_units_company_id_07c30929')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_crm_company_units_crm_customer_id_72d7152d')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('crm_customer_contacts', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_crm_customer_contacts_company_id_29b1c7dc')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_crm_customer_contacts_crm_customer_id_5afc7ba0')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_company_unit_id'], 'fk_crm_customer_contacts_crm_company_unit_id')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('crm_customers', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_crm_customers_company_id_6e16447d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['country_id'], 'fk_crm_customers_country_id_2e574f72')->references(['id'])->on('countries')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['currency_id'], 'fk_crm_customers_currency_id_83bed6d4')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_crm_customers_lab_id_7bbf6b5a')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['zoho_customer_id'], 'fk_crm_customers_zoho_customer_id_bbf97fa2')->references(['id'])->on('zoho_customers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('crm_feedback_ratings', function (Blueprint $table) {
            $table->foreign(['customer_feedback_id'], 'fk_crm_feedback_ratings_customer_feedback_id_07e530aa')->references(['id'])->on('customerfeedbacks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['evaluation_metric_id'], 'fk_crm_feedback_ratings_evaluation_metric_id_1a3865e9')->references(['id'])->on('crm_evaluation_metrics')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('crm_report_info_columns', function (Blueprint $table) {
            $table->foreign(['crm_customer_id'], 'fk_crm_report_info_columns_crm_customer_id_edc2a5dc')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
        });

        if (Schema::hasTable('crm_sample_points') && Schema::hasTable('users')) {
            $this->safeTable('crm_sample_points', function (Blueprint $table) {
                $table->foreign(['created_by'], 'fk_crm_sample_points_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            });
        }

        $this->safeTable('currency_conversions', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_currency_conversions_inventory_location_id_b8466137')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('custom_field_category_customers', function (Blueprint $table) {
            $table->foreign(['crm_customer_id'], 'fk_custom_field_category_customers_crm_customer_id_0f1f11c5')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('customer_invoice', function (Blueprint $table) {
            $table->foreign(['currency_id'], 'fk_customer_invoice_currency_id_0e6e5a88')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pricelist_id'], 'fk_customer_invoice_pricelist_id_73d1b366')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['zoho_customer_id'], 'fk_customer_invoice_zoho_customer_id_d1589ead')->references(['id'])->on('zoho_customers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('customer_submission_form_columns', function (Blueprint $table) {
            $table->foreign(['crm_customer_id'], 'fk_customer_submission_form_columns_crm_customer_id_8391a076')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('customerfeedbacks', function (Blueprint $table) {
            $table->foreign(['contact_id'], 'fk_customerfeedbacks_contact_id_0b4f7503')->references(['id'])->on('crm_customer_contacts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['customer_id'], 'fk_customerfeedbacks_customer_id_8001c3c9')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('customerqualifications', function (Blueprint $table) {
            $table->foreign(['qualification_id'], 'fk_customerqualifications_qualification_id_89f70621')->references(['id'])->on('qualifications')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('directorates', function (Blueprint $table) {
            $table->foreign(['head_id'], 'fk_directorates_head_id_1092a617')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['zone_id'], 'fk_directorates_zone_id_b8b22de7')->references(['id'])->on('zones')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('document_amendments', function (Blueprint $table) {
            $table->foreign(['amended_by'], 'fk_document_amendments_amended_by_16756694')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['approved_by'], 'fk_document_amendments_approved_by_325ebda9')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['authorized_by'], 'fk_document_amendments_authorized_by_70873aab')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['document_id'], 'fk_document_amendments_document_id_12f208c4')->references(['id'])->on('documents')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['requested_by'], 'fk_document_amendments_requested_by_92236343')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('document_approval_workflow_steps', function (Blueprint $table) {
            $table->foreign(['workflow_id'], 'fk_document_approval_workflow_steps_workflow_id_3b8db626')->references(['id'])->on('document_approval_workflows')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('document_approval_workflows', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_document_approval_workflows_created_by_77cf15e3')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['document_type_id'], 'fk_document_approval_workflows_document_type_id_c5f3d142')->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('document_audit_logs', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_document_audit_logs_user_id_e5f1505c')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('document_expiry_notification_settings', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_document_expiry_notification_settings_user_id_9c49d225')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('document_notifications', function (Blueprint $table) {
            $table->foreign(['document_id'], 'fk_document_notifications_document_id_5687ddfe')->references(['id'])->on('documents')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_document_notifications_user_id_c868cf66')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('document_permissions', function (Blueprint $table) {
            $table->foreign(['granted_by'], 'fk_document_permissions_granted_by_c7efa302')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('document_types', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_document_types_created_by_a1df2b1f')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'], 'fk_document_types_parent_id_f5ed952b')->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('document_versions', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_document_versions_created_by_705fce77')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['document_id'], 'fk_document_versions_document_id_02518f89')->references(['id'])->on('documents')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('documents', function (Blueprint $table) {
            $table->foreign(['approved_by'], 'fk_documents_approved_by_087cc358')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['archived_by'], 'fk_documents_archived_by_6999a458')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_documents_created_by_ba25b021')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['document_type_id'], 'fk_documents_document_type_id_5c10dc63')->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['owner_id'], 'fk_documents_owner_id_a7abd97d')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('entity_approvals', function (Blueprint $table) {
            $table->foreign(['approval_id'], 'fk_entity_approvals_approval_id_cc2f5119')->references(['id'])->on('approvals')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_location_id'], 'fk_entity_approvals_inventory_location_id_ae45f704')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_entity_approvals_user_id_2217f2a2')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('entity_attachments', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_entity_attachments_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('entity_notes', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_entity_notes_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('equipment', function (Blueprint $table) {
            $table->foreign(['asset_location_id'], 'fk_equipment_asset_location_id_c89b91e1')->references(['id'])->on('asset_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['asset_type_id'], 'fk_equipment_asset_type_id_20cb530a')->references(['id'])->on('asset_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_equipment_company_id_ebb721d6')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_item_id'], 'fk_equipment_inventory_item_id_3b735850')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_equipment_lab_id_ff873d8e')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('equipment_attachments', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_equipment_attachments_equipment_id_a8d18663')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_daily_log_entries', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_equipment_daily_log_entries_equipment_id_97932801')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_equipment_daily_log_entries_company_id_29fa31ca')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            $table->foreign(['workflow_id'], 'fk_equipment_disposal_approval_workflow_steps_workflow_1b1d9c8b')->references(['id'])->on('equipment_disposal_approval_workflows')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_disposal_approval_workflows', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_equipment_disposal_approval_workflows_created_by_267693a0')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_type_id'], 'fk_equipment_disposal_approval_workflows_equipment_typ_9ddba46a')->references(['id'])->on('asset_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['location_id'], 'fk_equipment_disposal_approval_workflows_location_id_944a1db3')->references(['id'])->on('asset_locations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_equipment_disposal_approval_workflows_company_id_3f4e6918')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_disposal_approvals', function (Blueprint $table) {
            $table->foreign(['approver_id'], 'fk_equipment_disposal_approvals_approver_id_6bfb8073')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['disposal_id'], 'fk_equipment_disposal_approvals_disposal_id_601a65ca')->references(['id'])->on('equipment_disposals')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_disposal_audit_logs', function (Blueprint $table) {
            $table->foreign(['disposal_id'], 'fk_equipment_disposal_audit_logs_disposal_id_f88da1a2')->references(['id'])->on('equipment_disposals')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_equipment_disposal_audit_logs_user_id_fd800541')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('equipment_disposal_files', function (Blueprint $table) {
            $table->foreign(['disposal_id'], 'fk_equipment_disposal_files_disposal_id_b9a6df72')->references(['id'])->on('equipment_disposals')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['uploaded_by'], 'fk_equipment_disposal_files_uploaded_by_bb963d2e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });

        $this->safeTable('equipment_disposals', function (Blueprint $table) {
            $table->foreign(['decommissioned_by'], 'fk_equipment_disposals_decommissioned_by_03cd3051')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_equipment_disposals_equipment_id_2979efd1')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['evaluation_id'], 'fk_equipment_disposals_evaluation_id_0f62680e')->references(['id'])->on('equipment_evaluations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['executed_by'], 'fk_equipment_disposals_executed_by_d61901f5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['requested_by'], 'fk_equipment_disposals_requested_by_9af021ec')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['witness_id'], 'fk_equipment_disposals_witness_id_410c4b6a')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_equipment_disposals_company_id_9087049c')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_evaluations', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_equipment_evaluations_equipment_id_3a40c184')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['evaluated_by'], 'fk_equipment_evaluations_evaluated_by_63e6f54d')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['company_id'], 'fk_equipment_evaluations_company_id_f567fb86')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_notification', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_equipment_notification_equipment_id_f3ba4755')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_operators', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_equipment_operators_equipment_id_2dbbbdb8')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_equipment_operators_user_id_2b6edac5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('equipment_usage', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_equipment_usage_equipment_id_a560c5d6')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('feedback_requests', function (Blueprint $table) {
            $table->foreign(['contact_id'], 'fk_feedback_requests_contact_id_5227f2e9')->references(['id'])->on('crm_customer_contacts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['feedback_id'], 'fk_feedback_requests_feedback_id_1799cc3e')->references(['id'])->on('customerfeedbacks')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_feedback_requests_company_id_4a70275d')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('finding_categories', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_finding_categories_company_id_c33bfaf4')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('finding_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_finding_statuses_company_id_45a0f0b9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('form_fields', function (Blueprint $table) {
            $table->foreign(['form_template_id'], 'fk_form_fields_form_template_id_fb6faee5')->references(['id'])->on('form_templates')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_field_id'], 'fk_form_fields_parent_field_id_739ee42c')->references(['id'])->on('form_fields')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_id'], 'fk_form_fields_parent_id_729e8c68')->references(['id'])->on('form_fields')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('form_template_variables', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_form_template_variables_created_by_6ba5ff42')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['form_template_id'], 'fk_form_template_variables_form_template_id_bea66487')->references(['id'])->on('form_templates')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('form_templates', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_form_templates_created_by_ae048d27')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('formula_mandatory_fields', function (Blueprint $table) {
            $table->foreign(['formula_version_id'], 'fk_formula_mandatory_fields_formula_version_id_e43a55d8')->references(['id'])->on('formula_versions')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('formula_steps', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_formula_steps_analyte_id_0eee2df8')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['formula_version_id'], 'fk_formula_steps_formula_version_id_1fd80d95')->references(['id'])->on('formula_versions')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('formula_versions', function (Blueprint $table) {
            $table->foreign(['approved_by'], 'fk_formula_versions_approved_by_018dfed1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_formula_versions_created_by_d020a4f5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_id'], 'fk_formula_versions_formula_id_7c1ff219')->references(['id'])->on('formulas')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('general_requisition_request_items', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_general_requisition_request_items_supplier_id_ff6f4027')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('general_requisition_supplier_quotes', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_general_requisition_supplier_quotes_supplier_id_df67df4f')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('general_requistion_requests', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_general_requistion_requests_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('import_lab_results', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_import_lab_results_analyte_id_1d31291e')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['batch_id'], 'fk_import_lab_results_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inspection_detail', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_inspection_detail_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('inventory_categories', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_inventory_categories_company_id_13e5b591')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_inventory_categories_inventory_location_id_ecf4006b')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('inventory_departments', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_inventory_departments_company_id_a20c72c8')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('inventory_item_notes', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'], 'fk_inventory_item_notes_inventory_item_id_eee48252')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_items', function (Blueprint $table) {
            $table->foreign(['inventory_category_id'], 'fk_inventory_items_inventory_category_id_a8d08c22')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_department_id'], 'fk_inventory_items_inventory_department_id_ebcd77ec')->references(['id'])->on('inventory_departments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_location_id'], 'fk_inventory_items_inventory_location_id_0b81cb5b')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_store_id'], 'fk_inventory_items_inventory_store_id_a90b6395')->references(['id'])->on('inventory_stores')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_store_slot_id'], 'fk_inventory_items_inventory_store_slot_id_a009a457')->references(['id'])->on('inventory_store_slots')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_sub_category_id'], 'fk_inventory_items_inventory_sub_category_id_08d9e9b3')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['item_brand_id'], 'fk_inventory_items_item_brand_id_2f67ff27')->references(['id'])->on('item_brands')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_inventory_items_supplier_id_9e0aa447')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_inventory_items_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('inventory_location_users', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_inventory_location_users_inventory_location_id_ef0dcd15')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_inventory_location_users_user_id_b839635a')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_locations', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_inventory_locations_company_id_81a0dcd9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_inventory_locations_inventory_location_id_e75cbfc6')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_order_item_to_inventory_items', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'], 'fk_inventory_order_item_to_inventory_items_inventory_i_cbf53323')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_order_id'], 'fk_inventory_order_item_to_inventory_items_inventory_o_a57f4645')->references(['id'])->on('inventory_orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_order_item_id'], 'fk_inventory_order_item_to_inventory_items_inventory_o_6d755b0d')->references(['id'])->on('inventory_order_items')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_order_items', function (Blueprint $table) {
            $table->foreign(['inventory_category_id'], 'fk_inventory_order_items_inventory_category_id_e792e9b3')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_order_id'], 'fk_inventory_order_items_inventory_order_id_78b82c90')->references(['id'])->on('inventory_orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_sub_category_id'], 'fk_inventory_order_items_inventory_sub_category_id_9a6fd5d4')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_orders', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_inventory_orders_company_id_a8270aff')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_inventory_orders_supplier_id_f38d7d14')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_inventory_orders_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('inventory_store_contacts', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_inventory_store_contacts_user_id_fa496f66')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_store_slot_contents', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'], 'fk_inventory_store_slot_contents_inventory_item_id_20182cef')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_store_slot_id'], 'fk_inventory_store_slot_contents_inventory_store_slot_51af8236')->references(['id'])->on('inventory_store_slots')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_sub_category_id'], 'fk_inventory_store_slot_contents_inventory_sub_categor_fd607d60')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('inventory_store_slots', function (Blueprint $table) {
            $table->foreign(['inventory_store_id'], 'fk_inventory_store_slots_inventory_store_id_d7fdda03')->references(['id'])->on('inventory_stores')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_stores', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_inventory_stores_company_id_7d77fd31')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_location_id'], 'fk_inventory_stores_inventory_location_id_73bf547f')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_sub_categories', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_inventory_sub_categories_company_id_e88e1013')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_category_id'], 'fk_inventory_sub_categories_inventory_category_id_9c931fea')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('inventory_supplier_ratings', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'], 'fk_inventory_supplier_ratings_inventory_item_id_686c132c')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_inventory_supplier_ratings_supplier_id_f72c0d49')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('invoicable_items', function (Blueprint $table) {
            $table->foreign(['currency_id'], 'fk_invoicable_items_currency_id_e77ada5f')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('invoice_details', function (Blueprint $table) {
            $table->foreign(['crm_customer_id'], 'fk_invoice_details_crm_customer_id_61e3be94')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['invoicable_item_id'], 'fk_invoice_details_invoicable_item_id_f01e159c')->references(['id'])->on('invoicable_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_invoice_details_sample_detail_id_74fc178b')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_invoice_details_sample_header_id_ab2c8ca5')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('iso_audits', function (Blueprint $table) {
            $table->foreign(['audit_type_id'], 'fk_iso_audits_audit_type_id_ca824c7f')->references(['id'])->on('audit_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['checklist_id'], 'fk_iso_audits_checklist_id_ce19dcf5')->references(['id'])->on('audit_checklists')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_iso_audits_status_id_208efbcf')->references(['id'])->on('audit_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_iso_audits_company_id_0f65128f')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_iso_audits_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('item_brands', function (Blueprint $table) {
            $table->foreign(['inventory_sub_category_id'], 'fk_item_brands_inventory_sub_category_id_0cdd51ba')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('lab_category_items', function (Blueprint $table) {
            $table->foreign(['inventory_category_id'], 'fk_lab_category_items_inventory_category_id_5cd33ec1')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_item_id'], 'fk_lab_category_items_inventory_item_id_61c735c4')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_sub_category_id'], 'fk_lab_category_items_inventory_sub_category_id_f980538e')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['item_brand_id'], 'fk_lab_category_items_item_brand_id_d4391140')->references(['id'])->on('item_brands')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('lab_inventory_category', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_lab_inventory_category_company_id_977b77fc')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_category_id'], 'fk_lab_inventory_category_inventory_category_id_cec2b38e')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_lab_inventory_category_inventory_location_id_fc5e27a5')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('lab_results_excel', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_lab_results_excel_analyte_id_0e9eb583')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_id'], 'fk_lab_results_excel_sample_header_id_ebf277af')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('lab_section_approver_configuration', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_lab_section_approver_configuration_user_id_47c33b35')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('lab_section_approver_relation', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_lab_section_approver_relation_user_id_23a338dc')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('lab_stock_movement', function (Blueprint $table) {
            $table->foreign(['preparation_id'], 'fk_lab_stock_movement_preparation_id_9e41c528')->references(['id'])->on('solution_preparations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_sub_category_id'], 'fk_lab_stock_movement_lab_sub_category_id_574a498a')->references(['id'])->on('lab_sub_category')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_lab_stock_movement_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('labs', function (Blueprint $table) {
            $table->foreign(['directorate_id'], 'fk_labs_directorate_id_a78b8d76')->references(['id'])->on('directorates')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['manager_id'], 'fk_labs_manager_id_92b47842')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['zone_id'], 'fk_labs_zone_id_1eca8233')->references(['id'])->on('zones')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_labs_company_id_f800269a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('likelihood_scales', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_likelihood_scales_company_id_8f5f0346')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('lookup_table_entries', function (Blueprint $table) {
            $table->foreign(['lookup_table_id'], 'fk_lookup_table_entries_lookup_table_id_94e237c9')->references(['id'])->on('lookup_tables')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('maintainance_calibration_logs', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_maintainance_calibration_logs_equipment_id_b95ea336')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_maintainance_calibration_logs_supplier_id_1227d389')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('method_reagents', function (Blueprint $table) {
            $table->foreign(['inventory_sub_category_id'], 'fk_method_reagents_inventory_sub_category_id_5b1954ac')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_run_samples', function (Blueprint $table) {
            $table->foreign(['run_id'], 'fk_ms_run_samples_run')->references(['id'])->on('method_sequence_runs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_method_sequence_run_samples_captured_result_id_62e96e76')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_method_sequence_run_samples_sample_detail_id_4f13d10b')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_method_sequence_run_samples_sample_header_id_f08806d6')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_run_stage_data', function (Blueprint $table) {
            $table->foreign(['run_id'], 'fk_ms_run_stage_run')->references(['id'])->on('method_sequence_runs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stage_id'], 'fk_ms_run_stage_stage_id')->references(['id'])->on('method_sequence_stages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['started_by_user_id'], 'fk_ms_run_stage_started_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['completed_by_user_id'], 'fk_ms_run_stage_completed_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('method_sequence_runs', function (Blueprint $table) {
            $table->foreign(['method_sequence_id'], 'fk_method_sequence_runs_method_sequence_id_29d7cc91')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_method_sequence_runs_sample_header_id_863a6058')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_stage_control_results', function (Blueprint $table) {
            $table->foreign(['run_stage_data_id'], 'fk_ms_ctrl_res_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['control_usage_id'], 'fk_ms_ctrl_res_usage')->references(['id'])->on('method_sequence_stage_control_usage')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_stage_control_usage', function (Blueprint $table) {
            $table->foreign(['run_stage_data_id'], 'fk_ms_ctrl_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            $table->foreign(['run_stage_data_id'], 'fk_ms_equip_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_method_sequence_stage_equipment_usage_equipment_id_a0617a22')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_stage_media_usage', function (Blueprint $table) {
            $table->foreign(['run_stage_data_id'], 'fk_ms_media_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_stage_sample_results', function (Blueprint $table) {
            $table->foreign(['run_stage_data_id'], 'fk_ms_samp_res_stage')->references(['id'])->on('method_sequence_run_stage_data')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_method_sequence_stage_sample_results_captured_resul_de3fa0d2')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_stages', function (Blueprint $table) {
            $table->foreign(['method_sequence_version_id'], 'fk_method_sequence_stages_method_sequence_version_id_556e02e9')->references(['id'])->on('method_sequence_versions')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('method_sequence_versions', function (Blueprint $table) {
            $table->foreign(['method_sequence_id'], 'fk_method_sequence_versions_method_sequence_id_40a73fea')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_method_sequence_versions_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['approved_by'], 'fk_method_sequence_versions_approved_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('method_sequences', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_method_sequences_analyte_id_99ba4b3b')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('module_pre_configs', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_module_pre_configs_inventory_location_id_805c2e87')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('module_pre_configs_07', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_module_pre_configs_07_inventory_location_id_fc74b809')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('naming_convension_consensuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_naming_convension_consensuses_company_id_bfef9c35')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('nc_origins', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_nc_origins_company_id_44402b30')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('nc_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_nc_statuses_company_id_fc706f40')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('non_conformances', function (Blueprint $table) {
            $table->foreign(['audit_finding_id'], 'fk_non_conformances_audit_finding_id_2eac81eb')->references(['id'])->on('audit_module_findings')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['audit_id'], 'fk_non_conformances_audit_id_3b51564e')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['likelihood_scale_id'], 'fk_non_conformances_likelihood_scale_id_f306b6b8')->references(['id'])->on('likelihood_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['origin_id'], 'fk_non_conformances_origin_id_693b6bf0')->references(['id'])->on('nc_origins')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_level_id'], 'fk_non_conformances_risk_level_id_93ea508a')->references(['id'])->on('risk_levels')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['severity_scale_id'], 'fk_non_conformances_severity_scale_id_79a8b53e')->references(['id'])->on('severity_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_non_conformances_status_id_5c054baa')->references(['id'])->on('nc_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_non_conformances_company_id_85b6a89e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_non_conformances_equipment_id_d579c041')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_non_conformances_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('o_t_p_s', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_o_t_p_s_user_id_4eb23e05')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('parts_repaireds', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_parts_repaireds_user_id_209c15c6')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('personel_certifications', function (Blueprint $table) {
            $table->foreign(['role_certification_id'], 'fk_personel_certifications_role_certification_id_54aa4d75')->references(['id'])->on('role_certifications')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('personnel_work_histories', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_personnel_work_histories_user_id_e5e8c97b')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('phone_contacts', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_phone_contacts_company_id_cbd9c3f3')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('pricelist_customers', function (Blueprint $table) {
            $table->foreign(['pricelist_id'], 'fk_pricelist_customers_pricelist_id_ccbf775c')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('pricelist_items', function (Blueprint $table) {
            $table->foreign(['pricelist_id'], 'fk_pricelist_items_pricelist_id_62f2ba68')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_pricelist_items_sample_type_id_7c867377')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('pricelists', function (Blueprint $table) {
            $table->foreign(['currency_id'], 'fk_pricelists_currency_id_c478c4e1')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('procedure_config_fields', function (Blueprint $table) {
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_config_fields_procedure_worksheet_id_539f1c54')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('procedure_test_kit_columns', function (Blueprint $table) {
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_test_kit_columns_procedure_worksheet_id_ff38cbf1')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('procedure_test_kit_rows', function (Blueprint $table) {
            $table->foreign(['captured_result_id'], 'fk_procedure_test_kit_rows_captured_result_id_458b1822')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_test_kit_rows_procedure_worksheet_id_4b8a412f')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('procedure_test_kit_values', function (Blueprint $table) {
            $table->foreign(['captured_result_id'], 'fk_procedure_test_kit_values_captured_result_id_5eb540d3')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_test_kit_column_id'], 'fk_procedure_test_kit_values_procedure_test_kit_column_440c979c')->references(['id'])->on('procedure_test_kit_columns')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_test_kit_row_id'], 'fk_procedure_test_kit_values_procedure_test_kit_row_id_95a59ff6')->references(['id'])->on('procedure_test_kit_rows')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('procedure_worksheet_step_analysts', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_procedure_worksheet_step_analysts_analyte_id_bd94285f')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_worksheet_step_analysts_procedure_workshe_a59794e0')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_step_id'], 'fk_procedure_worksheet_step_analysts_procedure_workshe_b11f1c3b')->references(['id'])->on('procedure_worksheet_steps')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['batch_id'], 'fk_procedure_worksheet_step_analysts_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('procedure_worksheet_steps', function (Blueprint $table) {
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_worksheet_steps_procedure_worksheet_id_23f70259')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('qc_approvers_config', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_qc_approvers_config_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('qc_processed_result', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_qc_processed_result_analysis_type_id_17936981')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_qc_processed_result_analyte_id_847ea0d1')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_qc_processed_result_sample_type_id_93101bbe')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_id'], 'fk_qc_processed_result_standard_id_31b3bf15')->references(['id'])->on('standards')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_value_id'], 'fk_qc_processed_result_standard_value_id_516e8a52')->references(['id'])->on('standard_values')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('qc_results', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_qc_results_analysis_type_id_5975cf64')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analyte_id'], 'fk_qc_results_analyte_id_78a4e4de')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_qc_results_captured_result_id_99b77d25')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['qc_scheme_id'], 'fk_qc_results_qc_scheme_id_7e36a928')->references(['id'])->on('qc_scheme')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['qc_type_id'], 'fk_qc_results_qc_type_id_b96596bd')->references(['id'])->on('qc_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['result_id'], 'fk_qc_results_result_id_95a58fdd')->references(['id'])->on('results')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_qc_results_sample_detail_id_51a69dc5')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_qc_results_sample_header_id_2ab1756a')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('qc_types', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_qc_types_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('quotation_details', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_quotation_details_analyte_id_23fff97d')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['invoicable_item_id'], 'fk_quotation_details_invoicable_item_id_676ef229')->references(['id'])->on('invoicable_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['quotation_header_id'], 'fk_quotation_details_quotation_header_id_e74eb412')->references(['id'])->on('quotation_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('quotation_details_analysis_type', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_quotation_details_analysis_type_analysis_type_id_abc46297')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['quotation_detail_id'], 'fk_quotation_details_analysis_type_quotation_detail_id_bb14845c')->references(['id'])->on('quotation_details')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('quotation_headers', function (Blueprint $table) {
            $table->foreign(['crm_customer_contact_id'], 'fk_quotation_headers_crm_customer_contact_id_ec04541f')->references(['id'])->on('crm_customer_contacts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_customer_id'], 'fk_quotation_headers_crm_customer_id_7a859b41')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['currency_id'], 'fk_quotation_headers_currency_id_623c064b')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pricelist_id'], 'fk_quotation_headers_pricelist_id_88831307')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('rating_details', function (Blueprint $table) {
            $table->foreign(['rating_header_id'], 'fk_rating_details_rating_header_id_0066c540')->references(['id'])->on('rating_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('rca_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_rca_statuses_company_id_fb4c4791')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('remedy_details', function (Blueprint $table) {
            $table->foreign(['remedy_header_id'], 'fk_remedy_details_remedy_header_id_60f14724')->references(['id'])->on('remedy_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('report_format_details', function (Blueprint $table) {
            $table->foreign(['report_format_id'], 'fk_report_format_details_report_format_id_c6376010')->references(['id'])->on('report_formats')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('report_format_sample_analysis_stage', function (Blueprint $table) {
            $table->foreign(['report_format_id'], 'rf_sas_format_id_fk')->references(['id'])->on('report_formats')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_analysis_stage_id'], 'rf_sas_stage_id_fk')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('report_format_sections', function (Blueprint $table) {
            $table->foreign(['report_format_id'], 'fk_report_format_sections_report_format_id_1e18f082')->references(['id'])->on('report_formats')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('report_formats', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_report_formats_company_id_7b711636')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('report_header_details', function (Blueprint $table) {
            $table->foreign(['sample_header_id'], 'fk_report_header_details_sample_header_id_2552a1da')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('request_entities', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_request_entities_inventory_location_id_baac4ff6')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['supplier_id'], 'fk_request_entities_supplier_id_326a86c6')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_request_entities_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('request_entity_items', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'], 'fk_request_entity_items_inventory_item_id_86a0622d')->references(['id'])->on('inventory_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_sub_category_id'], 'fk_request_entity_items_inventory_sub_category_id_89053d61')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['item_brand_id'], 'fk_request_entity_items_item_brand_id_719ceceb')->references(['id'])->on('item_brands')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('results', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_results_analysis_type_id_af56d81b')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['analyte_id'], 'fk_results_analyte_id_ffd54808')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_results_captured_result_id_60275229')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lab_section_id'], 'fk_results_lab_section_id')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ltm_method_id'], 'fk_results_ltm_method_id')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_results_sample_detail_id_a6d7fe77')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_results_sample_header_id_7a5d0465')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_acceptance_criteria', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_acceptance_criteria_company_id_b0deb05f')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_assessments', function (Blueprint $table) {
            $table->foreign(['likelihood_scale_id'], 'fk_risk_assessments_likelihood_scale_id_be10f1a5')->references(['id'])->on('likelihood_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_id'], 'fk_risk_assessments_risk_id_8f60d3c9')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['severity_scale_id'], 'fk_risk_assessments_severity_scale_id_3280c7b0')->references(['id'])->on('severity_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_risk_assessments_company_id_6b228850')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_risk_assessments_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risk_attachments', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_attachments_company_id_e731fad6')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_business_processes', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_business_processes_company_id_6a052fac')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_categories', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_categories_company_id_95b61279')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_configuration_options', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_configuration_options_company_id_88281b71')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_risk_configuration_options_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risk_evaluations', function (Blueprint $table) {
            $table->foreign(['assessment_id'], 'fk_risk_evaluations_assessment_id_b0ac8246')->references(['id'])->on('risk_assessments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['risk_id'], 'fk_risk_evaluations_risk_id_6d1eade0')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_risk_evaluations_company_id_420be571')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_risk_evaluations_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risk_level_thresholds', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_level_thresholds_company_id_7ff6d18a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_levels', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_levels_company_id_62f4dc04')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_notifications', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_notifications_company_id_c984bf84')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_process_links', function (Blueprint $table) {
            $table->foreign(['business_process_id'], 'fk_risk_process_links_business_process_id_1371d30f')->references(['id'])->on('risk_business_processes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['risk_id'], 'fk_risk_process_links_risk_id_ceac4996')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_risk_process_links_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risk_review_frequencies', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_review_frequencies_company_id_ae1de1c3')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_reviews', function (Blueprint $table) {
            $table->foreign(['risk_id'], 'fk_risk_reviews_risk_id_d1946dee')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_risk_reviews_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risk_scoring_configs', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_scoring_configs_company_id_11d8bc1c')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_sources', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_sources_company_id_3f026371')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_risk_statuses_company_id_4daa2f5a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('risk_treatment_implementations', function (Blueprint $table) {
            $table->foreign(['assessment_id'], 'fk_risk_treatment_implementations_assessment_id_659b3668')->references(['id'])->on('risk_assessments')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_id'], 'fk_risk_treatment_implementations_risk_id_832cc869')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['treatment_plan_id'], 'fk_risk_treatment_implementations_treatment_plan_id_f85d25ea')->references(['id'])->on('risk_treatment_plans')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_risk_treatment_implementations_company_id_67b49a1c')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_risk_treatment_implementations_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risk_treatment_plans', function (Blueprint $table) {
            $table->foreign(['capa_id'], 'fk_risk_treatment_plans_capa_id_9589ef67')->references(['id'])->on('corrective_actions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_id'], 'fk_risk_treatment_plans_risk_id_8a847314')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['treatment_type_id'], 'fk_risk_treatment_plans_treatment_type_id_3a6cb495')->references(['id'])->on('treatment_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_risk_treatment_plans_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('risks', function (Blueprint $table) {
            $table->foreign(['audit_finding_id'], 'fk_risks_audit_finding_id_df00ca43')->references(['id'])->on('audit_module_findings')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['audit_id'], 'fk_risks_audit_id_23431ec5')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['category_id'], 'fk_risks_category_id_c2349a14')->references(['id'])->on('risk_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['likelihood_scale_id'], 'fk_risks_likelihood_scale_id_4db81be6')->references(['id'])->on('likelihood_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['method_id'], 'fk_risks_method_id_649bd396')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['non_conformance_id'], 'fk_risks_non_conformance_id_274db830')->references(['id'])->on('non_conformances')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['other_source_id'], 'fk_risks_other_source_id_b254132d')->references(['id'])->on('risk_sources')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_id'], 'fk_risks_sample_id_3ecfa1f8')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['severity_scale_id'], 'fk_risks_severity_scale_id_4e5d3a1f')->references(['id'])->on('severity_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_risks_status_id_9032896b')->references(['id'])->on('risk_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_risks_company_id_91e8fdd9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['complaint_id'], 'fk_risks_complaint_id_3229aa4f')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_id'], 'fk_risks_equipment_id_b8697cec')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_risks_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('role_certifications', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_role_certifications_role_id_d818fa47')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('roles', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_roles_company_id_ac3f7bb4')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('root_cause_analyses', function (Blueprint $table) {
            $table->foreign(['non_conformance_id'], 'fk_root_cause_analyses_non_conformance_id_e1a6c6d0')->references(['id'])->on('non_conformances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['root_cause_method_id'], 'fk_root_cause_analyses_root_cause_method_id_152078fc')->references(['id'])->on('root_cause_methods')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_root_cause_analyses_status_id_5ff24697')->references(['id'])->on('rca_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_root_cause_analyses_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('root_cause_methods', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_root_cause_methods_company_id_ebffa6d5')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('samaco_sheet', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_samaco_sheet_equipment_id_21279139')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_analysis_dates', function (Blueprint $table) {
            $table->foreign(['sample_detail_id'], 'fk_sample_analysis_dates_sample_detail_id_5367e6fe')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_sample_analysis_dates_sample_header_id_c88bb7fc')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_analysis_stages', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_sample_analysis_stages_company_id_cca3edf2')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_sample_analysis_stages_lab_id_dd6032a1')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('sample_analysis_type_relation', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_sample_analysis_type_relation_analysis_type_id_09a1a145')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_sample_analysis_type_relation_sample_detail_id_31d9dff9')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['batch_id'], 'fk_sample_analysis_type_relation_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_approval_checklist', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_sample_approval_checklist_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('sample_captured_worksheet_formulas', function (Blueprint $table) {
            $table->foreign(['captured_result_id'], 'fk_sample_captured_worksheet_formulas_captured_result_f84b2efd')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_sample_captured_worksheet_formulas_sample_detail_id_d79e98f0')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_sample_captured_worksheet_formulas_sample_header_id_bc783e08')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formular_id'], 'fk_sample_captured_worksheet_formulas_formular_id')->references(['id'])->on('formulas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['done_by_user_id'], 'fk_worksheet_done_by_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['read_by_user_id'], 'fk_worksheet_read_by_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['posted_by_user_id'], 'fk_worksheet_posted_by_user')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('sample_conditions', function (Blueprint $table) {
            $table->foreign(['sample_type_id'], 'fk_sample_conditions_sample_type_id_ee4fc7a9')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_dates', function (Blueprint $table) {
            $table->foreign(['sample_header_id'], 'fk_sample_dates_sample_header_id_0400ca62')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('sample_detail_staging', function (Blueprint $table) {
            $table->foreign(['sample_header_id'], 'fk_sample_detail_staging_sample_header_id_e4a389df')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_details', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_sample_details_analysis_type_id_4bbdb281')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_product_id'], 'fk_sample_details_company_product_id_6a2b2201')->references(['id'])->on('company_products')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_sample_details_lab_id_6032c7b2')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['reporting_unit_id'], 'fk_sample_details_reporting_unit_id_6445cee8')->references(['id'])->on('reporting_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_condition_id'], 'fk_sample_details_sample_condition_id_297b33e9')->references(['id'])->on('sample_conditions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_id'], 'fk_sample_details_sample_header_id_75a450ac')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_point_id'], 'fk_sample_details_sample_point_id_3d478b93')->references(['id'])->on('sample_points')->onUpdate('no action')->onDelete('set null');
            $table->foreign('crm_unit_id')->references('id')->on('crm_company_units')->onDelete('set null');
            $table->foreign('main_standard')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('secondary_standard')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('third_standard_id')->references('id')->on('standards')->onDelete('set null');
            $table->foreign('store_id')->references('id')->on('inventory_stores')->onDelete('set null');
            $table->foreign('store_slot_id')->references('id')->on('inventory_store_slots')->onDelete('set null');
        });

        $this->safeTable('sample_header_staging', function (Blueprint $table) {
            $table->foreign(['sample_type_id'], 'fk_sample_header_staging_sample_type_id_ac1236b3')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_sample_header_staging_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('sample_headers', function (Blueprint $table) {
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
        });

        $this->safeTable('sample_imports', function (Blueprint $table) {
            $table->foreign(['sample_header_id'], 'fk_sample_imports_sample_header_id_31a30e9f')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
        });

        if (Schema::hasTable('sample_point_area')) {
            $this->safeTable('sample_point_area', function (Blueprint $table) {
                $table->foreign(['crm_customer_id'], 'fk_sample_point_area_crm_customer_id_a7c9520c')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
                if (Schema::hasTable('crm_areas')) {
                    $table->foreign(['crm_area_id'], 'fk_sample_point_area_crm_area_id_60117a20')->references(['id'])->on('crm_areas')->onUpdate('no action')->onDelete('set null');
                }
                $table->foreign(['crm_company_sub_unit_id'], 'fk_sample_point_area_crm_company_sub_unit_id_7479b6de')->references(['id'])->on('crm_company_sub_units')->onUpdate('no action')->onDelete('set null');
                $table->foreign(['crm_company_unit_id'], 'fk_sample_point_area_crm_company_unit_id_b92fe99c')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('set null');
            });
        }

        $this->safeTable('sample_points', function (Blueprint $table) {
            if (Schema::hasTable('sample_point_area')) {
                $table->foreign(['sample_point_area_id'], 'fk_sample_points_sample_point_area_id_288fec45')->references(['id'])->on('sample_point_area')->onUpdate('no action')->onDelete('cascade');
            }
            if (Schema::hasTable('crm_areas')) {
                $table->foreign(['crm_area_id'], 'fk_sample_points_crm_area_id_49c57678')->references(['id'])->on('crm_areas')->onUpdate('no action')->onDelete('set null');
            }
            $table->foreign(['crm_company_sub_unit_id'], 'fk_sample_points_crm_company_sub_unit_id_f42a1dd4')->references(['id'])->on('crm_company_sub_units')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_company_unit_id'], 'fk_sample_points_crm_company_unit_id_acbfb0fc')->references(['id'])->on('crm_company_units')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['crm_customer_id'], 'fk_sample_points_crm_customer_id_d6d2e4fc')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
            if (Schema::hasTable('crm_sample_points')) {
                $table->foreign(['crm_sample_point_id'], 'fk_sample_points_crm_sample_point_id_9dc4db32')->references(['id'])->on('crm_sample_points')->onUpdate('no action')->onDelete('set null');
            }
        });

        $this->safeTable('sample_progress', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_sample_progress_analyte_id_628b0345')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['method_id'], 'fk_sample_progress_method_id_f95a9f19')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['sample_detail_id'], 'fk_sample_progress_sample_detail_id_b3d1e95e')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stage_header_id'], 'fk_sample_progress_stage_header_id_9ad48958')->references(['id'])->on('stage_headers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['test_stage_id'], 'fk_sample_progress_test_stage_id_5cb1229d')->references(['id'])->on('test_stages')->onUpdate('no action')->onDelete('no action');
        });

        $this->safeTable('sample_submission_request_exhibits', function (Blueprint $table) {
            $table->foreign(['sample_detail_id'], 'fk_sample_submission_request_exhibits_sample_detail_id_11a32e48')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_submission_request_id'], 'fk_sample_submission_request_exhibits_sample_submissio_186edc74')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_submission_request_requested_analyses', function (Blueprint $table) {
            $table->foreign(['sample_submission_request_id'], 'fk_sample_submission_request_requested_analyses_sample_b0b24d94')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_submission_request_supporting_document_templates', function (Blueprint $table) {
            $table->foreign(['sample_submission_request_id'], 'ssr_sdoc_templates_request_fk')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supporting_document_template_id'], 'ssr_sdoc_templates_template_fk')->references(['id'])->on('supporting_document_templates')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_submission_request_suspects', function (Blueprint $table) {
            $table->foreign(['sample_submission_request_id'], 'fk_sample_submission_request_suspects_sample_submissio_1bdd5f9f')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_submission_requests', function (Blueprint $table) {
            $table->foreign(['crm_customer_id'], 'fk_sample_submission_requests_crm_customer_id_c9e674aa')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_id'], 'fk_sample_submission_requests_sample_header_id_85abbada')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('sample_to_sample_analysis_stages', function (Blueprint $table) {
            $table->foreign(['sample_analysis_stage_id'], 'fk_sample_to_sample_analysis_stages_sample_analysis_st_4df8a901')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_sample_to_sample_analysis_stages_sample_type_id_761dff80')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_type_qualifications', function (Blueprint $table) {
            $table->foreign(['qualification_id'], 'fk_sample_type_qualifications_qualification_id_f79cb613')->references(['id'])->on('qualifications')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_types', function (Blueprint $table) {
            $table->foreign(['rating_header_id'], 'fk_sample_types_rating_header_id_b2011103')->references(['id'])->on('rating_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['report_format_id'], 'fk_sample_types_report_format_id_3756207b')->references(['id'])->on('report_formats')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_sample_types_company_id_87f39267')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_worksheet_formular_mandatory_data', function (Blueprint $table) {
            $table->foreign(['worksheet_formular_id'], 'fk_wkst_mand_formula')->references(['id'])->on('sample_captured_worksheet_formulas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_mandatory_field_id'], 'fk_sample_worksheet_formular_mandatory_data_formula_ma_b1dc5bcc')->references(['id'])->on('formula_mandatory_fields')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('sample_worksheet_formular_step_data', function (Blueprint $table) {
            $table->foreign(['overridden_lookup_table_id'], 'fk_override_lookup')->references(['id'])->on('lookup_tables')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['worksheet_formular_id'], 'fk_wkst_step_formula')->references(['id'])->on('sample_captured_worksheet_formulas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_step_id'], 'fk_sample_worksheet_formular_step_data_formula_step_id_753dd4c6')->references(['id'])->on('formula_steps')->onUpdate('no action')->onDelete('cascade');
        });

        if (Schema::hasTable('sampletype_area_relation') && Schema::hasTable('crm_areas')) {
            $this->safeTable('sampletype_area_relation', function (Blueprint $table) {
                $table->foreign(['area_id'], 'fk_sampletype_area_relation_area_id_8a8d5dd5')->references(['id'])->on('crm_areas')->onUpdate('no action')->onDelete('cascade');
            });
        }
        $this->safeTable('sampletype_area_relation', function (Blueprint $table) {
            $table->foreign(['sample_type_id'], 'fk_sampletype_area_relation_sample_type_id_f00d6d77')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        if (Schema::hasTable('sampletype_sample_point_relation') && Schema::hasTable('crm_sample_points')) {
            $this->safeTable('sampletype_sample_point_relation', function (Blueprint $table) {
                $table->foreign(['sample_point_id'], 'fk_sampletype_sample_point_relation_sample_point_id_af6516ee')->references(['id'])->on('crm_sample_points')->onUpdate('no action')->onDelete('cascade');
            });
        }
        $this->safeTable('sampletype_sample_point_relation', function (Blueprint $table) {
            $table->foreign(['sample_type_id'], 'fk_sampletype_sample_point_relation_sample_type_id_21358013')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ser_header_worksheet_sample_relations', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_ser_header_worksheet_sample_relations_analysis_type_3fbb5aac')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_ser_header_worksheet_sample_relations_sample_detail_62c83c53')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ser_step_worksheet_sample_relations', function (Blueprint $table) {
            $table->foreign(['ser_header_id'], 'fk_stp_hdr_id')->references(['id'])->on('ser_header_worksheet_sample_relations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_ser_step_worksheet_sample_relations_equipment_id_7babb0d0')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ser_worksheet_step_id'], 'fk_ser_step_worksheet_sample_relations_ser_worksheet_s_5e3b6ff7')->references(['id'])->on('ser_worksheet_steps')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('ser_testkit_worksheet_sample_relations', function (Blueprint $table) {
            $table->foreign(['ser_header_id'], 'fk_tstwc_hdr_id')->references(['id'])->on('ser_header_worksheet_sample_relations')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('severity_scales', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_severity_scales_company_id_c2f016e9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('skill_capability_detail', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_skill_capability_detail_user_id_2f1faebe')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('skill_other_training_users', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_skill_other_training_users_user_id_465cabfe')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('skill_training_header', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_skill_training_header_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('skills_capability_matrix', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_skills_capability_matrix_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('skills_capability_matrix_role', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_skills_capability_matrix_role_role_id_eac5517b')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_skills_capability_matrix_role_user_id_6df6741f')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('skills_matrix_detail_role', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_skills_matrix_detail_role_role_id_e5c99e56')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('skills_matrix_role_requirments', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_skills_matrix_role_requirments_role_id_0ecc33c0')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('skills_training_planner_header', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_skills_training_planner_header_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('solution_batch_history', function (Blueprint $table) {
            $table->foreign(['solution_id'], 'fk_solution_batch_history_solution_id_f5c4c63c')->references(['id'])->on('lab_sub_category')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('solution_preparations', function (Blueprint $table) {
            $table->foreign(['solution_id'], 'fk_solution_preparations_solution_id_dd3390d4')->references(['id'])->on('lab_sub_category')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('spatie_model_has_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'fk_spatie_model_has_permissions_permission_id_26114abc')->references(['id'])->on('spatie_permissions')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('spatie_model_has_roles', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_spatie_model_has_roles_role_id_baea0445')->references(['id'])->on('spatie_roles')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('spatie_role_has_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'fk_spatie_role_has_permissions_permission_id_c9388e9a')->references(['id'])->on('spatie_permissions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'fk_spatie_role_has_permissions_role_id_8803c21a')->references(['id'])->on('spatie_roles')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('spatie_roles', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_spatie_roles_company_id_9a32a716')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('stage_headers', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_stage_headers_analyte_id_19346a7c')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['method_id'], 'fk_stage_headers_method_id_46186784')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['sample_type_id'], 'fk_stage_headers_sample_type_id_9876e3c1')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('no action');
        });

        $this->safeTable('standards', function (Blueprint $table) {
            $table->foreign(['qc_type_id'], 'fk_standards_qc_type_id_854455b4')->references(['id'])->on('qc_types')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('standards_analytes', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_standards_analytes_analyte_id_1ce10517')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_id'], 'fk_standards_analytes_standard_id_d4160bfa')->references(['id'])->on('standards')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_value_id'], 'fk_standards_analytes_standard_value_id_85cb104e')->references(['id'])->on('standard_values')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('stock_taking_counters', function (Blueprint $table) {
            $table->foreign(['stock_taking_id'], 'fk_stock_taking_counters_stock_taking_id_946b5769')->references(['id'])->on('stock_takings')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('stock_taking_sheets', function (Blueprint $table) {
            $table->foreign(['inventory_sub_category_id'], 'fk_stock_taking_sheets_inventory_sub_category_id_a0c29164')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stock_taking_id'], 'fk_stock_taking_sheets_stock_taking_id_96e75830')->references(['id'])->on('stock_takings')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('stock_takings', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_stock_takings_inventory_location_id_6aff6a64')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_stock_takings_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('stock_transfer_items', function (Blueprint $table) {
            $table->foreign(['stock_transfer_id'], 'fk_stock_transfer_items_stock_transfer_id_158032d9')->references(['id'])->on('stock_transfers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('stock_transfers', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_stock_transfers_inventory_location_id_c3d6af6b')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_audit_log', function (Blueprint $table) {
            $table->foreign(['submission_form_instance_id'], 'sf_audit_instance_id_foreign')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'sf_audit_user_id_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });

        $this->safeTable('submission_form_audit_logs', function (Blueprint $table) {
            $table->foreign(['submission_form_instance_id'], 'fk_submission_form_audit_logs_submission_form_instance_6021b7dc')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_submission_form_audit_logs_user_id_36d8a7c1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_element_holders', function (Blueprint $table) {
            $table->foreign(['submission_form_section_id'], 'sf_element_holders_section_id_foreign')->references(['id'])->on('submission_form_sections')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_elements', function (Blueprint $table) {
            $table->foreign(['submission_form_element_holder_id'], 'sf_elements_holder_id_foreign')->references(['id'])->on('submission_form_element_holders')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_instance_values', function (Blueprint $table) {
            $table->foreign(['submission_form_element_id'], 'sf_values_element_id_foreign')->references(['id'])->on('submission_form_elements')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['submission_form_instance_id'], 'sf_values_instance_id_foreign')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_instances', function (Blueprint $table) {
            $table->foreign(['submission_form_id'], 'sf_instances_form_id_foreign')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['reviewed_by'], 'sf_instances_reviewed_by_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['submitted_by'], 'sf_instances_submitted_by_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });

        $this->safeTable('submission_form_permissions', function (Blueprint $table) {
            $table->foreign(['submission_form_id'], 'sf_permissions_form_id_foreign')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'], 'sf_permissions_role_id_foreign')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'sf_permissions_user_id_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_sample_analysis_stage', function (Blueprint $table) {
            $table->foreign(['submission_form_id'], 'fk_submission_form_sample_analysis_stage_submission_fo_ad11ba44')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_analysis_stage_id'], 'fk_submission_form_sample_analysis_stage_sample_analys_ae4d8763')->references(['id'])->on('sample_analysis_stages')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_form_sections', function (Blueprint $table) {
            $table->foreign(['submission_form_id'], 'fk_submission_form_sections_submission_form_id_0630182a')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('submission_forms', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_submission_forms_created_by_e95a368e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });

        $this->safeTable('supplier_brands', function (Blueprint $table) {
            $table->foreign(['inventory_sub_category_id'], 'fk_supplier_brands_inventory_sub_category_id_6d8e4317')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_supplier_brands_supplier_id_f3092c76')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_by_categories', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_by_categories_supplier_id_48556342')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_categories', function (Blueprint $table) {
            $table->foreign(['inventory_sub_category_id'], 'fk_supplier_categories_inventory_sub_category_id_a989c7c5')->references(['id'])->on('inventory_sub_categories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_supplier_categories_supplier_id_b44737ac')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_contacts', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_contacts_supplier_id_080e6a73')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_contracts', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_contracts_supplier_id_b7cee5b3')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_quote_attachments', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_quote_attachments_supplier_id_00078211')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_quote_notes', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_quote_notes_supplier_id_c6ca0f26')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_quotes', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_quotes_supplier_id_2b1e0c9a')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supplier_r_f_q_s', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_supplier_r_f_q_s_supplier_id_03c7fc11')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('suppliers', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_suppliers_company_id_303eda27')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_suppliers_inventory_location_id_08c9d055')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('suppliers_categories', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_suppliers_categories_supplier_id_607c2167')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('suppliers_rating_criterias', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_suppliers_rating_criterias_supplier_id_750c29e8')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supporting_document_elements', function (Blueprint $table) {
            $table->foreign(['supporting_document_section_id'], 'fk_supporting_document_elements_supporting_document_se_567b3611')->references(['id'])->on('supporting_document_sections')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supporting_document_instance_values', function (Blueprint $table) {
            $table->foreign(['supporting_document_element_id'], 'fk_supporting_document_instance_values_supporting_docu_fca71b20')->references(['id'])->on('supporting_document_elements')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supporting_document_instance_id'], 'fk_supporting_document_instance_values_supporting_docu_eca0ad4a')->references(['id'])->on('supporting_document_instances')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supporting_document_instances', function (Blueprint $table) {
            $table->foreign(['sample_submission_request_id'], 'sdoc_instances_submission_request_fk')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supporting_document_template_id'], 'sdoc_instances_template_fk')->references(['id'])->on('supporting_document_templates')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_supporting_document_instances_sample_header_id_09f8aa51')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_supporting_document_instances_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('supporting_document_sections', function (Blueprint $table) {
            $table->foreign(['supporting_document_template_id'], 'fk_supporting_document_sections_supporting_document_te_c118c3bd')->references(['id'])->on('supporting_document_templates')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('supporting_document_templates', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_supporting_document_templates_company_id_8b663750')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_supporting_document_templates_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('system_configurations', function (Blueprint $table) {
            $table->foreign(['configuration_type_id'], 'fk_system_configurations_configuration_type_id')->references(['id'])->on('system_configuration_types')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('tat_captured', function (Blueprint $table) {
            $table->foreign(['analysis_type_id'], 'fk_tat_captured_analysis_type_id_d2586f85')->references(['id'])->on('analysis_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_tat_captured_analyte_id_90fcad44')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['captured_result_id'], 'fk_tat_captured_captured_result_id_ce707f0f')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_detail_id'], 'fk_tat_captured_sample_detail_id_429ce006')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_tat_captured_sample_header_id_923cf294')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_tat_captured_sample_type_id_3e3e34f0')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('test_stages', function (Blueprint $table) {
            $table->foreign(['stage_header_id'], 'fk_test_stages_stage_header_id_64ddf6d8')->references(['id'])->on('stage_headers')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ticket_assignments', function (Blueprint $table) {
            $table->foreign(['assigned_by'], 'fk_ticket_assignments_assigned_by_2ab2e2a6')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ticket_id'], 'fk_ticket_assignments_ticket_id_6360d46f')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_assignments_user_id_1c68edcb')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ticket_change_history', function (Blueprint $table) {
            $table->foreign(['ticket_id'], 'fk_ticket_change_history_ticket_id_34778629')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_change_history_user_id_6b8da7bf')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('ticket_chat', function (Blueprint $table) {
            $table->foreign(['ticket_id'], 'fk_ticket_chat_ticket_id_210db4dd')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_chat_user_id_b5e9069c')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ticket_chat_attachments', function (Blueprint $table) {
            $table->foreign(['chat_message_id'], 'fk_ticket_chat_attachments_chat_message_id_c1d53826')->references(['id'])->on('ticket_chat')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ticket_comments', function (Blueprint $table) {
            $table->foreign(['parent_comment_id'], 'fk_ticket_comments_parent_comment_id_5c4b5d16')->references(['id'])->on('ticket_comments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ticket_id'], 'fk_ticket_comments_ticket_id_f754f0bc')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_comments_user_id_ba807265')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ticket_permissions', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_ticket_permissions_user_id_7330fdef')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('ticket_team_chat', function (Blueprint $table) {
            $table->foreign(['ticket_id'], 'fk_ticket_team_chat_ticket_id_a741762d')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_team_chat_user_id_5d0780f7')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('treatment_types', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_treatment_types_company_id_3e2be6cf')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('uncertainty_budgets', function (Blueprint $table) {
            $table->foreign(['analyte_id'], 'fk_uncertainty_budgets_analyte_id_d1244d3a')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_uncertainty_budgets_company_id_8cf47a1e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_uncertainty_budgets_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('uncertainty_sources', function (Blueprint $table) {
            $table->foreign(['uncertainty_budget_id'], 'fk_uncertainty_sources_uncertainty_budget_id_a959796b')->references(['id'])->on('uncertainty_budgets')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('uom_conversions', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_uom_conversions_inventory_location_id_65089776')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('user_alerts', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_user_alerts_user_id_edc83019')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('user_departmental_approvals', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_user_departmental_approvals_role_id_a09303d7')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_user_departmental_approvals_user_id_3394ca6d')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('user_roles', function (Blueprint $table) {
            $table->foreign(['role_id'], 'fk_user_roles_role_id_6896079e')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_user_roles_user_id_2aa2451a')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('users', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_users_company_id_e27b71e0')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supplier_id'], 'fk_users_supplier_id_e9938e35')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['zone_id'], 'fk_users_zone_id_95643f3a')->references(['id'])->on('zones')->onUpdate('no action')->onDelete('set null');
            $table->foreign('client_id', 'fk_users_client_id')->references('id')->on('crm_customers')->onDelete('set null');
            $table->foreign('crm_contact_id', 'fk_users_crm_contact_id')->references('id')->on('crm_customer_contacts')->onDelete('set null');
        });

        $this->safeTable('verification_closure_statuses', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_verification_closure_statuses_company_id_9acf8cc4')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('verification_logs', function (Blueprint $table) {
            $table->foreign(['equipment_id'], 'fk_verification_logs_equipment_id_15b33975')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['supplier_id'], 'fk_verification_logs_supplier_id_4fd7ad25')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('verification_records', function (Blueprint $table) {
            $table->foreign(['closure_status_id'], 'fk_verification_records_closure_status_id_17665657')->references(['id'])->on('verification_closure_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['corrective_action_id'], 'fk_verification_records_corrective_action_id_1d120db9')->references(['id'])->on('corrective_actions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['effectiveness_result_id'], 'fk_verification_records_effectiveness_result_id_9eb622b5')->references(['id'])->on('verification_results')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_verification_records_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('verification_results', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_verification_results_company_id_a8415607')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('work_order_status_histories', function (Blueprint $table) {
            $table->foreign(['created_by_id'], 'fk_work_order_status_histories_created_by_id')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('work_orders', function (Blueprint $table) {
            $table->foreign(['service_id'], 'fk_work_orders_service_id_5fb9f0ea')->references(['id'])->on('services')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('workflow_actions', function (Blueprint $table) {
            $table->foreign(['company_id'], 'fk_workflow_actions_company_id_83e76d82')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('workorder_edits', function (Blueprint $table) {
            $table->foreign(['created_by_id'], 'fk_workorder_edits_created_by_id')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('worksheet_executions', function (Blueprint $table) {
            $table->foreign(['batch_id'], 'fk_worksheet_executions_batch_id_bd61f8d9')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['executed_by'], 'fk_worksheet_executions_executed_by_4db9f8fe')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['formula_version_id'], 'fk_worksheet_executions_formula_version_id_bad11461')->references(['id'])->on('formula_versions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_id'], 'fk_worksheet_executions_sample_id_62862059')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('set null');
        });

        $this->safeTable('zoho_customers', function (Blueprint $table) {
            $table->foreign(['currency_id'], 'fk_zoho_customers_currency_id_c8087f82')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('cascade');
        });

        $this->safeTable('zones', function (Blueprint $table) {
            $table->foreign(['inventory_location_id'], 'fk_zones_inventory_location_id_1b496aad')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->safeTable('zones', function (Blueprint $table) {
            $table->dropForeign('fk_zones_inventory_location_id_1b496aad');
        });

        $this->safeTable('zoho_customers', function (Blueprint $table) {
            $table->dropForeign('fk_zoho_customers_currency_id_c8087f82');
        });

        $this->safeTable('worksheet_executions', function (Blueprint $table) {
            $table->dropForeign('fk_worksheet_executions_batch_id_bd61f8d9');
            $table->dropForeign('fk_worksheet_executions_executed_by_4db9f8fe');
            $table->dropForeign('fk_worksheet_executions_formula_version_id_bad11461');
            $table->dropForeign('fk_worksheet_executions_sample_id_62862059');
        });

        $this->safeTable('workorder_edits', function (Blueprint $table) {
            $table->dropForeign('fk_workorder_edits_created_by_id');
        });

        $this->safeTable('workflow_actions', function (Blueprint $table) {
            $table->dropForeign('fk_workflow_actions_company_id_83e76d82');
        });

        $this->safeTable('work_orders', function (Blueprint $table) {
            $table->dropForeign('fk_work_orders_service_id_5fb9f0ea');
        });

        $this->safeTable('work_order_status_histories', function (Blueprint $table) {
            $table->dropForeign('fk_work_order_status_histories_created_by_id');
        });

        $this->safeTable('verification_results', function (Blueprint $table) {
            $table->dropForeign('fk_verification_results_company_id_a8415607');
        });

        $this->safeTable('verification_records', function (Blueprint $table) {
            $table->dropForeign('fk_verification_records_closure_status_id_17665657');
            $table->dropForeign('fk_verification_records_corrective_action_id_1d120db9');
            $table->dropForeign('fk_verification_records_effectiveness_result_id_9eb622b5');
            $table->dropForeign('fk_verification_records_created_by');
        });

        $this->safeTable('verification_logs', function (Blueprint $table) {
            $table->dropForeign('fk_verification_logs_equipment_id_15b33975');
            $table->dropForeign('fk_verification_logs_supplier_id_4fd7ad25');
        });

        $this->safeTable('verification_closure_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_verification_closure_statuses_company_id_9acf8cc4');
        });

        $this->safeTable('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_company_id_e27b71e0');
            $table->dropForeign('fk_users_supplier_id_e9938e35');
            $table->dropForeign('fk_users_zone_id_95643f3a');
        });

        $this->safeTable('user_roles', function (Blueprint $table) {
            $table->dropForeign('fk_user_roles_role_id_6896079e');
            $table->dropForeign('fk_user_roles_user_id_2aa2451a');
        });

        $this->safeTable('user_departmental_approvals', function (Blueprint $table) {
            $table->dropForeign('fk_user_departmental_approvals_role_id_a09303d7');
            $table->dropForeign('fk_user_departmental_approvals_user_id_3394ca6d');
        });

        $this->safeTable('user_alerts', function (Blueprint $table) {
            $table->dropForeign('fk_user_alerts_user_id_edc83019');
        });

        $this->safeTable('uom_conversions', function (Blueprint $table) {
            $table->dropForeign('fk_uom_conversions_inventory_location_id_65089776');
        });

        $this->safeTable('uncertainty_sources', function (Blueprint $table) {
            $table->dropForeign('fk_uncertainty_sources_uncertainty_budget_id_a959796b');
        });

        $this->safeTable('uncertainty_budgets', function (Blueprint $table) {
            $table->dropForeign('fk_uncertainty_budgets_analyte_id_d1244d3a');
            $table->dropForeign('fk_uncertainty_budgets_company_id_8cf47a1e');
            $table->dropForeign('fk_uncertainty_budgets_created_by');
        });

        $this->safeTable('treatment_types', function (Blueprint $table) {
            $table->dropForeign('fk_treatment_types_company_id_3e2be6cf');
        });

        $this->safeTable('ticket_team_chat', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_team_chat_ticket_id_a741762d');
            $table->dropForeign('fk_ticket_team_chat_user_id_5d0780f7');
        });

        $this->safeTable('ticket_permissions', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_permissions_user_id_7330fdef');
        });

        $this->safeTable('ticket_comments', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_comments_parent_comment_id_5c4b5d16');
            $table->dropForeign('fk_ticket_comments_ticket_id_f754f0bc');
            $table->dropForeign('fk_ticket_comments_user_id_ba807265');
        });

        $this->safeTable('ticket_chat_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_chat_attachments_chat_message_id_c1d53826');
        });

        $this->safeTable('ticket_chat', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_chat_ticket_id_210db4dd');
            $table->dropForeign('fk_ticket_chat_user_id_b5e9069c');
        });

        $this->safeTable('ticket_change_history', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_change_history_ticket_id_34778629');
            $table->dropForeign('fk_ticket_change_history_user_id_6b8da7bf');
        });

        $this->safeTable('ticket_assignments', function (Blueprint $table) {
            $table->dropForeign('fk_ticket_assignments_assigned_by_2ab2e2a6');
            $table->dropForeign('fk_ticket_assignments_ticket_id_6360d46f');
            $table->dropForeign('fk_ticket_assignments_user_id_1c68edcb');
        });

        $this->safeTable('test_stages', function (Blueprint $table) {
            $table->dropForeign('fk_test_stages_stage_header_id_64ddf6d8');
        });

        $this->safeTable('tat_captured', function (Blueprint $table) {
            $table->dropForeign('fk_tat_captured_analysis_type_id_d2586f85');
            $table->dropForeign('fk_tat_captured_analyte_id_90fcad44');
            $table->dropForeign('fk_tat_captured_captured_result_id_ce707f0f');
            $table->dropForeign('fk_tat_captured_sample_detail_id_429ce006');
            $table->dropForeign('fk_tat_captured_sample_header_id_923cf294');
            $table->dropForeign('fk_tat_captured_sample_type_id_3e3e34f0');
        });

        $this->safeTable('system_configurations', function (Blueprint $table) {
            $table->dropForeign('fk_system_configurations_configuration_type_id');
        });

        $this->safeTable('supporting_document_templates', function (Blueprint $table) {
            $table->dropForeign('fk_supporting_document_templates_company_id_8b663750');
            $table->dropForeign('fk_supporting_document_templates_created_by');
        });

        $this->safeTable('supporting_document_sections', function (Blueprint $table) {
            $table->dropForeign('fk_supporting_document_sections_supporting_document_te_c118c3bd');
        });

        $this->safeTable('supporting_document_instances', function (Blueprint $table) {
            $table->dropForeign('sdoc_instances_submission_request_fk');
            $table->dropForeign('sdoc_instances_template_fk');
            $table->dropForeign('fk_supporting_document_instances_sample_header_id_09f8aa51');
            $table->dropForeign('fk_supporting_document_instances_created_by');
        });

        $this->safeTable('supporting_document_instance_values', function (Blueprint $table) {
            $table->dropForeign('fk_supporting_document_instance_values_supporting_docu_fca71b20');
            $table->dropForeign('fk_supporting_document_instance_values_supporting_docu_eca0ad4a');
        });

        $this->safeTable('supporting_document_elements', function (Blueprint $table) {
            $table->dropForeign('fk_supporting_document_elements_supporting_document_se_567b3611');
        });

        $this->safeTable('suppliers_rating_criterias', function (Blueprint $table) {
            $table->dropForeign('fk_suppliers_rating_criterias_supplier_id_750c29e8');
        });

        $this->safeTable('suppliers_categories', function (Blueprint $table) {
            $table->dropForeign('fk_suppliers_categories_supplier_id_607c2167');
        });

        $this->safeTable('suppliers', function (Blueprint $table) {
            $table->dropForeign('fk_suppliers_company_id_303eda27');
            $table->dropForeign('fk_suppliers_inventory_location_id_08c9d055');
        });

        $this->safeTable('supplier_r_f_q_s', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_r_f_q_s_supplier_id_03c7fc11');
        });

        $this->safeTable('supplier_quotes', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_quotes_supplier_id_2b1e0c9a');
        });

        $this->safeTable('supplier_quote_notes', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_quote_notes_supplier_id_c6ca0f26');
        });

        $this->safeTable('supplier_quote_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_quote_attachments_supplier_id_00078211');
        });

        $this->safeTable('supplier_contracts', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_contracts_supplier_id_b7cee5b3');
        });

        $this->safeTable('supplier_contacts', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_contacts_supplier_id_080e6a73');
        });

        $this->safeTable('supplier_categories', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_categories_inventory_sub_category_id_a989c7c5');
            $table->dropForeign('fk_supplier_categories_supplier_id_b44737ac');
        });

        $this->safeTable('supplier_by_categories', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_by_categories_supplier_id_48556342');
        });

        $this->safeTable('supplier_brands', function (Blueprint $table) {
            $table->dropForeign('fk_supplier_brands_inventory_sub_category_id_6d8e4317');
            $table->dropForeign('fk_supplier_brands_supplier_id_f3092c76');
        });

        $this->safeTable('submission_forms', function (Blueprint $table) {
            $table->dropForeign('fk_submission_forms_created_by_e95a368e');
        });

        $this->safeTable('submission_form_sections', function (Blueprint $table) {
            $table->dropForeign('fk_submission_form_sections_submission_form_id_0630182a');
        });

        $this->safeTable('submission_form_sample_analysis_stage', function (Blueprint $table) {
            $table->dropForeign('fk_submission_form_sample_analysis_stage_submission_fo_ad11ba44');
            $table->dropForeign('fk_submission_form_sample_analysis_stage_sample_analys_ae4d8763');
        });

        $this->safeTable('submission_form_permissions', function (Blueprint $table) {
            $table->dropForeign('sf_permissions_form_id_foreign');
            $table->dropForeign('sf_permissions_role_id_foreign');
            $table->dropForeign('sf_permissions_user_id_foreign');
        });

        $this->safeTable('submission_form_instances', function (Blueprint $table) {
            $table->dropForeign('sf_instances_form_id_foreign');
            $table->dropForeign('sf_instances_reviewed_by_foreign');
            $table->dropForeign('sf_instances_submitted_by_foreign');
        });

        $this->safeTable('submission_form_instance_values', function (Blueprint $table) {
            $table->dropForeign('sf_values_element_id_foreign');
            $table->dropForeign('sf_values_instance_id_foreign');
        });

        $this->safeTable('submission_form_elements', function (Blueprint $table) {
            $table->dropForeign('sf_elements_holder_id_foreign');
        });

        $this->safeTable('submission_form_element_holders', function (Blueprint $table) {
            $table->dropForeign('sf_element_holders_section_id_foreign');
        });

        $this->safeTable('submission_form_audit_logs', function (Blueprint $table) {
            $table->dropForeign('fk_submission_form_audit_logs_submission_form_instance_6021b7dc');
            $table->dropForeign('fk_submission_form_audit_logs_user_id_36d8a7c1');
        });

        $this->safeTable('submission_form_audit_log', function (Blueprint $table) {
            $table->dropForeign('sf_audit_instance_id_foreign');
            $table->dropForeign('sf_audit_user_id_foreign');
        });

        $this->safeTable('stock_transfers', function (Blueprint $table) {
            $table->dropForeign('fk_stock_transfers_inventory_location_id_c3d6af6b');
        });

        $this->safeTable('stock_transfer_items', function (Blueprint $table) {
            $table->dropForeign('fk_stock_transfer_items_stock_transfer_id_158032d9');
        });

        $this->safeTable('stock_takings', function (Blueprint $table) {
            $table->dropForeign('fk_stock_takings_inventory_location_id_6aff6a64');
            $table->dropForeign('fk_stock_takings_created_by');
        });

        $this->safeTable('stock_taking_sheets', function (Blueprint $table) {
            $table->dropForeign('fk_stock_taking_sheets_inventory_sub_category_id_a0c29164');
            $table->dropForeign('fk_stock_taking_sheets_stock_taking_id_96e75830');
        });

        $this->safeTable('stock_taking_counters', function (Blueprint $table) {
            $table->dropForeign('fk_stock_taking_counters_stock_taking_id_946b5769');
        });

        $this->safeTable('standards_analytes', function (Blueprint $table) {
            $table->dropForeign('fk_standards_analytes_analyte_id_1ce10517');
            $table->dropForeign('fk_standards_analytes_standard_id_d4160bfa');
            $table->dropForeign('fk_standards_analytes_standard_value_id_85cb104e');
        });

        $this->safeTable('standards', function (Blueprint $table) {
            $table->dropForeign('fk_standards_qc_type_id_854455b4');
        });

        $this->safeTable('stage_headers', function (Blueprint $table) {
            $table->dropForeign('fk_stage_headers_analyte_id_19346a7c');
            $table->dropForeign('fk_stage_headers_method_id_46186784');
            $table->dropForeign('fk_stage_headers_sample_type_id_9876e3c1');
        });

        $this->safeTable('spatie_roles', function (Blueprint $table) {
            $table->dropForeign('fk_spatie_roles_company_id_9a32a716');
        });

        $this->safeTable('spatie_role_has_permissions', function (Blueprint $table) {
            $table->dropForeign('fk_spatie_role_has_permissions_permission_id_c9388e9a');
            $table->dropForeign('fk_spatie_role_has_permissions_role_id_8803c21a');
        });

        $this->safeTable('spatie_model_has_roles', function (Blueprint $table) {
            $table->dropForeign('fk_spatie_model_has_roles_role_id_baea0445');
        });

        $this->safeTable('spatie_model_has_permissions', function (Blueprint $table) {
            $table->dropForeign('fk_spatie_model_has_permissions_permission_id_26114abc');
        });

        $this->safeTable('solution_preparations', function (Blueprint $table) {
            $table->dropForeign('fk_solution_preparations_solution_id_dd3390d4');
        });

        $this->safeTable('solution_batch_history', function (Blueprint $table) {
            $table->dropForeign('fk_solution_batch_history_solution_id_f5c4c63c');
        });

        $this->safeTable('skills_training_planner_header', function (Blueprint $table) {
            $table->dropForeign('fk_skills_training_planner_header_created_by');
        });

        $this->safeTable('skills_matrix_role_requirments', function (Blueprint $table) {
            $table->dropForeign('fk_skills_matrix_role_requirments_role_id_0ecc33c0');
        });

        $this->safeTable('skills_matrix_detail_role', function (Blueprint $table) {
            $table->dropForeign('fk_skills_matrix_detail_role_role_id_e5c99e56');
        });

        $this->safeTable('skills_capability_matrix_role', function (Blueprint $table) {
            $table->dropForeign('fk_skills_capability_matrix_role_role_id_eac5517b');
            $table->dropForeign('fk_skills_capability_matrix_role_user_id_6df6741f');
        });

        $this->safeTable('skills_capability_matrix', function (Blueprint $table) {
            $table->dropForeign('fk_skills_capability_matrix_created_by');
        });

        $this->safeTable('skill_training_header', function (Blueprint $table) {
            $table->dropForeign('fk_skill_training_header_created_by');
        });

        $this->safeTable('skill_other_training_users', function (Blueprint $table) {
            $table->dropForeign('fk_skill_other_training_users_user_id_465cabfe');
        });

        $this->safeTable('skill_capability_detail', function (Blueprint $table) {
            $table->dropForeign('fk_skill_capability_detail_user_id_2f1faebe');
        });

        $this->safeTable('severity_scales', function (Blueprint $table) {
            $table->dropForeign('fk_severity_scales_company_id_c2f016e9');
        });

        $this->safeTable('ser_testkit_worksheet_sample_relations', function (Blueprint $table) {
            $table->dropForeign('fk_tstwc_hdr_id');
        });

        $this->safeTable('ser_step_worksheet_sample_relations', function (Blueprint $table) {
            $table->dropForeign('fk_stp_hdr_id');
            $table->dropForeign('fk_ser_step_worksheet_sample_relations_equipment_id_7babb0d0');
            $table->dropForeign('fk_ser_step_worksheet_sample_relations_ser_worksheet_s_5e3b6ff7');
        });

        $this->safeTable('ser_header_worksheet_sample_relations', function (Blueprint $table) {
            $table->dropForeign('fk_ser_header_worksheet_sample_relations_analysis_type_3fbb5aac');
            $table->dropForeign('fk_ser_header_worksheet_sample_relations_sample_detail_62c83c53');
        });

        $this->safeTable('sampletype_sample_point_relation', function (Blueprint $table) {
            $table->dropForeign('fk_sampletype_sample_point_relation_sample_point_id_af6516ee');
            $table->dropForeign('fk_sampletype_sample_point_relation_sample_type_id_21358013');
        });

        $this->safeTable('sampletype_area_relation', function (Blueprint $table) {
            $table->dropForeign('fk_sampletype_area_relation_area_id_8a8d5dd5');
            $table->dropForeign('fk_sampletype_area_relation_sample_type_id_f00d6d77');
        });

        $this->safeTable('sample_worksheet_formular_step_data', function (Blueprint $table) {
            $table->dropForeign('fk_override_lookup');
            $table->dropForeign('fk_wkst_step_formula');
            $table->dropForeign('fk_sample_worksheet_formular_step_data_formula_step_id_753dd4c6');
        });

        $this->safeTable('sample_worksheet_formular_mandatory_data', function (Blueprint $table) {
            $table->dropForeign('fk_wkst_mand_formula');
            $table->dropForeign('fk_sample_worksheet_formular_mandatory_data_formula_ma_b1dc5bcc');
        });

        $this->safeTable('sample_types', function (Blueprint $table) {
            $table->dropForeign('fk_sample_types_rating_header_id_b2011103');
            $table->dropForeign('fk_sample_types_report_format_id_3756207b');
            $table->dropForeign('fk_sample_types_company_id_87f39267');
        });

        $this->safeTable('sample_type_qualifications', function (Blueprint $table) {
            $table->dropForeign('fk_sample_type_qualifications_qualification_id_f79cb613');
        });

        $this->safeTable('sample_to_sample_analysis_stages', function (Blueprint $table) {
            $table->dropForeign('fk_sample_to_sample_analysis_stages_sample_analysis_st_4df8a901');
            $table->dropForeign('fk_sample_to_sample_analysis_stages_sample_type_id_761dff80');
        });

        $this->safeTable('sample_submission_requests', function (Blueprint $table) {
            $table->dropForeign('fk_sample_submission_requests_crm_customer_id_c9e674aa');
            $table->dropForeign('fk_sample_submission_requests_sample_header_id_85abbada');
        });

        $this->safeTable('sample_submission_request_suspects', function (Blueprint $table) {
            $table->dropForeign('fk_sample_submission_request_suspects_sample_submissio_1bdd5f9f');
        });

        $this->safeTable('sample_submission_request_supporting_document_templates', function (Blueprint $table) {
            $table->dropForeign('ssr_sdoc_templates_request_fk');
            $table->dropForeign('ssr_sdoc_templates_template_fk');
        });

        $this->safeTable('sample_submission_request_requested_analyses', function (Blueprint $table) {
            $table->dropForeign('fk_sample_submission_request_requested_analyses_sample_b0b24d94');
        });

        $this->safeTable('sample_submission_request_exhibits', function (Blueprint $table) {
            $table->dropForeign('fk_sample_submission_request_exhibits_sample_detail_id_11a32e48');
            $table->dropForeign('fk_sample_submission_request_exhibits_sample_submissio_186edc74');
        });

        $this->safeTable('sample_progress', function (Blueprint $table) {
            $table->dropForeign('fk_sample_progress_analyte_id_628b0345');
            $table->dropForeign('fk_sample_progress_method_id_f95a9f19');
            $table->dropForeign('fk_sample_progress_sample_detail_id_b3d1e95e');
            $table->dropForeign('fk_sample_progress_stage_header_id_9ad48958');
            $table->dropForeign('fk_sample_progress_test_stage_id_5cb1229d');
        });

        $this->safeTable('sample_points', function (Blueprint $table) {
            $table->dropForeign('fk_sample_points_sample_point_area_id_288fec45');
            $table->dropForeign('fk_sample_points_crm_area_id_49c57678');
            $table->dropForeign('fk_sample_points_crm_company_sub_unit_id_f42a1dd4');
            $table->dropForeign('fk_sample_points_crm_company_unit_id_acbfb0fc');
            $table->dropForeign('fk_sample_points_crm_customer_id_d6d2e4fc');
            $table->dropForeign('fk_sample_points_crm_sample_point_id_9dc4db32');
        });

        $this->safeTable('sample_point_area', function (Blueprint $table) {
            $table->dropForeign('fk_sample_point_area_crm_customer_id_a7c9520c');
            $table->dropForeign('fk_sample_point_area_crm_area_id_60117a20');
            $table->dropForeign('fk_sample_point_area_crm_company_sub_unit_id_7479b6de');
            $table->dropForeign('fk_sample_point_area_crm_company_unit_id_b92fe99c');
        });

        $this->safeTable('sample_imports', function (Blueprint $table) {
            $table->dropForeign('fk_sample_imports_sample_header_id_31a30e9f');
        });

        $this->safeTable('sample_headers', function (Blueprint $table) {
            $table->dropForeign('fk_sample_headers_company_sub_unit_id_82288b8d');
            $table->dropForeign('fk_sample_headers_crm_customer_id_3906f6bc');
            $table->dropForeign('fk_sample_headers_lab_id_04539a95');
            $table->dropForeign('fk_sample_headers_qc_scheme_id_f28ed5af');
            $table->dropForeign('fk_sample_headers_qc_type_id_b96262aa');
            $table->dropForeign('fk_sample_headers_sample_header_staging_id_e7894d03');
            $table->dropForeign('fk_sample_headers_sample_type_id_37009c3a');
            $table->dropForeign('fk_sample_headers_submission_form_instance_id_0102931b');
        });

        $this->safeTable('sample_header_staging', function (Blueprint $table) {
            $table->dropForeign('fk_sample_header_staging_sample_type_id_ac1236b3');
            $table->dropForeign('fk_sample_header_staging_created_by');
        });

        $this->safeTable('sample_details', function (Blueprint $table) {
            $table->dropForeign('fk_sample_details_analysis_type_id_4bbdb281');
            $table->dropForeign('fk_sample_details_company_product_id_6a2b2201');
            $table->dropForeign('fk_sample_details_lab_id_6032c7b2');
            $table->dropForeign('fk_sample_details_reporting_unit_id_6445cee8');
            $table->dropForeign('fk_sample_details_sample_condition_id_297b33e9');
            $table->dropForeign('fk_sample_details_sample_header_id_75a450ac');
            $table->dropForeign('fk_sample_details_sample_point_id_3d478b93');
        });

        $this->safeTable('sample_detail_staging', function (Blueprint $table) {
            $table->dropForeign('fk_sample_detail_staging_sample_header_id_e4a389df');
        });

        $this->safeTable('sample_dates', function (Blueprint $table) {
            $table->dropForeign('fk_sample_dates_sample_header_id_0400ca62');
        });

        $this->safeTable('sample_conditions', function (Blueprint $table) {
            $table->dropForeign('fk_sample_conditions_sample_type_id_ee4fc7a9');
        });

        $this->safeTable('sample_captured_worksheet_formulas', function (Blueprint $table) {
            $table->dropForeign('fk_sample_captured_worksheet_formulas_captured_result_f84b2efd');
            $table->dropForeign('fk_sample_captured_worksheet_formulas_sample_detail_id_d79e98f0');
            $table->dropForeign('fk_sample_captured_worksheet_formulas_sample_header_id_bc783e08');
            $table->dropForeign('fk_sample_captured_worksheet_formulas_formular_id');
            $table->dropForeign('fk_worksheet_done_by_user');
            $table->dropForeign('fk_worksheet_read_by_user');
            $table->dropForeign('fk_worksheet_posted_by_user');
        });

        $this->safeTable('sample_approval_checklist', function (Blueprint $table) {
            $table->dropForeign('fk_sample_approval_checklist_created_by');
        });

        $this->safeTable('sample_analysis_type_relation', function (Blueprint $table) {
            $table->dropForeign('fk_sample_analysis_type_relation_analysis_type_id_09a1a145');
            $table->dropForeign('fk_sample_analysis_type_relation_sample_detail_id_31d9dff9');
            $table->dropForeign('fk_sample_analysis_type_relation_batch_id');
        });

        $this->safeTable('sample_analysis_stages', function (Blueprint $table) {
            $table->dropForeign('fk_sample_analysis_stages_company_id_cca3edf2');
            $table->dropForeign('fk_sample_analysis_stages_lab_id_dd6032a1');
        });

        $this->safeTable('sample_analysis_dates', function (Blueprint $table) {
            $table->dropForeign('fk_sample_analysis_dates_sample_detail_id_5367e6fe');
            $table->dropForeign('fk_sample_analysis_dates_sample_header_id_c88bb7fc');
        });

        $this->safeTable('samaco_sheet', function (Blueprint $table) {
            $table->dropForeign('fk_samaco_sheet_equipment_id_21279139');
        });

        $this->safeTable('root_cause_methods', function (Blueprint $table) {
            $table->dropForeign('fk_root_cause_methods_company_id_ebffa6d5');
        });

        $this->safeTable('root_cause_analyses', function (Blueprint $table) {
            $table->dropForeign('fk_root_cause_analyses_non_conformance_id_e1a6c6d0');
            $table->dropForeign('fk_root_cause_analyses_root_cause_method_id_152078fc');
            $table->dropForeign('fk_root_cause_analyses_status_id_5ff24697');
            $table->dropForeign('fk_root_cause_analyses_created_by');
        });

        $this->safeTable('roles', function (Blueprint $table) {
            $table->dropForeign('fk_roles_company_id_ac3f7bb4');
        });

        $this->safeTable('role_certifications', function (Blueprint $table) {
            $table->dropForeign('fk_role_certifications_role_id_d818fa47');
        });

        $this->safeTable('risks', function (Blueprint $table) {
            $table->dropForeign('fk_risks_audit_finding_id_df00ca43');
            $table->dropForeign('fk_risks_audit_id_23431ec5');
            $table->dropForeign('fk_risks_category_id_c2349a14');
            $table->dropForeign('fk_risks_likelihood_scale_id_4db81be6');
            $table->dropForeign('fk_risks_method_id_649bd396');
            $table->dropForeign('fk_risks_non_conformance_id_274db830');
            $table->dropForeign('fk_risks_other_source_id_b254132d');
            $table->dropForeign('fk_risks_sample_id_3ecfa1f8');
            $table->dropForeign('fk_risks_severity_scale_id_4e5d3a1f');
            $table->dropForeign('fk_risks_status_id_9032896b');
            $table->dropForeign('fk_risks_company_id_91e8fdd9');
            $table->dropForeign('fk_risks_complaint_id_3229aa4f');
            $table->dropForeign('fk_risks_equipment_id_b8697cec');
            $table->dropForeign('fk_risks_created_by');
        });

        $this->safeTable('risk_treatment_plans', function (Blueprint $table) {
            $table->dropForeign('fk_risk_treatment_plans_capa_id_9589ef67');
            $table->dropForeign('fk_risk_treatment_plans_risk_id_8a847314');
            $table->dropForeign('fk_risk_treatment_plans_treatment_type_id_3a6cb495');
            $table->dropForeign('fk_risk_treatment_plans_created_by');
        });

        $this->safeTable('risk_treatment_implementations', function (Blueprint $table) {
            $table->dropForeign('fk_risk_treatment_implementations_assessment_id_659b3668');
            $table->dropForeign('fk_risk_treatment_implementations_risk_id_832cc869');
            $table->dropForeign('fk_risk_treatment_implementations_treatment_plan_id_f85d25ea');
            $table->dropForeign('fk_risk_treatment_implementations_company_id_67b49a1c');
            $table->dropForeign('fk_risk_treatment_implementations_created_by');
        });

        $this->safeTable('risk_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_risk_statuses_company_id_4daa2f5a');
        });

        $this->safeTable('risk_sources', function (Blueprint $table) {
            $table->dropForeign('fk_risk_sources_company_id_3f026371');
        });

        $this->safeTable('risk_scoring_configs', function (Blueprint $table) {
            $table->dropForeign('fk_risk_scoring_configs_company_id_11d8bc1c');
        });

        $this->safeTable('risk_reviews', function (Blueprint $table) {
            $table->dropForeign('fk_risk_reviews_risk_id_d1946dee');
            $table->dropForeign('fk_risk_reviews_created_by');
        });

        $this->safeTable('risk_review_frequencies', function (Blueprint $table) {
            $table->dropForeign('fk_risk_review_frequencies_company_id_ae1de1c3');
        });

        $this->safeTable('risk_process_links', function (Blueprint $table) {
            $table->dropForeign('fk_risk_process_links_business_process_id_1371d30f');
            $table->dropForeign('fk_risk_process_links_risk_id_ceac4996');
            $table->dropForeign('fk_risk_process_links_created_by');
        });

        $this->safeTable('risk_notifications', function (Blueprint $table) {
            $table->dropForeign('fk_risk_notifications_company_id_c984bf84');
        });

        $this->safeTable('risk_levels', function (Blueprint $table) {
            $table->dropForeign('fk_risk_levels_company_id_62f4dc04');
        });

        $this->safeTable('risk_level_thresholds', function (Blueprint $table) {
            $table->dropForeign('fk_risk_level_thresholds_company_id_7ff6d18a');
        });

        $this->safeTable('risk_evaluations', function (Blueprint $table) {
            $table->dropForeign('fk_risk_evaluations_assessment_id_b0ac8246');
            $table->dropForeign('fk_risk_evaluations_risk_id_6d1eade0');
            $table->dropForeign('fk_risk_evaluations_company_id_420be571');
            $table->dropForeign('fk_risk_evaluations_created_by');
        });

        $this->safeTable('risk_configuration_options', function (Blueprint $table) {
            $table->dropForeign('fk_risk_configuration_options_company_id_88281b71');
            $table->dropForeign('fk_risk_configuration_options_created_by');
        });

        $this->safeTable('risk_categories', function (Blueprint $table) {
            $table->dropForeign('fk_risk_categories_company_id_95b61279');
        });

        $this->safeTable('risk_business_processes', function (Blueprint $table) {
            $table->dropForeign('fk_risk_business_processes_company_id_6a052fac');
        });

        $this->safeTable('risk_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_risk_attachments_company_id_e731fad6');
        });

        $this->safeTable('risk_assessments', function (Blueprint $table) {
            $table->dropForeign('fk_risk_assessments_likelihood_scale_id_be10f1a5');
            $table->dropForeign('fk_risk_assessments_risk_id_8f60d3c9');
            $table->dropForeign('fk_risk_assessments_severity_scale_id_3280c7b0');
            $table->dropForeign('fk_risk_assessments_company_id_6b228850');
            $table->dropForeign('fk_risk_assessments_created_by');
        });

        $this->safeTable('risk_acceptance_criteria', function (Blueprint $table) {
            $table->dropForeign('fk_risk_acceptance_criteria_company_id_b0deb05f');
        });

        $this->safeTable('results', function (Blueprint $table) {
            $table->dropForeign('fk_results_analysis_type_id_af56d81b');
            $table->dropForeign('fk_results_analyte_id_ffd54808');
            $table->dropForeign('fk_results_captured_result_id_60275229');
            $table->dropForeign('fk_results_lab_section_id');
            $table->dropForeign('fk_results_ltm_method_id');
            $table->dropForeign('fk_results_sample_detail_id_a6d7fe77');
            $table->dropForeign('fk_results_sample_header_id_7a5d0465');
        });

        $this->safeTable('request_entity_items', function (Blueprint $table) {
            $table->dropForeign('fk_request_entity_items_inventory_item_id_86a0622d');
            $table->dropForeign('fk_request_entity_items_inventory_sub_category_id_89053d61');
            $table->dropForeign('fk_request_entity_items_item_brand_id_719ceceb');
        });

        $this->safeTable('request_entities', function (Blueprint $table) {
            $table->dropForeign('fk_request_entities_inventory_location_id_baac4ff6');
            $table->dropForeign('fk_request_entities_supplier_id_326a86c6');
            $table->dropForeign('fk_request_entities_created_by');
        });

        $this->safeTable('report_header_details', function (Blueprint $table) {
            $table->dropForeign('fk_report_header_details_sample_header_id_2552a1da');
        });

        $this->safeTable('report_formats', function (Blueprint $table) {
            $table->dropForeign('fk_report_formats_company_id_7b711636');
        });

        $this->safeTable('report_format_sections', function (Blueprint $table) {
            $table->dropForeign('fk_report_format_sections_report_format_id_1e18f082');
        });

        $this->safeTable('report_format_sample_analysis_stage', function (Blueprint $table) {
            $table->dropForeign('rf_sas_format_id_fk');
            $table->dropForeign('rf_sas_stage_id_fk');
        });

        $this->safeTable('report_format_details', function (Blueprint $table) {
            $table->dropForeign('fk_report_format_details_report_format_id_c6376010');
        });

        $this->safeTable('remedy_details', function (Blueprint $table) {
            $table->dropForeign('fk_remedy_details_remedy_header_id_60f14724');
        });

        $this->safeTable('rca_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_rca_statuses_company_id_fb4c4791');
        });

        $this->safeTable('rating_details', function (Blueprint $table) {
            $table->dropForeign('fk_rating_details_rating_header_id_0066c540');
        });

        $this->safeTable('quotation_headers', function (Blueprint $table) {
            $table->dropForeign('fk_quotation_headers_crm_customer_contact_id_ec04541f');
            $table->dropForeign('fk_quotation_headers_crm_customer_id_7a859b41');
            $table->dropForeign('fk_quotation_headers_currency_id_623c064b');
            $table->dropForeign('fk_quotation_headers_pricelist_id_88831307');
        });

        $this->safeTable('quotation_details_analysis_type', function (Blueprint $table) {
            $table->dropForeign('fk_quotation_details_analysis_type_analysis_type_id_abc46297');
            $table->dropForeign('fk_quotation_details_analysis_type_quotation_detail_id_bb14845c');
        });

        $this->safeTable('quotation_details', function (Blueprint $table) {
            $table->dropForeign('fk_quotation_details_analyte_id_23fff97d');
            $table->dropForeign('fk_quotation_details_invoicable_item_id_676ef229');
            $table->dropForeign('fk_quotation_details_quotation_header_id_e74eb412');
        });

        $this->safeTable('qc_types', function (Blueprint $table) {
            $table->dropForeign('fk_qc_types_created_by');
        });

        $this->safeTable('qc_results', function (Blueprint $table) {
            $table->dropForeign('fk_qc_results_analysis_type_id_5975cf64');
            $table->dropForeign('fk_qc_results_analyte_id_78a4e4de');
            $table->dropForeign('fk_qc_results_captured_result_id_99b77d25');
            $table->dropForeign('fk_qc_results_qc_scheme_id_7e36a928');
            $table->dropForeign('fk_qc_results_qc_type_id_b96596bd');
            $table->dropForeign('fk_qc_results_result_id_95a58fdd');
            $table->dropForeign('fk_qc_results_sample_detail_id_51a69dc5');
            $table->dropForeign('fk_qc_results_sample_header_id_2ab1756a');
        });

        $this->safeTable('qc_processed_result', function (Blueprint $table) {
            $table->dropForeign('fk_qc_processed_result_analysis_type_id_17936981');
            $table->dropForeign('fk_qc_processed_result_analyte_id_847ea0d1');
            $table->dropForeign('fk_qc_processed_result_sample_type_id_93101bbe');
            $table->dropForeign('fk_qc_processed_result_standard_id_31b3bf15');
            $table->dropForeign('fk_qc_processed_result_standard_value_id_516e8a52');
        });

        $this->safeTable('qc_approvers_config', function (Blueprint $table) {
            $table->dropForeign('fk_qc_approvers_config_created_by');
        });

        $this->safeTable('procedure_worksheet_steps', function (Blueprint $table) {
            $table->dropForeign('fk_procedure_worksheet_steps_procedure_worksheet_id_23f70259');
        });

        $this->safeTable('procedure_worksheet_step_analysts', function (Blueprint $table) {
            $table->dropForeign('fk_procedure_worksheet_step_analysts_analyte_id_bd94285f');
            $table->dropForeign('fk_procedure_worksheet_step_analysts_procedure_workshe_a59794e0');
            $table->dropForeign('fk_procedure_worksheet_step_analysts_procedure_workshe_b11f1c3b');
            $table->dropForeign('fk_procedure_worksheet_step_analysts_batch_id');
        });

        $this->safeTable('procedure_test_kit_values', function (Blueprint $table) {
            $table->dropForeign('fk_procedure_test_kit_values_captured_result_id_5eb540d3');
            $table->dropForeign('fk_procedure_test_kit_values_procedure_test_kit_column_440c979c');
            $table->dropForeign('fk_procedure_test_kit_values_procedure_test_kit_row_id_95a59ff6');
        });

        $this->safeTable('procedure_test_kit_rows', function (Blueprint $table) {
            $table->dropForeign('fk_procedure_test_kit_rows_captured_result_id_458b1822');
            $table->dropForeign('fk_procedure_test_kit_rows_procedure_worksheet_id_4b8a412f');
        });

        $this->safeTable('procedure_test_kit_columns', function (Blueprint $table) {
            $table->dropForeign('fk_procedure_test_kit_columns_procedure_worksheet_id_ff38cbf1');
        });

        $this->safeTable('procedure_config_fields', function (Blueprint $table) {
            $table->dropForeign('fk_procedure_config_fields_procedure_worksheet_id_539f1c54');
        });

        $this->safeTable('pricelists', function (Blueprint $table) {
            $table->dropForeign('fk_pricelists_currency_id_c478c4e1');
        });

        $this->safeTable('pricelist_items', function (Blueprint $table) {
            $table->dropForeign('fk_pricelist_items_pricelist_id_62f2ba68');
            $table->dropForeign('fk_pricelist_items_sample_type_id_7c867377');
        });

        $this->safeTable('pricelist_customers', function (Blueprint $table) {
            $table->dropForeign('fk_pricelist_customers_pricelist_id_ccbf775c');
        });

        $this->safeTable('phone_contacts', function (Blueprint $table) {
            $table->dropForeign('fk_phone_contacts_company_id_cbd9c3f3');
        });

        $this->safeTable('personnel_work_histories', function (Blueprint $table) {
            $table->dropForeign('fk_personnel_work_histories_user_id_e5e8c97b');
        });

        $this->safeTable('personel_certifications', function (Blueprint $table) {
            $table->dropForeign('fk_personel_certifications_role_certification_id_54aa4d75');
        });

        $this->safeTable('parts_repaireds', function (Blueprint $table) {
            $table->dropForeign('fk_parts_repaireds_user_id_209c15c6');
        });

        $this->safeTable('o_t_p_s', function (Blueprint $table) {
            $table->dropForeign('fk_o_t_p_s_user_id_4eb23e05');
        });

        $this->safeTable('non_conformances', function (Blueprint $table) {
            $table->dropForeign('fk_non_conformances_audit_finding_id_2eac81eb');
            $table->dropForeign('fk_non_conformances_audit_id_3b51564e');
            $table->dropForeign('fk_non_conformances_likelihood_scale_id_f306b6b8');
            $table->dropForeign('fk_non_conformances_origin_id_693b6bf0');
            $table->dropForeign('fk_non_conformances_risk_level_id_93ea508a');
            $table->dropForeign('fk_non_conformances_severity_scale_id_79a8b53e');
            $table->dropForeign('fk_non_conformances_status_id_5c054baa');
            $table->dropForeign('fk_non_conformances_company_id_85b6a89e');
            $table->dropForeign('fk_non_conformances_equipment_id_d579c041');
            $table->dropForeign('fk_non_conformances_created_by');
        });

        $this->safeTable('nc_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_nc_statuses_company_id_fc706f40');
        });

        $this->safeTable('nc_origins', function (Blueprint $table) {
            $table->dropForeign('fk_nc_origins_company_id_44402b30');
        });

        $this->safeTable('naming_convension_consensuses', function (Blueprint $table) {
            $table->dropForeign('fk_naming_convension_consensuses_company_id_bfef9c35');
        });

        $this->safeTable('module_pre_configs_07', function (Blueprint $table) {
            $table->dropForeign('fk_module_pre_configs_07_inventory_location_id_fc74b809');
        });

        $this->safeTable('module_pre_configs', function (Blueprint $table) {
            $table->dropForeign('fk_module_pre_configs_inventory_location_id_805c2e87');
        });

        $this->safeTable('method_sequences', function (Blueprint $table) {
            $table->dropForeign('fk_method_sequences_analyte_id_99ba4b3b');
        });

        $this->safeTable('method_sequence_versions', function (Blueprint $table) {
            $table->dropForeign('fk_method_sequence_versions_method_sequence_id_40a73fea');
            $table->dropForeign('fk_method_sequence_versions_created_by');
            $table->dropForeign('fk_method_sequence_versions_approved_by');
        });

        $this->safeTable('method_sequence_stages', function (Blueprint $table) {
            $table->dropForeign('fk_method_sequence_stages_method_sequence_version_id_556e02e9');
        });

        $this->safeTable('method_sequence_stage_sample_results', function (Blueprint $table) {
            $table->dropForeign('fk_ms_samp_res_stage');
            $table->dropForeign('fk_method_sequence_stage_sample_results_captured_resul_de3fa0d2');
        });

        $this->safeTable('method_sequence_stage_media_usage', function (Blueprint $table) {
            $table->dropForeign('fk_ms_media_stage');
        });

        $this->safeTable('method_sequence_stage_equipment_usage', function (Blueprint $table) {
            $table->dropForeign('fk_ms_equip_stage');
            $table->dropForeign('fk_method_sequence_stage_equipment_usage_equipment_id_a0617a22');
        });

        $this->safeTable('method_sequence_stage_control_usage', function (Blueprint $table) {
            $table->dropForeign('fk_ms_ctrl_stage');
        });

        $this->safeTable('method_sequence_stage_control_results', function (Blueprint $table) {
            $table->dropForeign('fk_ms_ctrl_res_stage');
            $table->dropForeign('fk_ms_ctrl_res_usage');
        });

        $this->safeTable('method_sequence_runs', function (Blueprint $table) {
            $table->dropForeign('fk_method_sequence_runs_method_sequence_id_29d7cc91');
            $table->dropForeign('fk_method_sequence_runs_sample_header_id_863a6058');
        });

        $this->safeTable('method_sequence_run_stage_data', function (Blueprint $table) {
            $table->dropForeign('fk_ms_run_stage_run');
            $table->dropForeign('fk_ms_run_stage_stage_id');
            $table->dropForeign('fk_ms_run_stage_started_user');
            $table->dropForeign('fk_ms_run_stage_completed_user');
        });

        $this->safeTable('method_sequence_run_samples', function (Blueprint $table) {
            $table->dropForeign('fk_ms_run_samples_run');
            $table->dropForeign('fk_method_sequence_run_samples_captured_result_id_62e96e76');
            $table->dropForeign('fk_method_sequence_run_samples_sample_detail_id_4f13d10b');
            $table->dropForeign('fk_method_sequence_run_samples_sample_header_id_f08806d6');
        });

        $this->safeTable('method_reagents', function (Blueprint $table) {
            $table->dropForeign('fk_method_reagents_inventory_sub_category_id_5b1954ac');
        });

        $this->safeTable('maintainance_calibration_logs', function (Blueprint $table) {
            $table->dropForeign('fk_maintainance_calibration_logs_equipment_id_b95ea336');
            $table->dropForeign('fk_maintainance_calibration_logs_supplier_id_1227d389');
        });

        $this->safeTable('lookup_table_entries', function (Blueprint $table) {
            $table->dropForeign('fk_lookup_table_entries_lookup_table_id_94e237c9');
        });

        $this->safeTable('likelihood_scales', function (Blueprint $table) {
            $table->dropForeign('fk_likelihood_scales_company_id_8f5f0346');
        });

        $this->safeTable('labs', function (Blueprint $table) {
            $table->dropForeign('fk_labs_directorate_id_a78b8d76');
            $table->dropForeign('fk_labs_manager_id_92b47842');
            $table->dropForeign('fk_labs_zone_id_1eca8233');
            $table->dropForeign('fk_labs_company_id_f800269a');
        });

        $this->safeTable('lab_stock_movement', function (Blueprint $table) {
            $table->dropForeign('fk_lab_stock_movement_preparation_id_9e41c528');
            $table->dropForeign('fk_lab_stock_movement_lab_sub_category_id_574a498a');
            $table->dropForeign('fk_lab_stock_movement_created_by');
        });

        $this->safeTable('lab_section_approver_relation', function (Blueprint $table) {
            $table->dropForeign('fk_lab_section_approver_relation_user_id_23a338dc');
        });

        $this->safeTable('lab_section_approver_configuration', function (Blueprint $table) {
            $table->dropForeign('fk_lab_section_approver_configuration_user_id_47c33b35');
        });

        $this->safeTable('lab_results_excel', function (Blueprint $table) {
            $table->dropForeign('fk_lab_results_excel_analyte_id_0e9eb583');
            $table->dropForeign('fk_lab_results_excel_sample_header_id_ebf277af');
        });

        $this->safeTable('lab_inventory_category', function (Blueprint $table) {
            $table->dropForeign('fk_lab_inventory_category_company_id_977b77fc');
            $table->dropForeign('fk_lab_inventory_category_inventory_category_id_cec2b38e');
            $table->dropForeign('fk_lab_inventory_category_inventory_location_id_fc5e27a5');
        });

        $this->safeTable('lab_category_items', function (Blueprint $table) {
            $table->dropForeign('fk_lab_category_items_inventory_category_id_5cd33ec1');
            $table->dropForeign('fk_lab_category_items_inventory_item_id_61c735c4');
            $table->dropForeign('fk_lab_category_items_inventory_sub_category_id_f980538e');
            $table->dropForeign('fk_lab_category_items_item_brand_id_d4391140');
        });

        $this->safeTable('item_brands', function (Blueprint $table) {
            $table->dropForeign('fk_item_brands_inventory_sub_category_id_0cdd51ba');
        });

        $this->safeTable('iso_audits', function (Blueprint $table) {
            $table->dropForeign('fk_iso_audits_audit_type_id_ca824c7f');
            $table->dropForeign('fk_iso_audits_checklist_id_ce19dcf5');
            $table->dropForeign('fk_iso_audits_status_id_208efbcf');
            $table->dropForeign('fk_iso_audits_company_id_0f65128f');
            $table->dropForeign('fk_iso_audits_created_by');
        });

        $this->safeTable('invoice_details', function (Blueprint $table) {
            $table->dropForeign('fk_invoice_details_crm_customer_id_61e3be94');
            $table->dropForeign('fk_invoice_details_invoicable_item_id_f01e159c');
            $table->dropForeign('fk_invoice_details_sample_detail_id_74fc178b');
            $table->dropForeign('fk_invoice_details_sample_header_id_ab2c8ca5');
        });

        $this->safeTable('invoicable_items', function (Blueprint $table) {
            $table->dropForeign('fk_invoicable_items_currency_id_e77ada5f');
        });

        $this->safeTable('inventory_supplier_ratings', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_supplier_ratings_inventory_item_id_686c132c');
            $table->dropForeign('fk_inventory_supplier_ratings_supplier_id_f72c0d49');
        });

        $this->safeTable('inventory_sub_categories', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_sub_categories_company_id_e88e1013');
            $table->dropForeign('fk_inventory_sub_categories_inventory_category_id_9c931fea');
        });

        $this->safeTable('inventory_stores', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_stores_company_id_7d77fd31');
            $table->dropForeign('fk_inventory_stores_inventory_location_id_73bf547f');
        });

        $this->safeTable('inventory_store_slots', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_store_slots_inventory_store_id_d7fdda03');
        });

        $this->safeTable('inventory_store_slot_contents', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_store_slot_contents_inventory_item_id_20182cef');
            $table->dropForeign('fk_inventory_store_slot_contents_inventory_store_slot_51af8236');
            $table->dropForeign('fk_inventory_store_slot_contents_inventory_sub_categor_fd607d60');
        });

        $this->safeTable('inventory_store_contacts', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_store_contacts_user_id_fa496f66');
        });

        $this->safeTable('inventory_orders', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_orders_company_id_a8270aff');
            $table->dropForeign('fk_inventory_orders_supplier_id_f38d7d14');
            $table->dropForeign('fk_inventory_orders_created_by');
        });

        $this->safeTable('inventory_order_items', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_order_items_inventory_category_id_e792e9b3');
            $table->dropForeign('fk_inventory_order_items_inventory_order_id_78b82c90');
            $table->dropForeign('fk_inventory_order_items_inventory_sub_category_id_9a6fd5d4');
        });

        $this->safeTable('inventory_order_item_to_inventory_items', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_order_item_to_inventory_items_inventory_i_cbf53323');
            $table->dropForeign('fk_inventory_order_item_to_inventory_items_inventory_o_a57f4645');
            $table->dropForeign('fk_inventory_order_item_to_inventory_items_inventory_o_6d755b0d');
        });

        $this->safeTable('inventory_locations', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_locations_company_id_81a0dcd9');
            $table->dropForeign('fk_inventory_locations_inventory_location_id_e75cbfc6');
        });

        $this->safeTable('inventory_location_users', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_location_users_inventory_location_id_ef0dcd15');
            $table->dropForeign('fk_inventory_location_users_user_id_b839635a');
        });

        $this->safeTable('inventory_items', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_items_inventory_category_id_a8d08c22');
            $table->dropForeign('fk_inventory_items_inventory_department_id_ebcd77ec');
            $table->dropForeign('fk_inventory_items_inventory_location_id_0b81cb5b');
            $table->dropForeign('fk_inventory_items_inventory_store_id_a90b6395');
            $table->dropForeign('fk_inventory_items_inventory_store_slot_id_a009a457');
            $table->dropForeign('fk_inventory_items_inventory_sub_category_id_08d9e9b3');
            $table->dropForeign('fk_inventory_items_item_brand_id_2f67ff27');
            $table->dropForeign('fk_inventory_items_supplier_id_9e0aa447');
            $table->dropForeign('fk_inventory_items_created_by');
        });

        $this->safeTable('inventory_item_notes', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_item_notes_inventory_item_id_eee48252');
        });

        $this->safeTable('inventory_departments', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_departments_company_id_a20c72c8');
        });

        $this->safeTable('inventory_categories', function (Blueprint $table) {
            $table->dropForeign('fk_inventory_categories_company_id_13e5b591');
            $table->dropForeign('fk_inventory_categories_inventory_location_id_ecf4006b');
        });

        $this->safeTable('inspection_detail', function (Blueprint $table) {
            $table->dropForeign('fk_inspection_detail_created_by');
        });

        $this->safeTable('import_lab_results', function (Blueprint $table) {
            $table->dropForeign('fk_import_lab_results_analyte_id_1d31291e');
            $table->dropForeign('fk_import_lab_results_batch_id');
        });

        $this->safeTable('general_requistion_requests', function (Blueprint $table) {
            $table->dropForeign('fk_general_requistion_requests_created_by');
        });

        $this->safeTable('general_requisition_supplier_quotes', function (Blueprint $table) {
            $table->dropForeign('fk_general_requisition_supplier_quotes_supplier_id_df67df4f');
        });

        $this->safeTable('general_requisition_request_items', function (Blueprint $table) {
            $table->dropForeign('fk_general_requisition_request_items_supplier_id_ff6f4027');
        });

        $this->safeTable('formula_versions', function (Blueprint $table) {
            $table->dropForeign('fk_formula_versions_approved_by_018dfed1');
            $table->dropForeign('fk_formula_versions_created_by_d020a4f5');
            $table->dropForeign('fk_formula_versions_formula_id_7c1ff219');
        });

        $this->safeTable('formula_steps', function (Blueprint $table) {
            $table->dropForeign('fk_formula_steps_analyte_id_0eee2df8');
            $table->dropForeign('fk_formula_steps_formula_version_id_1fd80d95');
        });

        $this->safeTable('formula_mandatory_fields', function (Blueprint $table) {
            $table->dropForeign('fk_formula_mandatory_fields_formula_version_id_e43a55d8');
        });

        $this->safeTable('form_templates', function (Blueprint $table) {
            $table->dropForeign('fk_form_templates_created_by_ae048d27');
        });

        $this->safeTable('form_template_variables', function (Blueprint $table) {
            $table->dropForeign('fk_form_template_variables_created_by_6ba5ff42');
            $table->dropForeign('fk_form_template_variables_form_template_id_bea66487');
        });

        $this->safeTable('form_fields', function (Blueprint $table) {
            $table->dropForeign('fk_form_fields_form_template_id_fb6faee5');
            $table->dropForeign('fk_form_fields_parent_field_id_739ee42c');
            $table->dropForeign('fk_form_fields_parent_id_729e8c68');
        });

        $this->safeTable('finding_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_finding_statuses_company_id_45a0f0b9');
        });

        $this->safeTable('finding_categories', function (Blueprint $table) {
            $table->dropForeign('fk_finding_categories_company_id_c33bfaf4');
        });

        $this->safeTable('feedback_requests', function (Blueprint $table) {
            $table->dropForeign('fk_feedback_requests_contact_id_5227f2e9');
            $table->dropForeign('fk_feedback_requests_feedback_id_1799cc3e');
            $table->dropForeign('fk_feedback_requests_company_id_4a70275d');
        });

        $this->safeTable('equipment_usage', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_usage_equipment_id_a560c5d6');
        });

        $this->safeTable('equipment_operators', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_operators_equipment_id_2dbbbdb8');
            $table->dropForeign('fk_equipment_operators_user_id_2b6edac5');
        });

        $this->safeTable('equipment_notification', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_notification_equipment_id_f3ba4755');
        });

        $this->safeTable('equipment_evaluations', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_evaluations_equipment_id_3a40c184');
            $table->dropForeign('fk_equipment_evaluations_evaluated_by_63e6f54d');
            $table->dropForeign('fk_equipment_evaluations_company_id_f567fb86');
        });

        $this->safeTable('equipment_disposals', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_disposals_decommissioned_by_03cd3051');
            $table->dropForeign('fk_equipment_disposals_equipment_id_2979efd1');
            $table->dropForeign('fk_equipment_disposals_evaluation_id_0f62680e');
            $table->dropForeign('fk_equipment_disposals_executed_by_d61901f5');
            $table->dropForeign('fk_equipment_disposals_requested_by_9af021ec');
            $table->dropForeign('fk_equipment_disposals_witness_id_410c4b6a');
            $table->dropForeign('fk_equipment_disposals_company_id_9087049c');
        });

        $this->safeTable('equipment_disposal_files', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_disposal_files_disposal_id_b9a6df72');
            $table->dropForeign('fk_equipment_disposal_files_uploaded_by_bb963d2e');
        });

        $this->safeTable('equipment_disposal_audit_logs', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_disposal_audit_logs_disposal_id_f88da1a2');
            $table->dropForeign('fk_equipment_disposal_audit_logs_user_id_fd800541');
        });

        $this->safeTable('equipment_disposal_approvals', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_disposal_approvals_approver_id_6bfb8073');
            $table->dropForeign('fk_equipment_disposal_approvals_disposal_id_601a65ca');
        });

        $this->safeTable('equipment_disposal_approval_workflows', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_disposal_approval_workflows_created_by_267693a0');
            $table->dropForeign('fk_equipment_disposal_approval_workflows_equipment_typ_9ddba46a');
            $table->dropForeign('fk_equipment_disposal_approval_workflows_location_id_944a1db3');
            $table->dropForeign('fk_equipment_disposal_approval_workflows_company_id_3f4e6918');
        });

        $this->safeTable('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_disposal_approval_workflow_steps_workflow_1b1d9c8b');
        });

        $this->safeTable('equipment_daily_log_entries', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_daily_log_entries_equipment_id_97932801');
            $table->dropForeign('fk_equipment_daily_log_entries_company_id_29fa31ca');
        });

        $this->safeTable('equipment_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_attachments_equipment_id_a8d18663');
        });

        $this->safeTable('equipment', function (Blueprint $table) {
            $table->dropForeign('fk_equipment_asset_location_id_c89b91e1');
            $table->dropForeign('fk_equipment_asset_type_id_20cb530a');
            $table->dropForeign('fk_equipment_company_id_ebb721d6');
            $table->dropForeign('fk_equipment_inventory_item_id_3b735850');
            $table->dropForeign('fk_equipment_lab_id_ff873d8e');
        });

        $this->safeTable('entity_notes', function (Blueprint $table) {
            $table->dropForeign('fk_entity_notes_created_by');
        });

        $this->safeTable('entity_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_entity_attachments_created_by');
        });

        $this->safeTable('entity_approvals', function (Blueprint $table) {
            $table->dropForeign('fk_entity_approvals_approval_id_cc2f5119');
            $table->dropForeign('fk_entity_approvals_inventory_location_id_ae45f704');
            $table->dropForeign('fk_entity_approvals_user_id_2217f2a2');
        });

        $this->safeTable('documents', function (Blueprint $table) {
            $table->dropForeign('fk_documents_approved_by_087cc358');
            $table->dropForeign('fk_documents_archived_by_6999a458');
            $table->dropForeign('fk_documents_created_by_ba25b021');
            $table->dropForeign('fk_documents_document_type_id_5c10dc63');
            $table->dropForeign('fk_documents_owner_id_a7abd97d');
        });

        $this->safeTable('document_versions', function (Blueprint $table) {
            $table->dropForeign('fk_document_versions_created_by_705fce77');
            $table->dropForeign('fk_document_versions_document_id_02518f89');
        });

        $this->safeTable('document_types', function (Blueprint $table) {
            $table->dropForeign('fk_document_types_created_by_a1df2b1f');
            $table->dropForeign('fk_document_types_parent_id_f5ed952b');
        });

        $this->safeTable('document_permissions', function (Blueprint $table) {
            $table->dropForeign('fk_document_permissions_granted_by_c7efa302');
        });

        $this->safeTable('document_notifications', function (Blueprint $table) {
            $table->dropForeign('fk_document_notifications_document_id_5687ddfe');
            $table->dropForeign('fk_document_notifications_user_id_c868cf66');
        });

        $this->safeTable('document_expiry_notification_settings', function (Blueprint $table) {
            $table->dropForeign('fk_document_expiry_notification_settings_user_id_9c49d225');
        });

        $this->safeTable('document_audit_logs', function (Blueprint $table) {
            $table->dropForeign('fk_document_audit_logs_user_id_e5f1505c');
        });

        $this->safeTable('document_approval_workflows', function (Blueprint $table) {
            $table->dropForeign('fk_document_approval_workflows_created_by_77cf15e3');
            $table->dropForeign('fk_document_approval_workflows_document_type_id_c5f3d142');
        });

        $this->safeTable('document_approval_workflow_steps', function (Blueprint $table) {
            $table->dropForeign('fk_document_approval_workflow_steps_workflow_id_3b8db626');
        });

        $this->safeTable('document_amendments', function (Blueprint $table) {
            $table->dropForeign('fk_document_amendments_amended_by_16756694');
            $table->dropForeign('fk_document_amendments_approved_by_325ebda9');
            $table->dropForeign('fk_document_amendments_authorized_by_70873aab');
            $table->dropForeign('fk_document_amendments_document_id_12f208c4');
            $table->dropForeign('fk_document_amendments_requested_by_92236343');
        });

        $this->safeTable('directorates', function (Blueprint $table) {
            $table->dropForeign('fk_directorates_head_id_1092a617');
            $table->dropForeign('fk_directorates_zone_id_b8b22de7');
        });

        $this->safeTable('customerqualifications', function (Blueprint $table) {
            $table->dropForeign('fk_customerqualifications_qualification_id_89f70621');
        });

        $this->safeTable('customerfeedbacks', function (Blueprint $table) {
            $table->dropForeign('fk_customerfeedbacks_contact_id_0b4f7503');
            $table->dropForeign('fk_customerfeedbacks_customer_id_8001c3c9');
        });

        $this->safeTable('customer_submission_form_columns', function (Blueprint $table) {
            $table->dropForeign('fk_customer_submission_form_columns_crm_customer_id_8391a076');
        });

        $this->safeTable('customer_invoice', function (Blueprint $table) {
            $table->dropForeign('fk_customer_invoice_currency_id_0e6e5a88');
            $table->dropForeign('fk_customer_invoice_pricelist_id_73d1b366');
            $table->dropForeign('fk_customer_invoice_zoho_customer_id_d1589ead');
        });

        $this->safeTable('custom_field_category_customers', function (Blueprint $table) {
            $table->dropForeign('fk_custom_field_category_customers_crm_customer_id_0f1f11c5');
        });

        $this->safeTable('currency_conversions', function (Blueprint $table) {
            $table->dropForeign('fk_currency_conversions_inventory_location_id_b8466137');
        });

        $this->safeTable('crm_sample_points', function (Blueprint $table) {
            $table->dropForeign('fk_crm_sample_points_created_by');
        });

        $this->safeTable('crm_report_info_columns', function (Blueprint $table) {
            $table->dropForeign('fk_crm_report_info_columns_crm_customer_id_edc2a5dc');
        });

        $this->safeTable('crm_feedback_ratings', function (Blueprint $table) {
            $table->dropForeign('fk_crm_feedback_ratings_customer_feedback_id_07e530aa');
            $table->dropForeign('fk_crm_feedback_ratings_evaluation_metric_id_1a3865e9');
        });

        $this->safeTable('crm_customers', function (Blueprint $table) {
            $table->dropForeign('fk_crm_customers_company_id_6e16447d');
            $table->dropForeign('fk_crm_customers_country_id_2e574f72');
            $table->dropForeign('fk_crm_customers_currency_id_83bed6d4');
            $table->dropForeign('fk_crm_customers_lab_id_7bbf6b5a');
            $table->dropForeign('fk_crm_customers_zoho_customer_id_bbf97fa2');
        });

        $this->safeTable('crm_customer_contacts', function (Blueprint $table) {
            $table->dropForeign('fk_crm_customer_contacts_company_id_29b1c7dc');
            $table->dropForeign('fk_crm_customer_contacts_crm_customer_id_5afc7ba0');
            $table->dropForeign('fk_crm_customer_contacts_crm_company_unit_id');
        });

        $this->safeTable('crm_company_units', function (Blueprint $table) {
            $table->dropForeign('fk_crm_company_units_crm_company_section_id_51fb10eb');
            $table->dropForeign('fk_crm_company_units_company_id_07c30929');
            $table->dropForeign('fk_crm_company_units_crm_customer_id_72d7152d');
        });

        $this->safeTable('crm_company_sub_units', function (Blueprint $table) {
            $table->dropForeign('fk_crm_company_sub_units_crm_company_unit_id_b912448f');
            $table->dropForeign('fk_crm_company_sub_units_crm_customer_id_fe5a66b5');
        });

        $this->safeTable('crm_company_sections', function (Blueprint $table) {
            $table->dropForeign('fk_crm_company_sections_crm_customer_id_81f517eb');
            $table->dropForeign('fk_crm_company_sections_company_id_6771526d');
        });

        $this->safeTable('crm_areas', function (Blueprint $table) {
            $table->dropForeign('fk_crm_areas_created_by');
        });

        $this->safeTable('corrective_actions', function (Blueprint $table) {
            $table->dropForeign('fk_corrective_actions_action_type_id_ec968186');
            $table->dropForeign('fk_corrective_actions_capa_category_id_56b8c0c6');
            $table->dropForeign('fk_corrective_actions_non_conformance_id_b8706d63');
            $table->dropForeign('fk_corrective_actions_priority_id_d17bed10');
            $table->dropForeign('fk_corrective_actions_status_id_8e89b6b0');
            $table->dropForeign('fk_corrective_actions_company_id_9d86642a');
            $table->dropForeign('fk_corrective_actions_created_by');
        });

        $this->safeTable('conversation', function (Blueprint $table) {
            $table->dropForeign('fk_conversation_company_id_cd133da9');
        });

        $this->safeTable('compliance_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_compliance_statuses_company_id_b0216bae');
        });

        $this->safeTable('complaintsresolutions', function (Blueprint $table) {
            $table->dropForeign('fk_complaintsresolutions_resolved_by_user_id_3d6a7e9b');
            $table->dropForeign('fk_complaintsresolutions_complaint_id_9de8d7d2');
        });

        $this->safeTable('complaints', function (Blueprint $table) {
            $table->dropForeign('fk_complaints_closed_by_88d82921');
            $table->dropForeign('fk_complaints_escalated_from_user_id_b3bcc4ed');
            $table->dropForeign('fk_complaints_escalated_to_user_id_71f14b27');
            $table->dropForeign('fk_complaints_intake_approved_by_cf863675');
            $table->dropForeign('fk_complaints_ticket_category_id_6c44710b');
        });

        $this->safeTable('complaintnotes', function (Blueprint $table) {
            $table->dropForeign('fk_complaintnotes_complaint_id_204e6b5b');
        });

        $this->safeTable('complaintattachments', function (Blueprint $table) {
            $table->dropForeign('fk_complaintattachments_complaint_id_4cebbd95');
        });

        $this->safeTable('company_products', function (Blueprint $table) {
            $table->dropForeign('fk_company_products_crm_company_unit_id_34fa1d2f');
        });

        $this->safeTable('companies', function (Blueprint $table) {
            $table->dropForeign('fk_companies_country_id_34dce04f');
        });

        $this->safeTable('chat_message', function (Blueprint $table) {
            $table->dropForeign('fk_chat_message_company_id_b892717e');
            $table->dropForeign('fk_chat_message_conversation_id_2f3ae54c');
        });

        $this->safeTable('chain_of_custody_complaints', function (Blueprint $table) {
            $table->dropForeign('fk_chain_of_custody_complaints_complaint_id_781161b7');
        });

        $this->safeTable('chain_of_custodies', function (Blueprint $table) {
            $table->dropForeign('fk_chain_of_custodies_sample_header_id_addc66de');
        });

        $this->safeTable('certificate_templates', function (Blueprint $table) {
            $table->dropForeign('ct_templates_created_by_fk');
            $table->dropForeign('ct_templates_submission_form_fk');
        });

        $this->safeTable('certificate_template_sections', function (Blueprint $table) {
            $table->dropForeign('ct_sections_parent_fk');
            $table->dropForeign('ct_sections_template_fk');
        });

        $this->safeTable('certificate_template_reports', function (Blueprint $table) {
            $table->dropForeign('ct_reports_generated_by_fk');
            $table->dropForeign('ct_reports_instance_fk');
            $table->dropForeign('ct_reports_template_fk');
        });

        $this->safeTable('certificate_template_elements', function (Blueprint $table) {
            $table->dropForeign('ct_elements_section_fk');
            $table->dropForeign('fk_certificate_template_elements_certificate_template_dfc97abd');
        });

        $this->safeTable('certificate_template_element_holders', function (Blueprint $table) {
            $table->dropForeign('fk_certificate_template_element_holders_parent_holder_730437f6');
            $table->dropForeign('ct_elem_holders_section_fk');
        });

        $this->safeTable('captured_view', function (Blueprint $table) {
            $table->dropForeign('fk_captured_view_analysis_type_id_96611ddd');
            $table->dropForeign('fk_captured_view_analyte_id_708f95df');
            $table->dropForeign('fk_captured_view_equipment_id_582990f4');
            $table->dropForeign('fk_captured_view_reporting_unit_id_e6c3b7e4');
            $table->dropForeign('fk_captured_view_sample_detail_id_25f57e7d');
            $table->dropForeign('fk_captured_view_sample_header_id_2457014c');
            $table->dropForeign('fk_captured_view_user_id_5b454118');
        });

        $this->safeTable('captured_results', function (Blueprint $table) {
            $table->dropForeign('fk_captured_results_analysis_element_id_60f5f36e');
            $table->dropForeign('fk_captured_results_analysis_type_id_58a53b2e');
            $table->dropForeign('fk_captured_results_analyte_id_9470012c');
            $table->dropForeign('fk_captured_results_batch_attachment_id_69c1960f');
            $table->dropForeign('fk_captured_results_equipment_id_21ffab5e');
            $table->dropForeign('fk_captured_results_method_sequence_id_003f0cf3');
            $table->dropForeign('fk_captured_results_procedure_worksheet_id_fa59b1b1');
            $table->dropForeign('fk_captured_results_reporting_unit_id_8dadf385');
            $table->dropForeign('fk_captured_results_sample_detail_id_1440397f');
            $table->dropForeign('fk_captured_results_sample_header_id_602cf157');
            $table->dropForeign('fk_captured_results_stage_header_id_81f6c2e4');
            $table->dropForeign('fk_captured_results_user_id_20ffa6bc');
        });

        $this->safeTable('captured_procedure_values', function (Blueprint $table) {
            $table->dropForeign('fk_captured_procedure_values_captured_result_id_0b37e1e1');
            $table->dropForeign('fk_captured_procedure_values_procedure_worksheet_step_a989f116');
        });

        $this->safeTable('captured_procedure_config_values', function (Blueprint $table) {
            $table->dropForeign('fk_captured_procedure_config_values_captured_result_id_157a0db9');
            $table->dropForeign('fk_captured_procedure_config_values_procedure_config_f_e9d35992');
            $table->dropForeign('fk_captured_procedure_config_values_procedure_workshee_3a3e78c6');
        });

        $this->safeTable('capa_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_capa_statuses_company_id_83e5513e');
        });

        $this->safeTable('capa_priorities', function (Blueprint $table) {
            $table->dropForeign('fk_capa_priorities_company_id_757b9b17');
        });

        $this->safeTable('capa_categories', function (Blueprint $table) {
            $table->dropForeign('fk_capa_categories_company_id_7ca826ae');
        });

        $this->safeTable('capa_action_types', function (Blueprint $table) {
            $table->dropForeign('fk_capa_action_types_company_id_aa4c2386');
        });

        $this->safeTable('calendarevents_notifications', function (Blueprint $table) {
            $table->dropForeign('fk_calendarevents_notifications_calendar_event_id_fc31581d');
        });

        $this->safeTable('calendar_events', function (Blueprint $table) {
            $table->dropForeign('fk_calendar_events_created_by');
        });

        $this->safeTable('batch_sequences', function (Blueprint $table) {
            $table->dropForeign('batch_seq_instance_id_foreign');
        });

        $this->safeTable('batch_notifications', function (Blueprint $table) {
            $table->dropForeign('fk_batch_notifications_batch_id');
            $table->dropForeign('fk_batch_notifications_created_by');
        });

        $this->safeTable('batch_labsection_approval', function (Blueprint $table) {
            $table->dropForeign('fk_batch_labsection_approval_user_id_51dabab5');
            $table->dropForeign('fk_batch_labsection_approval_batch_id');
        });

        $this->safeTable('batch_comments', function (Blueprint $table) {
            $table->dropForeign('fk_batch_comments_sample_header_id_6ca4c5ac');
            $table->dropForeign('fk_batch_comments_created_by');
        });

        $this->safeTable('batch_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_batch_attachments_batch_id');
        });

        $this->safeTable('batch_attachment_annotations', function (Blueprint $table) {
            $table->dropForeign('fk_batch_attachment_annotations_batch_attachment_id_15e491e9');
        });

        $this->safeTable('batch_approval_checklist', function (Blueprint $table) {
            $table->dropForeign('fk_batch_approval_checklist_approval_id_e81b8627');
        });

        $this->safeTable('batch_ammendments', function (Blueprint $table) {
            $table->dropForeign('fk_batch_ammendments_batch_id');
            $table->dropForeign('fk_batch_ammendments_created_by_id');
        });

        $this->safeTable('audits', function (Blueprint $table) {
            $table->dropForeign('fk_audits_user_id_19ab19d2');
        });

        $this->safeTable('audit_workflow_approvers', function (Blueprint $table) {
            $table->dropForeign('fk_audit_workflow_approvers_user_id_e43ca3f6');
            $table->dropForeign('fk_audit_workflow_approvers_company_id_44b60215');
        });

        $this->safeTable('audit_types', function (Blueprint $table) {
            $table->dropForeign('fk_audit_types_company_id_87624143');
        });

        $this->safeTable('audit_team_roles', function (Blueprint $table) {
            $table->dropForeign('fk_audit_team_roles_company_id_8479fdfe');
        });

        $this->safeTable('audit_team_members', function (Blueprint $table) {
            $table->dropForeign('fk_audit_team_members_audit_id_e79981bc');
            $table->dropForeign('fk_audit_team_members_role_id_8db54710');
            $table->dropForeign('fk_audit_team_members_user_id_9f975f5e');
        });

        $this->safeTable('audit_statuses', function (Blueprint $table) {
            $table->dropForeign('fk_audit_statuses_company_id_e142bc09');
        });

        $this->safeTable('audit_notifications', function (Blueprint $table) {
            $table->dropForeign('fk_audit_notifications_notification_type_id_d6765bc3');
            $table->dropForeign('fk_audit_notifications_company_id_45aa558e');
        });

        $this->safeTable('audit_notification_types', function (Blueprint $table) {
            $table->dropForeign('fk_audit_notification_types_company_id_316b9c3d');
        });

        $this->safeTable('audit_module_findings', function (Blueprint $table) {
            $table->dropForeign('fk_audit_module_findings_audit_id_c06ca483');
            $table->dropForeign('fk_audit_module_findings_finding_category_id_6d005db6');
            $table->dropForeign('fk_audit_module_findings_risk_level_id_e0f3c164');
            $table->dropForeign('fk_audit_module_findings_status_id_89f8ae96');
            $table->dropForeign('fk_audit_module_findings_created_by');
        });

        $this->safeTable('audit_email_templates', function (Blueprint $table) {
            $table->dropForeign('fk_audit_email_templates_company_id_243ca6fd');
        });

        $this->safeTable('audit_checklists', function (Blueprint $table) {
            $table->dropForeign('fk_audit_checklists_audit_type_id_78022d85');
            $table->dropForeign('fk_audit_checklists_company_id_52b583e1');
            $table->dropForeign('fk_audit_checklists_created_by');
        });

        $this->safeTable('audit_checklist_items', function (Blueprint $table) {
            $table->dropForeign('fk_audit_checklist_items_audit_checklist_id_35b1b989');
        });

        $this->safeTable('audit_checklist_audit', function (Blueprint $table) {
            $table->dropForeign('fk_audit_checklist_audit_audit_checklist_id_ca5c2996');
            $table->dropForeign('fk_audit_checklist_audit_audit_id_5ad9b77a');
        });

        $this->safeTable('audit_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_audit_attachments_attachment_type_id_1ae5dc97');
            $table->dropForeign('fk_audit_attachments_company_id_a2bf5550');
        });

        $this->safeTable('attachment_types', function (Blueprint $table) {
            $table->dropForeign('fk_attachment_types_company_id_b814397a');
        });

        $this->safeTable('approvals', function (Blueprint $table) {
            $table->dropForeign('fk_approvals_inventory_location_id_f6594aa9');
            $table->dropForeign('fk_approvals_role_id_a3ff9e9b');
        });

        $this->safeTable('analytes', function (Blueprint $table) {
            $table->dropForeign('fk_analytes_company_id_d5cb8dcd');
            $table->dropForeign('fk_analytes_equipment_id_85658cec');
        });

        $this->safeTable('analysis_types', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_types_company_id_1ce9c588');
            $table->dropForeign('fk_analysis_types_lab_id_fd913615');
            $table->dropForeign('fk_analysis_types_procedure_worksheet_id_0710fe93');
            $table->dropForeign('fk_analysis_types_sample_type_id_b9228794');
        });

        $this->safeTable('analysis_type_invoicable_item', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_type_invoicable_item_analysis_type_id_83c7af8b');
            $table->dropForeign('fk_analysis_type_invoicable_item_invoicable_item_id_b9ecb23e');
        });

        $this->safeTable('analysis_methods', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_methods_company_id_800d5758');
            $table->dropForeign('fk_analysis_methods_sample_header_id_1ab3181c');
        });

        $this->safeTable('analysis_method_elements', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_method_elements_analysis_method_id_9a29a709');
            $table->dropForeign('fk_analysis_method_elements_analyte_id_437636e5');
            $table->dropForeign('fk_analysis_method_elements_company_id_85f7ffa2');
        });

        $this->safeTable('analysis_guides', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_guides_analysis_type_id_e15f1068');
            $table->dropForeign('fk_analysis_guides_analyte_id_cb7db970');
            $table->dropForeign('fk_analysis_guides_standard_id_b2c82c24');
            $table->dropForeign('fk_analysis_guides_standard_value_id_a7efc6ec');
        });

        $this->safeTable('analysis_elements', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_elements_method_sequence_id_a8e0950f');
            $table->dropForeign('fk_analysis_elements_remedy_header_id_ba5aa4bf');
            $table->dropForeign('fk_analysis_elements_analysis_type_id_bba30d9d');
            $table->dropForeign('fk_analysis_elements_analyte_id_eec905de');
            $table->dropForeign('fk_analysis_elements_company_id_b4f06a92');
            $table->dropForeign('fk_analysis_elements_equipment_id_b3fd69bd');
            $table->dropForeign('fk_analysis_elements_procedure_worksheet_id_c6c26d38');
        });

        $this->safeTable('ai_messages', function (Blueprint $table) {
            $table->dropForeign('fk_ai_messages_ai_conversation_id_c3ddd1d0');
            $table->dropForeign('fk_ai_messages_parent_message_id_33553798');
        });

        $this->safeTable('ai_conversations', function (Blueprint $table) {
            $table->dropForeign('fk_ai_conversations_user_id_99a7c1e5');
        });

        $this->safeTable('ai_chat_attachments', function (Blueprint $table) {
            $table->dropForeign('fk_ai_chat_att_convo');
            $table->dropForeign('fk_ai_chat_att_msg');
        });

        $this->safeTable('ai_analytics_logs', function (Blueprint $table) {
            $table->dropForeign('fk_ai_analytics_logs_user_id_2b7ea81e');
        });

        $this->safeTable('ai_action_logs', function (Blueprint $table) {
            $table->dropForeign('fk_ai_action_logs_user_id_e475dafb');
        });
    }

    private function safeTable(string $tableName, \Closure $callback): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        try {
            Schema::table($tableName, $callback);
        } catch (\Throwable $e) {
            // Some environments intentionally miss referenced tables/constraints.
            // Skip to keep this bulk conversion migration idempotent.
        }
    }
};