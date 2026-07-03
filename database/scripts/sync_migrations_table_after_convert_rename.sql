-- Sync Laravel migrations table after convert/ batch filename rename
-- Run against an EXISTING database that already executed the OLD filenames.
--
-- BEFORE running:
--   1. Backup: CREATE TABLE migrations_backup AS SELECT * FROM migrations;
--   2. Do NOT run php artisan migrate until this script completes.
--
-- AFTER running:
--   SELECT COUNT(*) FROM migrations WHERE migration LIKE '2026_04_23_211%';  -- expect 0
--   php artisan migrate:status | grep Pending  -- convert batch should not be Pending
--
-- Total updates: 406

BEGIN;

-- Remove duplicate rows if migrate was already run with NEW names (failed/partial).
-- Keeps the OLD-name row; delete NEW-name row only when OLD also exists.
DELETE FROM migrations
WHERE migration = '2026_02_05_200000_create_ai_action_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211221_create_ai_action_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200001_create_ai_analytics_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211222_create_ai_analytics_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200002_create_ai_chat_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211223_create_ai_chat_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200003_create_ai_conversations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211224_create_ai_conversations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200004_create_ai_feature_snapshots_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211225_create_ai_feature_snapshots_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200005_create_ai_messages_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211226_create_ai_messages_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200006_create_ai_model_registry_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211227_create_ai_model_registry_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200007_create_analysis_elements_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211228_create_analysis_elements_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200008_create_analysis_guides_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211229_create_analysis_guides_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200009_create_analysis_method_elements_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211230_create_analysis_method_elements_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200010_create_analysis_methods_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211231_create_analysis_methods_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200011_create_analysis_type_invoicable_item_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211232_create_analysis_type_invoicable_item_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200012_create_analysis_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211233_create_analysis_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200013_create_analytes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211234_create_analytes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200014_create_companies_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211235_create_companies_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200015_create_approvals_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211236_create_approvals_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200016_create_asset_locations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211237_create_asset_locations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200017_create_asset_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211238_create_asset_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200018_create_attachment_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211239_create_attachment_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200019_create_audit_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211240_create_audit_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200020_create_audit_checklist_audit_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211241_create_audit_checklist_audit_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200021_create_audit_checklist_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211242_create_audit_checklist_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200022_create_audit_checklists_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211243_create_audit_checklists_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200023_create_audit_email_templates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211244_create_audit_email_templates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200024_create_audit_module_findings_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211245_create_audit_module_findings_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200025_create_audit_notification_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211246_create_audit_notification_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200026_create_audit_notifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211247_create_audit_notifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200027_create_audit_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211248_create_audit_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200028_create_audit_team_members_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211249_create_audit_team_members_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200029_create_audit_team_roles_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211250_create_audit_team_roles_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200030_create_audit_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211251_create_audit_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200031_create_audit_workflow_approvers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211252_create_audit_workflow_approvers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200032_create_audits_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211253_create_audits_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200033_create_batch_approval_checklist_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211254_create_batch_approval_checklist_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200034_create_batch_attachment_annotations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211255_create_batch_attachment_annotations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200035_create_batch_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211256_create_batch_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200036_create_batch_comments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211257_create_batch_comments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200037_create_batch_labsection_approval_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211258_create_batch_labsection_approval_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200038_create_batch_notifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211259_create_batch_notifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200039_create_batch_sequences_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211300_create_batch_sequences_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200040_create_calendar_events_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211301_create_calendar_events_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200041_create_calendarevents_notifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211302_create_calendarevents_notifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200042_create_capa_action_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211303_create_capa_action_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200043_create_capa_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211304_create_capa_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200044_create_capa_priorities_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211305_create_capa_priorities_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200045_create_capa_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211306_create_capa_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200046_create_captured_procedure_config_values_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211307_create_captured_procedure_config_values_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200047_create_captured_procedure_values_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211308_create_captured_procedure_values_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200048_create_captured_view_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211309_create_captured_view_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200049_create_certificate_template_element_holders_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211310_create_certificate_template_element_holders_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200050_create_certificate_template_elements_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211311_create_certificate_template_elements_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200051_create_certificate_template_reports_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211312_create_certificate_template_reports_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200052_create_certificate_template_sections_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211313_create_certificate_template_sections_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200053_create_certificate_templates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211314_create_certificate_templates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200054_create_chain_of_custodies_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211315_create_chain_of_custodies_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200055_create_chain_of_custody_complaints_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211316_create_chain_of_custody_complaints_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200056_create_chart_of_accounts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211317_create_chart_of_accounts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200057_create_chat_message_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211318_create_chat_message_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200058_create_company_products_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211320_create_company_products_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200059_create_complaint_type_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211321_create_complaint_type_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200100_create_complaintattachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211322_create_complaintattachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200101_create_complaintnotes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211323_create_complaintnotes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200102_create_complaints_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211324_create_complaints_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200103_create_complaintsresolutions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211325_create_complaintsresolutions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200104_create_compliance_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211326_create_compliance_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200105_create_contact_phones_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211327_create_contact_phones_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200106_create_conversation_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211328_create_conversation_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200107_create_corrective_actions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211329_create_corrective_actions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200108_create_countries_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211330_create_countries_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200109_create_crm_areas_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211331_create_crm_areas_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200110_create_crm_company_sections_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211332_create_crm_company_sections_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200111_create_crm_company_sub_units_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211333_create_crm_company_sub_units_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200112_create_crm_company_units_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211334_create_crm_company_units_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200113_create_crm_customer_contacts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211335_create_crm_customer_contacts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200114_create_crm_customers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211336_create_crm_customers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200115_create_crm_evaluation_metrics_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211337_create_crm_evaluation_metrics_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200116_create_crm_feedback_ratings_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211338_create_crm_feedback_ratings_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200117_create_crm_report_info_columns_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211339_create_crm_report_info_columns_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200118_create_crm_sample_points_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211340_create_crm_sample_points_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200119_create_currencies_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211341_create_currencies_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200120_create_currency_conversions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211342_create_currency_conversions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200121_create_custom_field_category_customers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211343_create_custom_field_category_customers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200122_create_customer_invoice_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211344_create_customer_invoice_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200123_create_customer_submission_form_columns_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211345_create_customer_submission_form_columns_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200124_create_customerfeedbacks_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211346_create_customerfeedbacks_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200125_create_customerqualifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211347_create_customerqualifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200126_create_directorates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211348_create_directorates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200127_create_disposal_reasons_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211349_create_disposal_reasons_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200128_create_document_amendments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211350_create_document_amendments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200129_create_document_approval_workflow_steps_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211351_create_document_approval_workflow_steps_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200130_create_document_approval_workflows_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211352_create_document_approval_workflows_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200131_create_document_audit_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211353_create_document_audit_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200132_create_document_expiry_notification_settings_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211354_create_document_expiry_notification_settings_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200133_create_document_notifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211355_create_document_notifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200134_create_document_permissions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211356_create_document_permissions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200135_create_document_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211357_create_document_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200136_create_document_versions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211358_create_document_versions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200137_create_documents_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211359_create_documents_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200138_create_email_sents_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211400_create_email_sents_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200139_create_entity_approvals_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211401_create_entity_approvals_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200140_create_entity_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211402_create_entity_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200141_create_entity_notes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211403_create_entity_notes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200142_create_equipment_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211404_create_equipment_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200143_create_equipment_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211405_create_equipment_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200144_create_equipment_daily_log_entries_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211406_create_equipment_daily_log_entries_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200145_create_equipment_disposal_approval_workflow_steps_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211407_create_equipment_disposal_approval_workflow_steps_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200146_create_equipment_disposal_approval_workflows_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211408_create_equipment_disposal_approval_workflows_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200147_create_equipment_disposal_approvals_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211409_create_equipment_disposal_approvals_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200148_create_equipment_disposal_audit_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211410_create_equipment_disposal_audit_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200149_create_equipment_disposal_files_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211411_create_equipment_disposal_files_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200150_create_equipment_disposal_methods_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211412_create_equipment_disposal_methods_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200151_create_equipment_disposals_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211413_create_equipment_disposals_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200152_create_equipment_evaluations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211414_create_equipment_evaluations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200153_create_equipment_notification_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211415_create_equipment_notification_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200154_create_equipment_operators_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211416_create_equipment_operators_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200155_create_equipment_usage_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211417_create_equipment_usage_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200156_create_event_history_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211418_create_event_history_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200157_create_failed_jobs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211419_create_failed_jobs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200158_create_feedback_requests_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211420_create_feedback_requests_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200159_create_finding_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211421_create_finding_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200200_create_finding_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211422_create_finding_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200201_create_form_fields_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211423_create_form_fields_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200202_create_form_template_variables_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211424_create_form_template_variables_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200203_create_form_templates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211425_create_form_templates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200204_create_formula_mandatory_fields_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211426_create_formula_mandatory_fields_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200205_create_formula_steps_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211427_create_formula_steps_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200206_create_formula_versions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211428_create_formula_versions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200207_create_formulas_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211429_create_formulas_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200208_create_general_requisition_request_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211430_create_general_requisition_request_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200209_create_general_requisition_supplier_quotes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211431_create_general_requisition_supplier_quotes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200210_create_general_requistion_requests_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211432_create_general_requistion_requests_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200211_create_global_variables_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211433_create_global_variables_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200212_create_import_lab_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211434_create_import_lab_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200213_create_inspection_detail_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211435_create_inspection_detail_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200214_create_inventory_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211436_create_inventory_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200215_create_inventory_departments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211437_create_inventory_departments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200216_create_inventory_item_notes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211438_create_inventory_item_notes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200217_create_inventory_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211439_create_inventory_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200218_create_inventory_location_users_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211440_create_inventory_location_users_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200219_create_inventory_locations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211441_create_inventory_locations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200220_create_inventory_order_item_to_inventory_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211442_create_inventory_order_item_to_inventory_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200221_create_inventory_order_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211443_create_inventory_order_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200222_create_inventory_orders_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211444_create_inventory_orders_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200223_create_inventory_store_contacts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211445_create_inventory_store_contacts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200224_create_inventory_store_slot_contents_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211446_create_inventory_store_slot_contents_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200225_create_inventory_store_slots_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211447_create_inventory_store_slots_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200226_create_inventory_stores_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211448_create_inventory_stores_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200227_create_inventory_sub_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211449_create_inventory_sub_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200228_create_inventory_supplier_ratings_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211450_create_inventory_supplier_ratings_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200229_create_invoicable_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211451_create_invoicable_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200230_create_invoice_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211452_create_invoice_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200231_create_invoice_payment_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211453_create_invoice_payment_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200232_create_iso_audits_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211454_create_iso_audits_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200233_create_item_brands_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211455_create_item_brands_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200234_create_item_states_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211456_create_item_states_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200235_create_job_designation_responsibility_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211457_create_job_designation_responsibility_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200236_create_lab_category_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211458_create_lab_category_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200237_create_lab_inventory_category_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211459_create_lab_inventory_category_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200238_create_lab_results_excel_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211500_create_lab_results_excel_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200239_create_lab_section_approver_configuration_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211501_create_lab_section_approver_configuration_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200240_create_lab_section_approver_relation_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211502_create_lab_section_approver_relation_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200241_create_lab_stock_movement_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211503_create_lab_stock_movement_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200242_create_lab_sub_category_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211504_create_lab_sub_category_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200243_create_labs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211505_create_labs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200244_create_language_lines_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211506_create_language_lines_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200245_create_languages_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211507_create_languages_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200246_create_likelihood_scales_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211508_create_likelihood_scales_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200247_create_lookup_table_entries_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211509_create_lookup_table_entries_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200248_create_lookup_tables_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211510_create_lookup_tables_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200249_create_maintainance_calibration_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211511_create_maintainance_calibration_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200250_create_method_reagents_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211512_create_method_reagents_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200251_create_method_sequence_run_samples_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211513_create_method_sequence_run_samples_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200252_create_method_sequence_run_stage_data_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211514_create_method_sequence_run_stage_data_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200253_create_method_sequence_runs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211515_create_method_sequence_runs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200254_create_method_sequence_stage_control_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211516_create_method_sequence_stage_control_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200255_create_method_sequence_stage_control_usage_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211517_create_method_sequence_stage_control_usage_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200256_create_method_sequence_stage_equipment_usage_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211518_create_method_sequence_stage_equipment_usage_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200257_create_method_sequence_stage_media_usage_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211519_create_method_sequence_stage_media_usage_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200258_create_method_sequence_stage_sample_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211520_create_method_sequence_stage_sample_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200259_create_method_sequence_stages_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211521_create_method_sequence_stages_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200300_create_method_sequence_versions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211522_create_method_sequence_versions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200301_create_method_sequences_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211523_create_method_sequences_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200302_create_method_validation_requests_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211524_create_method_validation_requests_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200303_create_module_pre_configs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211525_create_module_pre_configs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200304_create_module_pre_configs_07_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211526_create_module_pre_configs_07_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200305_create_mytestusers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211527_create_mytestusers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200306_create_naming_convension_consensuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211528_create_naming_convension_consensuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200307_create_nc_origins_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211529_create_nc_origins_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200308_create_nc_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211530_create_nc_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200309_create_non_conformances_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211531_create_non_conformances_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200310_create_o_t_p_s_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211532_create_o_t_p_s_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200311_create_parameters_import_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211533_create_parameters_import_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200312_create_parts_repaireds_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211534_create_parts_repaireds_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200313_create_password_resets_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211535_create_password_resets_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200314_create_personal_access_tokens_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211536_create_personal_access_tokens_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200315_create_personel_certifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211537_create_personel_certifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200316_create_personnel_work_histories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211538_create_personnel_work_histories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200317_create_personnel_working_schedules_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211539_create_personnel_working_schedules_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200318_create_phone_contacts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211540_create_phone_contacts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200319_create_preparation_steps_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211541_create_preparation_steps_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200320_create_pricelist_customers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211542_create_pricelist_customers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200321_create_pricelist_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211543_create_pricelist_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200322_create_pricelists_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211544_create_pricelists_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200323_create_procedure_config_fields_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211545_create_procedure_config_fields_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200324_create_procedure_test_kit_columns_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211546_create_procedure_test_kit_columns_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200325_create_procedure_test_kit_rows_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211547_create_procedure_test_kit_rows_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200326_create_procedure_test_kit_values_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211548_create_procedure_test_kit_values_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200327_create_procedure_worksheet_step_analysts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211549_create_procedure_worksheet_step_analysts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200328_create_procedure_worksheet_steps_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211550_create_procedure_worksheet_steps_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200329_create_procedure_worksheets_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211551_create_procedure_worksheets_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200330_create_qc_approvers_config_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211552_create_qc_approvers_config_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200331_create_qc_processed_result_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211553_create_qc_processed_result_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200332_create_qc_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211554_create_qc_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200333_create_qc_scheme_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211555_create_qc_scheme_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200334_create_qc_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211556_create_qc_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200335_create_qualifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211557_create_qualifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200336_create_quotation_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211558_create_quotation_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200337_create_quotation_details_analysis_type_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211559_create_quotation_details_analysis_type_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200338_create_quotation_headers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211600_create_quotation_headers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200339_create_rating_criterias_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211601_create_rating_criterias_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200340_create_rating_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211602_create_rating_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200341_create_rating_headers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211603_create_rating_headers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200342_create_rca_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211604_create_rca_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200343_create_remedy_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211605_create_remedy_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200344_create_remedy_headers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211606_create_remedy_headers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200345_create_report_format_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211607_create_report_format_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200346_create_report_format_sample_analysis_stage_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211608_create_report_format_sample_analysis_stage_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200347_create_report_format_sections_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211609_create_report_format_sections_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200348_create_report_formats_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211610_create_report_formats_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200349_create_report_header_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211611_create_report_header_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200350_create_report_table_configurations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211612_create_report_table_configurations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200351_create_reporting_units_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211613_create_reporting_units_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200352_create_request_entities_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211614_create_request_entities_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200353_create_request_entity_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211615_create_request_entity_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200354_create_request_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211616_create_request_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200355_create_requisition_locations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211617_create_requisition_locations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200356_create_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211618_create_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200357_create_risk_acceptance_criteria_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211619_create_risk_acceptance_criteria_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200358_create_risk_assessments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211620_create_risk_assessments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200359_create_risk_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211621_create_risk_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200400_create_risk_business_processes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211622_create_risk_business_processes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200401_create_risk_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211623_create_risk_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200402_create_risk_configuration_options_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211624_create_risk_configuration_options_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200403_create_risk_evaluations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211625_create_risk_evaluations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200404_create_risk_level_thresholds_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211626_create_risk_level_thresholds_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200405_create_risk_levels_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211627_create_risk_levels_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200406_create_risk_notifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211628_create_risk_notifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200407_create_risk_process_links_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211629_create_risk_process_links_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200408_create_risk_review_frequencies_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211630_create_risk_review_frequencies_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200409_create_risk_reviews_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211631_create_risk_reviews_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200410_create_risk_scoring_configs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211632_create_risk_scoring_configs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200411_create_risk_sources_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211633_create_risk_sources_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200412_create_risk_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211634_create_risk_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200413_create_risk_treatment_implementations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211635_create_risk_treatment_implementations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200414_create_risk_treatment_plans_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211636_create_risk_treatment_plans_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200415_create_risks_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211637_create_risks_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200416_create_role_certifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211638_create_role_certifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200417_create_roles_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211639_create_roles_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200418_create_root_cause_analyses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211640_create_root_cause_analyses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200419_create_root_cause_methods_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211641_create_root_cause_methods_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200420_create_samaco_sheet_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211642_create_samaco_sheet_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200421_create_sample_analysis_dates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211643_create_sample_analysis_dates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200422_create_sample_analysis_stages_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211644_create_sample_analysis_stages_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200423_create_sample_analysis_type_relation_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211645_create_sample_analysis_type_relation_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200424_create_sample_approval_checklist_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211646_create_sample_approval_checklist_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200425_create_sample_attachment_relations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211647_create_sample_attachment_relations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200426_create_sample_captured_worksheet_formulas_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211648_create_sample_captured_worksheet_formulas_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200427_create_sample_conditions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211649_create_sample_conditions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200428_create_sample_dates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211650_create_sample_dates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200429_create_sample_detail_staging_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211651_create_sample_detail_staging_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200430_create_sample_header_staging_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211652_create_sample_header_staging_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200431_create_sample_imports_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211653_create_sample_imports_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200432_create_sample_interlab_log_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211654_create_sample_interlab_log_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200433_create_sample_point_area_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211655_create_sample_point_area_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200434_create_sample_points_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211656_create_sample_points_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200435_create_sample_progress_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211657_create_sample_progress_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200436_create_sample_sequences_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211658_create_sample_sequences_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200437_create_sample_staging_rejection_log_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211659_create_sample_staging_rejection_log_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200438_create_sample_submission_request_exhibits_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211700_create_sample_submission_request_exhibits_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200439_create_sample_submission_request_requested_analyses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211701_create_sample_submission_request_requested_analyses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200440_create_sample_submission_request_supporting_document_templates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211702_create_sample_submission_request_supporting_document_templates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200441_create_sample_submission_request_suspects_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211703_create_sample_submission_request_suspects_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200442_create_sample_submission_requests_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211704_create_sample_submission_requests_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200443_create_sample_to_sample_analysis_stages_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211705_create_sample_to_sample_analysis_stages_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200444_create_sample_type_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211706_create_sample_type_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200445_create_sample_type_qualifications_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211707_create_sample_type_qualifications_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200446_create_sample_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211708_create_sample_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200447_create_sample_worksheet_formular_mandatory_data_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211709_create_sample_worksheet_formular_mandatory_data_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200448_create_sample_worksheet_formular_step_data_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211710_create_sample_worksheet_formular_step_data_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200449_create_sampletype_area_relation_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211711_create_sampletype_area_relation_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200450_create_sampletype_sample_point_relation_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211712_create_sampletype_sample_point_relation_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200451_create_ser_header_worksheet_sample_relations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211713_create_ser_header_worksheet_sample_relations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200452_create_ser_step_worksheet_sample_relations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211714_create_ser_step_worksheet_sample_relations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200453_create_ser_testkit_worksheet_sample_relations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211715_create_ser_testkit_worksheet_sample_relations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200454_create_ser_worksheet_steps_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211716_create_ser_worksheet_steps_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200455_create_service_confirmation_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211717_create_service_confirmation_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200456_create_services_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211718_create_services_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200457_create_severity_scales_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211719_create_severity_scales_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200458_create_skill_capability_detail_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211720_create_skill_capability_detail_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200459_create_skill_other_training_users_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211721_create_skill_other_training_users_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200500_create_skill_training_header_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211722_create_skill_training_header_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200501_create_skill_training_header_staff_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211723_create_skill_training_header_staff_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200502_create_skills_capability_matrix_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211724_create_skills_capability_matrix_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200503_create_skills_capability_matrix_role_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211725_create_skills_capability_matrix_role_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200504_create_skills_matrix_configurations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211726_create_skills_matrix_configurations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200505_create_skills_matrix_detail_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211727_create_skills_matrix_detail_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200506_create_skills_matrix_detail_role_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211728_create_skills_matrix_detail_role_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200507_create_skills_matrix_role_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211729_create_skills_matrix_role_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200508_create_skills_matrix_role_requirments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211730_create_skills_matrix_role_requirments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200509_create_skills_training_detail_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211731_create_skills_training_detail_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200510_create_skills_training_planner_detail_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211732_create_skills_training_planner_detail_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200511_create_skills_training_planner_header_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211733_create_skills_training_planner_header_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200512_create_skillsmatrices_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211734_create_skillsmatrices_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200513_create_solution_batch_history_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211735_create_solution_batch_history_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200514_create_solution_consumption_metrics_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211736_create_solution_consumption_metrics_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200515_create_solution_preparations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211737_create_solution_preparations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200516_create_spatie_model_has_permissions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211738_create_spatie_model_has_permissions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200517_create_spatie_model_has_roles_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211739_create_spatie_model_has_roles_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200518_create_spatie_permissions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211740_create_spatie_permissions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200519_create_spatie_role_has_permissions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211741_create_spatie_role_has_permissions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200520_create_spatie_roles_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211742_create_spatie_roles_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200521_create_stage_headers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211743_create_stage_headers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200522_create_standard_values_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211744_create_standard_values_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200523_create_standards_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211745_create_standards_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200524_create_captured_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211746_create_captured_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200525_create_sample_details_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211747_create_sample_details_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200526_create_standards_analytes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211748_create_standards_analytes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200527_create_stock_taking_counters_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211749_create_stock_taking_counters_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200528_create_stock_taking_sheets_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211750_create_stock_taking_sheets_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200529_create_stock_takings_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211751_create_stock_takings_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200530_create_stock_transfer_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211752_create_stock_transfer_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200531_create_stock_transfers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211753_create_stock_transfers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200532_create_submission_form_audit_log_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211754_create_submission_form_audit_log_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200533_create_submission_form_audit_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211755_create_submission_form_audit_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200534_create_submission_form_element_holders_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211756_create_submission_form_element_holders_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200535_create_submission_form_elements_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211757_create_submission_form_elements_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200536_create_submission_form_instance_values_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211758_create_submission_form_instance_values_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200537_create_submission_form_instances_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211759_create_submission_form_instances_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200538_create_submission_form_permissions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211800_create_submission_form_permissions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200539_create_submission_form_sample_analysis_stage_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211801_create_submission_form_sample_analysis_stage_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200540_create_submission_form_sections_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211802_create_submission_form_sections_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200541_create_submission_forms_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211803_create_submission_forms_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200542_create_supplier_brands_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211804_create_supplier_brands_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200543_create_supplier_by_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211805_create_supplier_by_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200544_create_supplier_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211806_create_supplier_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200545_create_supplier_contacts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211807_create_supplier_contacts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200546_create_supplier_contract_items_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211808_create_supplier_contract_items_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200547_create_supplier_contracts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211809_create_supplier_contracts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200548_create_supplier_quote_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211810_create_supplier_quote_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200549_create_supplier_quote_notes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211811_create_supplier_quote_notes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200550_create_supplier_quotes_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211812_create_supplier_quotes_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200551_create_supplier_r_f_q_s_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211813_create_supplier_r_f_q_s_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200552_create_supplier_rating_criteria_guide_supplier_scores_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211814_create_supplier_rating_criteria_guide_supplier_scores_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200553_create_supplier_rating_criteria_guides_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211815_create_supplier_rating_criteria_guides_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200554_create_suppliers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211816_create_suppliers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200555_create_suppliers_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211817_create_suppliers_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200556_create_suppliers_rating_criterias_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211818_create_suppliers_rating_criterias_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200557_create_supporting_document_elements_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211819_create_supporting_document_elements_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200558_create_supporting_document_instance_values_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211820_create_supporting_document_instance_values_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200559_create_supporting_document_instances_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211821_create_supporting_document_instances_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200600_create_supporting_document_sections_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211822_create_supporting_document_sections_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200601_create_supporting_document_templates_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211823_create_supporting_document_templates_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200602_create_system_configuration_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211824_create_system_configuration_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200603_create_system_configurations_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211825_create_system_configurations_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200604_create_tat_captured_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211826_create_tat_captured_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200605_create_tax_regime_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211827_create_tax_regime_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200606_create_temp_sample_data_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211828_create_temp_sample_data_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200607_create_test_stages_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211829_create_test_stages_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200608_create_ticket_assignments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211830_create_ticket_assignments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200609_create_ticket_categories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211831_create_ticket_categories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200610_create_ticket_change_history_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211832_create_ticket_change_history_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200611_create_ticket_chat_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211833_create_ticket_chat_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200612_create_ticket_chat_attachments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211834_create_ticket_chat_attachments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200613_create_ticket_comments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211835_create_ticket_comments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200614_create_ticket_permissions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211836_create_ticket_permissions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200615_create_ticket_priorities_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211837_create_ticket_priorities_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200616_create_ticket_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211838_create_ticket_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200617_create_ticket_team_chat_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211839_create_ticket_team_chat_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200618_create_topologies_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211840_create_topologies_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200619_create_treatment_types_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211841_create_treatment_types_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200620_create_uncertainty_budgets_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211842_create_uncertainty_budgets_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200621_create_uncertainty_sources_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211843_create_uncertainty_sources_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200622_create_unit_of_measure_conversions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211844_create_unit_of_measure_conversions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200623_create_uom_conversions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211845_create_uom_conversions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200624_create_user_alerts_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211846_create_user_alerts_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200625_create_user_departmental_approvals_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211847_create_user_departmental_approvals_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200626_create_user_roles_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211848_create_user_roles_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200627_create_users_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211849_create_users_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200628_create_batch_ammendments_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211850_create_batch_ammendments_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200629_create_sample_headers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211851_create_sample_headers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200630_create_verification_closure_statuses_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211852_create_verification_closure_statuses_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200631_create_verification_logs_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211853_create_verification_logs_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200632_create_verification_records_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211854_create_verification_records_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200633_create_verification_results_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211855_create_verification_results_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200634_create_work_order_resources_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211856_create_work_order_resources_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200635_create_work_order_status_histories_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211857_create_work_order_status_histories_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200636_create_work_orders_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211858_create_work_orders_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200637_create_workflow_actions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211859_create_workflow_actions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200638_create_workorder_edits_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211900_create_workorder_edits_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200639_create_workorder_personnel_schedules_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211901_create_workorder_personnel_schedules_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200640_create_worksheet_executions_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211902_create_worksheet_executions_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200641_create_worksheet_external_samples_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211903_create_worksheet_external_samples_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200642_create_zoho_api_tokens_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211904_create_zoho_api_tokens_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200643_create_zoho_customers_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211905_create_zoho_customers_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200644_create_zones_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211906_create_zones_table.php');
DELETE FROM migrations
WHERE migration = '2026_02_05_200645_create_portal_access_requests_table.php'
  AND EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_04_23_211907_create_portal_access_requests_table.php');

-- Rename old filenames to new filenames.
UPDATE migrations SET migration = '2026_02_05_200000_create_ai_action_logs_table.php' WHERE migration = '2026_04_23_211221_create_ai_action_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200001_create_ai_analytics_logs_table.php' WHERE migration = '2026_04_23_211222_create_ai_analytics_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200002_create_ai_chat_attachments_table.php' WHERE migration = '2026_04_23_211223_create_ai_chat_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200003_create_ai_conversations_table.php' WHERE migration = '2026_04_23_211224_create_ai_conversations_table.php';
UPDATE migrations SET migration = '2026_02_05_200004_create_ai_feature_snapshots_table.php' WHERE migration = '2026_04_23_211225_create_ai_feature_snapshots_table.php';
UPDATE migrations SET migration = '2026_02_05_200005_create_ai_messages_table.php' WHERE migration = '2026_04_23_211226_create_ai_messages_table.php';
UPDATE migrations SET migration = '2026_02_05_200006_create_ai_model_registry_table.php' WHERE migration = '2026_04_23_211227_create_ai_model_registry_table.php';
UPDATE migrations SET migration = '2026_02_05_200007_create_analysis_elements_table.php' WHERE migration = '2026_04_23_211228_create_analysis_elements_table.php';
UPDATE migrations SET migration = '2026_02_05_200008_create_analysis_guides_table.php' WHERE migration = '2026_04_23_211229_create_analysis_guides_table.php';
UPDATE migrations SET migration = '2026_02_05_200009_create_analysis_method_elements_table.php' WHERE migration = '2026_04_23_211230_create_analysis_method_elements_table.php';
UPDATE migrations SET migration = '2026_02_05_200010_create_analysis_methods_table.php' WHERE migration = '2026_04_23_211231_create_analysis_methods_table.php';
UPDATE migrations SET migration = '2026_02_05_200011_create_analysis_type_invoicable_item_table.php' WHERE migration = '2026_04_23_211232_create_analysis_type_invoicable_item_table.php';
UPDATE migrations SET migration = '2026_02_05_200012_create_analysis_types_table.php' WHERE migration = '2026_04_23_211233_create_analysis_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200013_create_analytes_table.php' WHERE migration = '2026_04_23_211234_create_analytes_table.php';
UPDATE migrations SET migration = '2026_02_05_200014_create_companies_table.php' WHERE migration = '2026_04_23_211235_create_companies_table.php';
UPDATE migrations SET migration = '2026_02_05_200015_create_approvals_table.php' WHERE migration = '2026_04_23_211236_create_approvals_table.php';
UPDATE migrations SET migration = '2026_02_05_200016_create_asset_locations_table.php' WHERE migration = '2026_04_23_211237_create_asset_locations_table.php';
UPDATE migrations SET migration = '2026_02_05_200017_create_asset_types_table.php' WHERE migration = '2026_04_23_211238_create_asset_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200018_create_attachment_types_table.php' WHERE migration = '2026_04_23_211239_create_attachment_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200019_create_audit_attachments_table.php' WHERE migration = '2026_04_23_211240_create_audit_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200020_create_audit_checklist_audit_table.php' WHERE migration = '2026_04_23_211241_create_audit_checklist_audit_table.php';
UPDATE migrations SET migration = '2026_02_05_200021_create_audit_checklist_items_table.php' WHERE migration = '2026_04_23_211242_create_audit_checklist_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200022_create_audit_checklists_table.php' WHERE migration = '2026_04_23_211243_create_audit_checklists_table.php';
UPDATE migrations SET migration = '2026_02_05_200023_create_audit_email_templates_table.php' WHERE migration = '2026_04_23_211244_create_audit_email_templates_table.php';
UPDATE migrations SET migration = '2026_02_05_200024_create_audit_module_findings_table.php' WHERE migration = '2026_04_23_211245_create_audit_module_findings_table.php';
UPDATE migrations SET migration = '2026_02_05_200025_create_audit_notification_types_table.php' WHERE migration = '2026_04_23_211246_create_audit_notification_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200026_create_audit_notifications_table.php' WHERE migration = '2026_04_23_211247_create_audit_notifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200027_create_audit_statuses_table.php' WHERE migration = '2026_04_23_211248_create_audit_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200028_create_audit_team_members_table.php' WHERE migration = '2026_04_23_211249_create_audit_team_members_table.php';
UPDATE migrations SET migration = '2026_02_05_200029_create_audit_team_roles_table.php' WHERE migration = '2026_04_23_211250_create_audit_team_roles_table.php';
UPDATE migrations SET migration = '2026_02_05_200030_create_audit_types_table.php' WHERE migration = '2026_04_23_211251_create_audit_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200031_create_audit_workflow_approvers_table.php' WHERE migration = '2026_04_23_211252_create_audit_workflow_approvers_table.php';
UPDATE migrations SET migration = '2026_02_05_200032_create_audits_table.php' WHERE migration = '2026_04_23_211253_create_audits_table.php';
UPDATE migrations SET migration = '2026_02_05_200033_create_batch_approval_checklist_table.php' WHERE migration = '2026_04_23_211254_create_batch_approval_checklist_table.php';
UPDATE migrations SET migration = '2026_02_05_200034_create_batch_attachment_annotations_table.php' WHERE migration = '2026_04_23_211255_create_batch_attachment_annotations_table.php';
UPDATE migrations SET migration = '2026_02_05_200035_create_batch_attachments_table.php' WHERE migration = '2026_04_23_211256_create_batch_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200036_create_batch_comments_table.php' WHERE migration = '2026_04_23_211257_create_batch_comments_table.php';
UPDATE migrations SET migration = '2026_02_05_200037_create_batch_labsection_approval_table.php' WHERE migration = '2026_04_23_211258_create_batch_labsection_approval_table.php';
UPDATE migrations SET migration = '2026_02_05_200038_create_batch_notifications_table.php' WHERE migration = '2026_04_23_211259_create_batch_notifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200039_create_batch_sequences_table.php' WHERE migration = '2026_04_23_211300_create_batch_sequences_table.php';
UPDATE migrations SET migration = '2026_02_05_200040_create_calendar_events_table.php' WHERE migration = '2026_04_23_211301_create_calendar_events_table.php';
UPDATE migrations SET migration = '2026_02_05_200041_create_calendarevents_notifications_table.php' WHERE migration = '2026_04_23_211302_create_calendarevents_notifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200042_create_capa_action_types_table.php' WHERE migration = '2026_04_23_211303_create_capa_action_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200043_create_capa_categories_table.php' WHERE migration = '2026_04_23_211304_create_capa_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200044_create_capa_priorities_table.php' WHERE migration = '2026_04_23_211305_create_capa_priorities_table.php';
UPDATE migrations SET migration = '2026_02_05_200045_create_capa_statuses_table.php' WHERE migration = '2026_04_23_211306_create_capa_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200046_create_captured_procedure_config_values_table.php' WHERE migration = '2026_04_23_211307_create_captured_procedure_config_values_table.php';
UPDATE migrations SET migration = '2026_02_05_200047_create_captured_procedure_values_table.php' WHERE migration = '2026_04_23_211308_create_captured_procedure_values_table.php';
UPDATE migrations SET migration = '2026_02_05_200048_create_captured_view_table.php' WHERE migration = '2026_04_23_211309_create_captured_view_table.php';
UPDATE migrations SET migration = '2026_02_05_200049_create_certificate_template_element_holders_table.php' WHERE migration = '2026_04_23_211310_create_certificate_template_element_holders_table.php';
UPDATE migrations SET migration = '2026_02_05_200050_create_certificate_template_elements_table.php' WHERE migration = '2026_04_23_211311_create_certificate_template_elements_table.php';
UPDATE migrations SET migration = '2026_02_05_200051_create_certificate_template_reports_table.php' WHERE migration = '2026_04_23_211312_create_certificate_template_reports_table.php';
UPDATE migrations SET migration = '2026_02_05_200052_create_certificate_template_sections_table.php' WHERE migration = '2026_04_23_211313_create_certificate_template_sections_table.php';
UPDATE migrations SET migration = '2026_02_05_200053_create_certificate_templates_table.php' WHERE migration = '2026_04_23_211314_create_certificate_templates_table.php';
UPDATE migrations SET migration = '2026_02_05_200054_create_chain_of_custodies_table.php' WHERE migration = '2026_04_23_211315_create_chain_of_custodies_table.php';
UPDATE migrations SET migration = '2026_02_05_200055_create_chain_of_custody_complaints_table.php' WHERE migration = '2026_04_23_211316_create_chain_of_custody_complaints_table.php';
UPDATE migrations SET migration = '2026_02_05_200056_create_chart_of_accounts_table.php' WHERE migration = '2026_04_23_211317_create_chart_of_accounts_table.php';
UPDATE migrations SET migration = '2026_02_05_200057_create_chat_message_table.php' WHERE migration = '2026_04_23_211318_create_chat_message_table.php';
UPDATE migrations SET migration = '2026_02_05_200058_create_company_products_table.php' WHERE migration = '2026_04_23_211320_create_company_products_table.php';
UPDATE migrations SET migration = '2026_02_05_200059_create_complaint_type_table.php' WHERE migration = '2026_04_23_211321_create_complaint_type_table.php';
UPDATE migrations SET migration = '2026_02_05_200100_create_complaintattachments_table.php' WHERE migration = '2026_04_23_211322_create_complaintattachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200101_create_complaintnotes_table.php' WHERE migration = '2026_04_23_211323_create_complaintnotes_table.php';
UPDATE migrations SET migration = '2026_02_05_200102_create_complaints_table.php' WHERE migration = '2026_04_23_211324_create_complaints_table.php';
UPDATE migrations SET migration = '2026_02_05_200103_create_complaintsresolutions_table.php' WHERE migration = '2026_04_23_211325_create_complaintsresolutions_table.php';
UPDATE migrations SET migration = '2026_02_05_200104_create_compliance_statuses_table.php' WHERE migration = '2026_04_23_211326_create_compliance_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200105_create_contact_phones_table.php' WHERE migration = '2026_04_23_211327_create_contact_phones_table.php';
UPDATE migrations SET migration = '2026_02_05_200106_create_conversation_table.php' WHERE migration = '2026_04_23_211328_create_conversation_table.php';
UPDATE migrations SET migration = '2026_02_05_200107_create_corrective_actions_table.php' WHERE migration = '2026_04_23_211329_create_corrective_actions_table.php';
UPDATE migrations SET migration = '2026_02_05_200108_create_countries_table.php' WHERE migration = '2026_04_23_211330_create_countries_table.php';
UPDATE migrations SET migration = '2026_02_05_200109_create_crm_areas_table.php' WHERE migration = '2026_04_23_211331_create_crm_areas_table.php';
UPDATE migrations SET migration = '2026_02_05_200110_create_crm_company_sections_table.php' WHERE migration = '2026_04_23_211332_create_crm_company_sections_table.php';
UPDATE migrations SET migration = '2026_02_05_200111_create_crm_company_sub_units_table.php' WHERE migration = '2026_04_23_211333_create_crm_company_sub_units_table.php';
UPDATE migrations SET migration = '2026_02_05_200112_create_crm_company_units_table.php' WHERE migration = '2026_04_23_211334_create_crm_company_units_table.php';
UPDATE migrations SET migration = '2026_02_05_200113_create_crm_customer_contacts_table.php' WHERE migration = '2026_04_23_211335_create_crm_customer_contacts_table.php';
UPDATE migrations SET migration = '2026_02_05_200114_create_crm_customers_table.php' WHERE migration = '2026_04_23_211336_create_crm_customers_table.php';
UPDATE migrations SET migration = '2026_02_05_200115_create_crm_evaluation_metrics_table.php' WHERE migration = '2026_04_23_211337_create_crm_evaluation_metrics_table.php';
UPDATE migrations SET migration = '2026_02_05_200116_create_crm_feedback_ratings_table.php' WHERE migration = '2026_04_23_211338_create_crm_feedback_ratings_table.php';
UPDATE migrations SET migration = '2026_02_05_200117_create_crm_report_info_columns_table.php' WHERE migration = '2026_04_23_211339_create_crm_report_info_columns_table.php';
UPDATE migrations SET migration = '2026_02_05_200118_create_crm_sample_points_table.php' WHERE migration = '2026_04_23_211340_create_crm_sample_points_table.php';
UPDATE migrations SET migration = '2026_02_05_200119_create_currencies_table.php' WHERE migration = '2026_04_23_211341_create_currencies_table.php';
UPDATE migrations SET migration = '2026_02_05_200120_create_currency_conversions_table.php' WHERE migration = '2026_04_23_211342_create_currency_conversions_table.php';
UPDATE migrations SET migration = '2026_02_05_200121_create_custom_field_category_customers_table.php' WHERE migration = '2026_04_23_211343_create_custom_field_category_customers_table.php';
UPDATE migrations SET migration = '2026_02_05_200122_create_customer_invoice_table.php' WHERE migration = '2026_04_23_211344_create_customer_invoice_table.php';
UPDATE migrations SET migration = '2026_02_05_200123_create_customer_submission_form_columns_table.php' WHERE migration = '2026_04_23_211345_create_customer_submission_form_columns_table.php';
UPDATE migrations SET migration = '2026_02_05_200124_create_customerfeedbacks_table.php' WHERE migration = '2026_04_23_211346_create_customerfeedbacks_table.php';
UPDATE migrations SET migration = '2026_02_05_200125_create_customerqualifications_table.php' WHERE migration = '2026_04_23_211347_create_customerqualifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200126_create_directorates_table.php' WHERE migration = '2026_04_23_211348_create_directorates_table.php';
UPDATE migrations SET migration = '2026_02_05_200127_create_disposal_reasons_table.php' WHERE migration = '2026_04_23_211349_create_disposal_reasons_table.php';
UPDATE migrations SET migration = '2026_02_05_200128_create_document_amendments_table.php' WHERE migration = '2026_04_23_211350_create_document_amendments_table.php';
UPDATE migrations SET migration = '2026_02_05_200129_create_document_approval_workflow_steps_table.php' WHERE migration = '2026_04_23_211351_create_document_approval_workflow_steps_table.php';
UPDATE migrations SET migration = '2026_02_05_200130_create_document_approval_workflows_table.php' WHERE migration = '2026_04_23_211352_create_document_approval_workflows_table.php';
UPDATE migrations SET migration = '2026_02_05_200131_create_document_audit_logs_table.php' WHERE migration = '2026_04_23_211353_create_document_audit_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200132_create_document_expiry_notification_settings_table.php' WHERE migration = '2026_04_23_211354_create_document_expiry_notification_settings_table.php';
UPDATE migrations SET migration = '2026_02_05_200133_create_document_notifications_table.php' WHERE migration = '2026_04_23_211355_create_document_notifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200134_create_document_permissions_table.php' WHERE migration = '2026_04_23_211356_create_document_permissions_table.php';
UPDATE migrations SET migration = '2026_02_05_200135_create_document_types_table.php' WHERE migration = '2026_04_23_211357_create_document_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200136_create_document_versions_table.php' WHERE migration = '2026_04_23_211358_create_document_versions_table.php';
UPDATE migrations SET migration = '2026_02_05_200137_create_documents_table.php' WHERE migration = '2026_04_23_211359_create_documents_table.php';
UPDATE migrations SET migration = '2026_02_05_200138_create_email_sents_table.php' WHERE migration = '2026_04_23_211400_create_email_sents_table.php';
UPDATE migrations SET migration = '2026_02_05_200139_create_entity_approvals_table.php' WHERE migration = '2026_04_23_211401_create_entity_approvals_table.php';
UPDATE migrations SET migration = '2026_02_05_200140_create_entity_attachments_table.php' WHERE migration = '2026_04_23_211402_create_entity_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200141_create_entity_notes_table.php' WHERE migration = '2026_04_23_211403_create_entity_notes_table.php';
UPDATE migrations SET migration = '2026_02_05_200142_create_equipment_table.php' WHERE migration = '2026_04_23_211404_create_equipment_table.php';
UPDATE migrations SET migration = '2026_02_05_200143_create_equipment_attachments_table.php' WHERE migration = '2026_04_23_211405_create_equipment_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200144_create_equipment_daily_log_entries_table.php' WHERE migration = '2026_04_23_211406_create_equipment_daily_log_entries_table.php';
UPDATE migrations SET migration = '2026_02_05_200145_create_equipment_disposal_approval_workflow_steps_table.php' WHERE migration = '2026_04_23_211407_create_equipment_disposal_approval_workflow_steps_table.php';
UPDATE migrations SET migration = '2026_02_05_200146_create_equipment_disposal_approval_workflows_table.php' WHERE migration = '2026_04_23_211408_create_equipment_disposal_approval_workflows_table.php';
UPDATE migrations SET migration = '2026_02_05_200147_create_equipment_disposal_approvals_table.php' WHERE migration = '2026_04_23_211409_create_equipment_disposal_approvals_table.php';
UPDATE migrations SET migration = '2026_02_05_200148_create_equipment_disposal_audit_logs_table.php' WHERE migration = '2026_04_23_211410_create_equipment_disposal_audit_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200149_create_equipment_disposal_files_table.php' WHERE migration = '2026_04_23_211411_create_equipment_disposal_files_table.php';
UPDATE migrations SET migration = '2026_02_05_200150_create_equipment_disposal_methods_table.php' WHERE migration = '2026_04_23_211412_create_equipment_disposal_methods_table.php';
UPDATE migrations SET migration = '2026_02_05_200151_create_equipment_disposals_table.php' WHERE migration = '2026_04_23_211413_create_equipment_disposals_table.php';
UPDATE migrations SET migration = '2026_02_05_200152_create_equipment_evaluations_table.php' WHERE migration = '2026_04_23_211414_create_equipment_evaluations_table.php';
UPDATE migrations SET migration = '2026_02_05_200153_create_equipment_notification_table.php' WHERE migration = '2026_04_23_211415_create_equipment_notification_table.php';
UPDATE migrations SET migration = '2026_02_05_200154_create_equipment_operators_table.php' WHERE migration = '2026_04_23_211416_create_equipment_operators_table.php';
UPDATE migrations SET migration = '2026_02_05_200155_create_equipment_usage_table.php' WHERE migration = '2026_04_23_211417_create_equipment_usage_table.php';
UPDATE migrations SET migration = '2026_02_05_200156_create_event_history_table.php' WHERE migration = '2026_04_23_211418_create_event_history_table.php';
UPDATE migrations SET migration = '2026_02_05_200157_create_failed_jobs_table.php' WHERE migration = '2026_04_23_211419_create_failed_jobs_table.php';
UPDATE migrations SET migration = '2026_02_05_200158_create_feedback_requests_table.php' WHERE migration = '2026_04_23_211420_create_feedback_requests_table.php';
UPDATE migrations SET migration = '2026_02_05_200159_create_finding_categories_table.php' WHERE migration = '2026_04_23_211421_create_finding_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200200_create_finding_statuses_table.php' WHERE migration = '2026_04_23_211422_create_finding_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200201_create_form_fields_table.php' WHERE migration = '2026_04_23_211423_create_form_fields_table.php';
UPDATE migrations SET migration = '2026_02_05_200202_create_form_template_variables_table.php' WHERE migration = '2026_04_23_211424_create_form_template_variables_table.php';
UPDATE migrations SET migration = '2026_02_05_200203_create_form_templates_table.php' WHERE migration = '2026_04_23_211425_create_form_templates_table.php';
UPDATE migrations SET migration = '2026_02_05_200204_create_formula_mandatory_fields_table.php' WHERE migration = '2026_04_23_211426_create_formula_mandatory_fields_table.php';
UPDATE migrations SET migration = '2026_02_05_200205_create_formula_steps_table.php' WHERE migration = '2026_04_23_211427_create_formula_steps_table.php';
UPDATE migrations SET migration = '2026_02_05_200206_create_formula_versions_table.php' WHERE migration = '2026_04_23_211428_create_formula_versions_table.php';
UPDATE migrations SET migration = '2026_02_05_200207_create_formulas_table.php' WHERE migration = '2026_04_23_211429_create_formulas_table.php';
UPDATE migrations SET migration = '2026_02_05_200208_create_general_requisition_request_items_table.php' WHERE migration = '2026_04_23_211430_create_general_requisition_request_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200209_create_general_requisition_supplier_quotes_table.php' WHERE migration = '2026_04_23_211431_create_general_requisition_supplier_quotes_table.php';
UPDATE migrations SET migration = '2026_02_05_200210_create_general_requistion_requests_table.php' WHERE migration = '2026_04_23_211432_create_general_requistion_requests_table.php';
UPDATE migrations SET migration = '2026_02_05_200211_create_global_variables_table.php' WHERE migration = '2026_04_23_211433_create_global_variables_table.php';
UPDATE migrations SET migration = '2026_02_05_200212_create_import_lab_results_table.php' WHERE migration = '2026_04_23_211434_create_import_lab_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200213_create_inspection_detail_table.php' WHERE migration = '2026_04_23_211435_create_inspection_detail_table.php';
UPDATE migrations SET migration = '2026_02_05_200214_create_inventory_categories_table.php' WHERE migration = '2026_04_23_211436_create_inventory_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200215_create_inventory_departments_table.php' WHERE migration = '2026_04_23_211437_create_inventory_departments_table.php';
UPDATE migrations SET migration = '2026_02_05_200216_create_inventory_item_notes_table.php' WHERE migration = '2026_04_23_211438_create_inventory_item_notes_table.php';
UPDATE migrations SET migration = '2026_02_05_200217_create_inventory_items_table.php' WHERE migration = '2026_04_23_211439_create_inventory_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200218_create_inventory_location_users_table.php' WHERE migration = '2026_04_23_211440_create_inventory_location_users_table.php';
UPDATE migrations SET migration = '2026_02_05_200219_create_inventory_locations_table.php' WHERE migration = '2026_04_23_211441_create_inventory_locations_table.php';
UPDATE migrations SET migration = '2026_02_05_200220_create_inventory_order_item_to_inventory_items_table.php' WHERE migration = '2026_04_23_211442_create_inventory_order_item_to_inventory_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200221_create_inventory_order_items_table.php' WHERE migration = '2026_04_23_211443_create_inventory_order_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200222_create_inventory_orders_table.php' WHERE migration = '2026_04_23_211444_create_inventory_orders_table.php';
UPDATE migrations SET migration = '2026_02_05_200223_create_inventory_store_contacts_table.php' WHERE migration = '2026_04_23_211445_create_inventory_store_contacts_table.php';
UPDATE migrations SET migration = '2026_02_05_200224_create_inventory_store_slot_contents_table.php' WHERE migration = '2026_04_23_211446_create_inventory_store_slot_contents_table.php';
UPDATE migrations SET migration = '2026_02_05_200225_create_inventory_store_slots_table.php' WHERE migration = '2026_04_23_211447_create_inventory_store_slots_table.php';
UPDATE migrations SET migration = '2026_02_05_200226_create_inventory_stores_table.php' WHERE migration = '2026_04_23_211448_create_inventory_stores_table.php';
UPDATE migrations SET migration = '2026_02_05_200227_create_inventory_sub_categories_table.php' WHERE migration = '2026_04_23_211449_create_inventory_sub_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200228_create_inventory_supplier_ratings_table.php' WHERE migration = '2026_04_23_211450_create_inventory_supplier_ratings_table.php';
UPDATE migrations SET migration = '2026_02_05_200229_create_invoicable_items_table.php' WHERE migration = '2026_04_23_211451_create_invoicable_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200230_create_invoice_details_table.php' WHERE migration = '2026_04_23_211452_create_invoice_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200231_create_invoice_payment_details_table.php' WHERE migration = '2026_04_23_211453_create_invoice_payment_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200232_create_iso_audits_table.php' WHERE migration = '2026_04_23_211454_create_iso_audits_table.php';
UPDATE migrations SET migration = '2026_02_05_200233_create_item_brands_table.php' WHERE migration = '2026_04_23_211455_create_item_brands_table.php';
UPDATE migrations SET migration = '2026_02_05_200234_create_item_states_table.php' WHERE migration = '2026_04_23_211456_create_item_states_table.php';
UPDATE migrations SET migration = '2026_02_05_200235_create_job_designation_responsibility_table.php' WHERE migration = '2026_04_23_211457_create_job_designation_responsibility_table.php';
UPDATE migrations SET migration = '2026_02_05_200236_create_lab_category_items_table.php' WHERE migration = '2026_04_23_211458_create_lab_category_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200237_create_lab_inventory_category_table.php' WHERE migration = '2026_04_23_211459_create_lab_inventory_category_table.php';
UPDATE migrations SET migration = '2026_02_05_200238_create_lab_results_excel_table.php' WHERE migration = '2026_04_23_211500_create_lab_results_excel_table.php';
UPDATE migrations SET migration = '2026_02_05_200239_create_lab_section_approver_configuration_table.php' WHERE migration = '2026_04_23_211501_create_lab_section_approver_configuration_table.php';
UPDATE migrations SET migration = '2026_02_05_200240_create_lab_section_approver_relation_table.php' WHERE migration = '2026_04_23_211502_create_lab_section_approver_relation_table.php';
UPDATE migrations SET migration = '2026_02_05_200241_create_lab_stock_movement_table.php' WHERE migration = '2026_04_23_211503_create_lab_stock_movement_table.php';
UPDATE migrations SET migration = '2026_02_05_200242_create_lab_sub_category_table.php' WHERE migration = '2026_04_23_211504_create_lab_sub_category_table.php';
UPDATE migrations SET migration = '2026_02_05_200243_create_labs_table.php' WHERE migration = '2026_04_23_211505_create_labs_table.php';
UPDATE migrations SET migration = '2026_02_05_200244_create_language_lines_table.php' WHERE migration = '2026_04_23_211506_create_language_lines_table.php';
UPDATE migrations SET migration = '2026_02_05_200245_create_languages_table.php' WHERE migration = '2026_04_23_211507_create_languages_table.php';
UPDATE migrations SET migration = '2026_02_05_200246_create_likelihood_scales_table.php' WHERE migration = '2026_04_23_211508_create_likelihood_scales_table.php';
UPDATE migrations SET migration = '2026_02_05_200247_create_lookup_table_entries_table.php' WHERE migration = '2026_04_23_211509_create_lookup_table_entries_table.php';
UPDATE migrations SET migration = '2026_02_05_200248_create_lookup_tables_table.php' WHERE migration = '2026_04_23_211510_create_lookup_tables_table.php';
UPDATE migrations SET migration = '2026_02_05_200249_create_maintainance_calibration_logs_table.php' WHERE migration = '2026_04_23_211511_create_maintainance_calibration_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200250_create_method_reagents_table.php' WHERE migration = '2026_04_23_211512_create_method_reagents_table.php';
UPDATE migrations SET migration = '2026_02_05_200251_create_method_sequence_run_samples_table.php' WHERE migration = '2026_04_23_211513_create_method_sequence_run_samples_table.php';
UPDATE migrations SET migration = '2026_02_05_200252_create_method_sequence_run_stage_data_table.php' WHERE migration = '2026_04_23_211514_create_method_sequence_run_stage_data_table.php';
UPDATE migrations SET migration = '2026_02_05_200253_create_method_sequence_runs_table.php' WHERE migration = '2026_04_23_211515_create_method_sequence_runs_table.php';
UPDATE migrations SET migration = '2026_02_05_200254_create_method_sequence_stage_control_results_table.php' WHERE migration = '2026_04_23_211516_create_method_sequence_stage_control_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200255_create_method_sequence_stage_control_usage_table.php' WHERE migration = '2026_04_23_211517_create_method_sequence_stage_control_usage_table.php';
UPDATE migrations SET migration = '2026_02_05_200256_create_method_sequence_stage_equipment_usage_table.php' WHERE migration = '2026_04_23_211518_create_method_sequence_stage_equipment_usage_table.php';
UPDATE migrations SET migration = '2026_02_05_200257_create_method_sequence_stage_media_usage_table.php' WHERE migration = '2026_04_23_211519_create_method_sequence_stage_media_usage_table.php';
UPDATE migrations SET migration = '2026_02_05_200258_create_method_sequence_stage_sample_results_table.php' WHERE migration = '2026_04_23_211520_create_method_sequence_stage_sample_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200259_create_method_sequence_stages_table.php' WHERE migration = '2026_04_23_211521_create_method_sequence_stages_table.php';
UPDATE migrations SET migration = '2026_02_05_200300_create_method_sequence_versions_table.php' WHERE migration = '2026_04_23_211522_create_method_sequence_versions_table.php';
UPDATE migrations SET migration = '2026_02_05_200301_create_method_sequences_table.php' WHERE migration = '2026_04_23_211523_create_method_sequences_table.php';
UPDATE migrations SET migration = '2026_02_05_200302_create_method_validation_requests_table.php' WHERE migration = '2026_04_23_211524_create_method_validation_requests_table.php';
UPDATE migrations SET migration = '2026_02_05_200303_create_module_pre_configs_table.php' WHERE migration = '2026_04_23_211525_create_module_pre_configs_table.php';
UPDATE migrations SET migration = '2026_02_05_200304_create_module_pre_configs_07_table.php' WHERE migration = '2026_04_23_211526_create_module_pre_configs_07_table.php';
UPDATE migrations SET migration = '2026_02_05_200305_create_mytestusers_table.php' WHERE migration = '2026_04_23_211527_create_mytestusers_table.php';
UPDATE migrations SET migration = '2026_02_05_200306_create_naming_convension_consensuses_table.php' WHERE migration = '2026_04_23_211528_create_naming_convension_consensuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200307_create_nc_origins_table.php' WHERE migration = '2026_04_23_211529_create_nc_origins_table.php';
UPDATE migrations SET migration = '2026_02_05_200308_create_nc_statuses_table.php' WHERE migration = '2026_04_23_211530_create_nc_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200309_create_non_conformances_table.php' WHERE migration = '2026_04_23_211531_create_non_conformances_table.php';
UPDATE migrations SET migration = '2026_02_05_200310_create_o_t_p_s_table.php' WHERE migration = '2026_04_23_211532_create_o_t_p_s_table.php';
UPDATE migrations SET migration = '2026_02_05_200311_create_parameters_import_table.php' WHERE migration = '2026_04_23_211533_create_parameters_import_table.php';
UPDATE migrations SET migration = '2026_02_05_200312_create_parts_repaireds_table.php' WHERE migration = '2026_04_23_211534_create_parts_repaireds_table.php';
UPDATE migrations SET migration = '2026_02_05_200313_create_password_resets_table.php' WHERE migration = '2026_04_23_211535_create_password_resets_table.php';
UPDATE migrations SET migration = '2026_02_05_200314_create_personal_access_tokens_table.php' WHERE migration = '2026_04_23_211536_create_personal_access_tokens_table.php';
UPDATE migrations SET migration = '2026_02_05_200315_create_personel_certifications_table.php' WHERE migration = '2026_04_23_211537_create_personel_certifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200316_create_personnel_work_histories_table.php' WHERE migration = '2026_04_23_211538_create_personnel_work_histories_table.php';
UPDATE migrations SET migration = '2026_02_05_200317_create_personnel_working_schedules_table.php' WHERE migration = '2026_04_23_211539_create_personnel_working_schedules_table.php';
UPDATE migrations SET migration = '2026_02_05_200318_create_phone_contacts_table.php' WHERE migration = '2026_04_23_211540_create_phone_contacts_table.php';
UPDATE migrations SET migration = '2026_02_05_200319_create_preparation_steps_table.php' WHERE migration = '2026_04_23_211541_create_preparation_steps_table.php';
UPDATE migrations SET migration = '2026_02_05_200320_create_pricelist_customers_table.php' WHERE migration = '2026_04_23_211542_create_pricelist_customers_table.php';
UPDATE migrations SET migration = '2026_02_05_200321_create_pricelist_items_table.php' WHERE migration = '2026_04_23_211543_create_pricelist_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200322_create_pricelists_table.php' WHERE migration = '2026_04_23_211544_create_pricelists_table.php';
UPDATE migrations SET migration = '2026_02_05_200323_create_procedure_config_fields_table.php' WHERE migration = '2026_04_23_211545_create_procedure_config_fields_table.php';
UPDATE migrations SET migration = '2026_02_05_200324_create_procedure_test_kit_columns_table.php' WHERE migration = '2026_04_23_211546_create_procedure_test_kit_columns_table.php';
UPDATE migrations SET migration = '2026_02_05_200325_create_procedure_test_kit_rows_table.php' WHERE migration = '2026_04_23_211547_create_procedure_test_kit_rows_table.php';
UPDATE migrations SET migration = '2026_02_05_200326_create_procedure_test_kit_values_table.php' WHERE migration = '2026_04_23_211548_create_procedure_test_kit_values_table.php';
UPDATE migrations SET migration = '2026_02_05_200327_create_procedure_worksheet_step_analysts_table.php' WHERE migration = '2026_04_23_211549_create_procedure_worksheet_step_analysts_table.php';
UPDATE migrations SET migration = '2026_02_05_200328_create_procedure_worksheet_steps_table.php' WHERE migration = '2026_04_23_211550_create_procedure_worksheet_steps_table.php';
UPDATE migrations SET migration = '2026_02_05_200329_create_procedure_worksheets_table.php' WHERE migration = '2026_04_23_211551_create_procedure_worksheets_table.php';
UPDATE migrations SET migration = '2026_02_05_200330_create_qc_approvers_config_table.php' WHERE migration = '2026_04_23_211552_create_qc_approvers_config_table.php';
UPDATE migrations SET migration = '2026_02_05_200331_create_qc_processed_result_table.php' WHERE migration = '2026_04_23_211553_create_qc_processed_result_table.php';
UPDATE migrations SET migration = '2026_02_05_200332_create_qc_results_table.php' WHERE migration = '2026_04_23_211554_create_qc_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200333_create_qc_scheme_table.php' WHERE migration = '2026_04_23_211555_create_qc_scheme_table.php';
UPDATE migrations SET migration = '2026_02_05_200334_create_qc_types_table.php' WHERE migration = '2026_04_23_211556_create_qc_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200335_create_qualifications_table.php' WHERE migration = '2026_04_23_211557_create_qualifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200336_create_quotation_details_table.php' WHERE migration = '2026_04_23_211558_create_quotation_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200337_create_quotation_details_analysis_type_table.php' WHERE migration = '2026_04_23_211559_create_quotation_details_analysis_type_table.php';
UPDATE migrations SET migration = '2026_02_05_200338_create_quotation_headers_table.php' WHERE migration = '2026_04_23_211600_create_quotation_headers_table.php';
UPDATE migrations SET migration = '2026_02_05_200339_create_rating_criterias_table.php' WHERE migration = '2026_04_23_211601_create_rating_criterias_table.php';
UPDATE migrations SET migration = '2026_02_05_200340_create_rating_details_table.php' WHERE migration = '2026_04_23_211602_create_rating_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200341_create_rating_headers_table.php' WHERE migration = '2026_04_23_211603_create_rating_headers_table.php';
UPDATE migrations SET migration = '2026_02_05_200342_create_rca_statuses_table.php' WHERE migration = '2026_04_23_211604_create_rca_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200343_create_remedy_details_table.php' WHERE migration = '2026_04_23_211605_create_remedy_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200344_create_remedy_headers_table.php' WHERE migration = '2026_04_23_211606_create_remedy_headers_table.php';
UPDATE migrations SET migration = '2026_02_05_200345_create_report_format_details_table.php' WHERE migration = '2026_04_23_211607_create_report_format_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200346_create_report_format_sample_analysis_stage_table.php' WHERE migration = '2026_04_23_211608_create_report_format_sample_analysis_stage_table.php';
UPDATE migrations SET migration = '2026_02_05_200347_create_report_format_sections_table.php' WHERE migration = '2026_04_23_211609_create_report_format_sections_table.php';
UPDATE migrations SET migration = '2026_02_05_200348_create_report_formats_table.php' WHERE migration = '2026_04_23_211610_create_report_formats_table.php';
UPDATE migrations SET migration = '2026_02_05_200349_create_report_header_details_table.php' WHERE migration = '2026_04_23_211611_create_report_header_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200350_create_report_table_configurations_table.php' WHERE migration = '2026_04_23_211612_create_report_table_configurations_table.php';
UPDATE migrations SET migration = '2026_02_05_200351_create_reporting_units_table.php' WHERE migration = '2026_04_23_211613_create_reporting_units_table.php';
UPDATE migrations SET migration = '2026_02_05_200352_create_request_entities_table.php' WHERE migration = '2026_04_23_211614_create_request_entities_table.php';
UPDATE migrations SET migration = '2026_02_05_200353_create_request_entity_items_table.php' WHERE migration = '2026_04_23_211615_create_request_entity_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200354_create_request_types_table.php' WHERE migration = '2026_04_23_211616_create_request_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200355_create_requisition_locations_table.php' WHERE migration = '2026_04_23_211617_create_requisition_locations_table.php';
UPDATE migrations SET migration = '2026_02_05_200356_create_results_table.php' WHERE migration = '2026_04_23_211618_create_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200357_create_risk_acceptance_criteria_table.php' WHERE migration = '2026_04_23_211619_create_risk_acceptance_criteria_table.php';
UPDATE migrations SET migration = '2026_02_05_200358_create_risk_assessments_table.php' WHERE migration = '2026_04_23_211620_create_risk_assessments_table.php';
UPDATE migrations SET migration = '2026_02_05_200359_create_risk_attachments_table.php' WHERE migration = '2026_04_23_211621_create_risk_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200400_create_risk_business_processes_table.php' WHERE migration = '2026_04_23_211622_create_risk_business_processes_table.php';
UPDATE migrations SET migration = '2026_02_05_200401_create_risk_categories_table.php' WHERE migration = '2026_04_23_211623_create_risk_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200402_create_risk_configuration_options_table.php' WHERE migration = '2026_04_23_211624_create_risk_configuration_options_table.php';
UPDATE migrations SET migration = '2026_02_05_200403_create_risk_evaluations_table.php' WHERE migration = '2026_04_23_211625_create_risk_evaluations_table.php';
UPDATE migrations SET migration = '2026_02_05_200404_create_risk_level_thresholds_table.php' WHERE migration = '2026_04_23_211626_create_risk_level_thresholds_table.php';
UPDATE migrations SET migration = '2026_02_05_200405_create_risk_levels_table.php' WHERE migration = '2026_04_23_211627_create_risk_levels_table.php';
UPDATE migrations SET migration = '2026_02_05_200406_create_risk_notifications_table.php' WHERE migration = '2026_04_23_211628_create_risk_notifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200407_create_risk_process_links_table.php' WHERE migration = '2026_04_23_211629_create_risk_process_links_table.php';
UPDATE migrations SET migration = '2026_02_05_200408_create_risk_review_frequencies_table.php' WHERE migration = '2026_04_23_211630_create_risk_review_frequencies_table.php';
UPDATE migrations SET migration = '2026_02_05_200409_create_risk_reviews_table.php' WHERE migration = '2026_04_23_211631_create_risk_reviews_table.php';
UPDATE migrations SET migration = '2026_02_05_200410_create_risk_scoring_configs_table.php' WHERE migration = '2026_04_23_211632_create_risk_scoring_configs_table.php';
UPDATE migrations SET migration = '2026_02_05_200411_create_risk_sources_table.php' WHERE migration = '2026_04_23_211633_create_risk_sources_table.php';
UPDATE migrations SET migration = '2026_02_05_200412_create_risk_statuses_table.php' WHERE migration = '2026_04_23_211634_create_risk_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200413_create_risk_treatment_implementations_table.php' WHERE migration = '2026_04_23_211635_create_risk_treatment_implementations_table.php';
UPDATE migrations SET migration = '2026_02_05_200414_create_risk_treatment_plans_table.php' WHERE migration = '2026_04_23_211636_create_risk_treatment_plans_table.php';
UPDATE migrations SET migration = '2026_02_05_200415_create_risks_table.php' WHERE migration = '2026_04_23_211637_create_risks_table.php';
UPDATE migrations SET migration = '2026_02_05_200416_create_role_certifications_table.php' WHERE migration = '2026_04_23_211638_create_role_certifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200417_create_roles_table.php' WHERE migration = '2026_04_23_211639_create_roles_table.php';
UPDATE migrations SET migration = '2026_02_05_200418_create_root_cause_analyses_table.php' WHERE migration = '2026_04_23_211640_create_root_cause_analyses_table.php';
UPDATE migrations SET migration = '2026_02_05_200419_create_root_cause_methods_table.php' WHERE migration = '2026_04_23_211641_create_root_cause_methods_table.php';
UPDATE migrations SET migration = '2026_02_05_200420_create_samaco_sheet_table.php' WHERE migration = '2026_04_23_211642_create_samaco_sheet_table.php';
UPDATE migrations SET migration = '2026_02_05_200421_create_sample_analysis_dates_table.php' WHERE migration = '2026_04_23_211643_create_sample_analysis_dates_table.php';
UPDATE migrations SET migration = '2026_02_05_200422_create_sample_analysis_stages_table.php' WHERE migration = '2026_04_23_211644_create_sample_analysis_stages_table.php';
UPDATE migrations SET migration = '2026_02_05_200423_create_sample_analysis_type_relation_table.php' WHERE migration = '2026_04_23_211645_create_sample_analysis_type_relation_table.php';
UPDATE migrations SET migration = '2026_02_05_200424_create_sample_approval_checklist_table.php' WHERE migration = '2026_04_23_211646_create_sample_approval_checklist_table.php';
UPDATE migrations SET migration = '2026_02_05_200425_create_sample_attachment_relations_table.php' WHERE migration = '2026_04_23_211647_create_sample_attachment_relations_table.php';
UPDATE migrations SET migration = '2026_02_05_200426_create_sample_captured_worksheet_formulas_table.php' WHERE migration = '2026_04_23_211648_create_sample_captured_worksheet_formulas_table.php';
UPDATE migrations SET migration = '2026_02_05_200427_create_sample_conditions_table.php' WHERE migration = '2026_04_23_211649_create_sample_conditions_table.php';
UPDATE migrations SET migration = '2026_02_05_200428_create_sample_dates_table.php' WHERE migration = '2026_04_23_211650_create_sample_dates_table.php';
UPDATE migrations SET migration = '2026_02_05_200429_create_sample_detail_staging_table.php' WHERE migration = '2026_04_23_211651_create_sample_detail_staging_table.php';
UPDATE migrations SET migration = '2026_02_05_200430_create_sample_header_staging_table.php' WHERE migration = '2026_04_23_211652_create_sample_header_staging_table.php';
UPDATE migrations SET migration = '2026_02_05_200431_create_sample_imports_table.php' WHERE migration = '2026_04_23_211653_create_sample_imports_table.php';
UPDATE migrations SET migration = '2026_02_05_200432_create_sample_interlab_log_table.php' WHERE migration = '2026_04_23_211654_create_sample_interlab_log_table.php';
UPDATE migrations SET migration = '2026_02_05_200433_create_sample_point_area_table.php' WHERE migration = '2026_04_23_211655_create_sample_point_area_table.php';
UPDATE migrations SET migration = '2026_02_05_200434_create_sample_points_table.php' WHERE migration = '2026_04_23_211656_create_sample_points_table.php';
UPDATE migrations SET migration = '2026_02_05_200435_create_sample_progress_table.php' WHERE migration = '2026_04_23_211657_create_sample_progress_table.php';
UPDATE migrations SET migration = '2026_02_05_200436_create_sample_sequences_table.php' WHERE migration = '2026_04_23_211658_create_sample_sequences_table.php';
UPDATE migrations SET migration = '2026_02_05_200437_create_sample_staging_rejection_log_table.php' WHERE migration = '2026_04_23_211659_create_sample_staging_rejection_log_table.php';
UPDATE migrations SET migration = '2026_02_05_200438_create_sample_submission_request_exhibits_table.php' WHERE migration = '2026_04_23_211700_create_sample_submission_request_exhibits_table.php';
UPDATE migrations SET migration = '2026_02_05_200439_create_sample_submission_request_requested_analyses_table.php' WHERE migration = '2026_04_23_211701_create_sample_submission_request_requested_analyses_table.php';
UPDATE migrations SET migration = '2026_02_05_200440_create_sample_submission_request_supporting_document_templates_table.php' WHERE migration = '2026_04_23_211702_create_sample_submission_request_supporting_document_templates_table.php';
UPDATE migrations SET migration = '2026_02_05_200441_create_sample_submission_request_suspects_table.php' WHERE migration = '2026_04_23_211703_create_sample_submission_request_suspects_table.php';
UPDATE migrations SET migration = '2026_02_05_200442_create_sample_submission_requests_table.php' WHERE migration = '2026_04_23_211704_create_sample_submission_requests_table.php';
UPDATE migrations SET migration = '2026_02_05_200443_create_sample_to_sample_analysis_stages_table.php' WHERE migration = '2026_04_23_211705_create_sample_to_sample_analysis_stages_table.php';
UPDATE migrations SET migration = '2026_02_05_200444_create_sample_type_categories_table.php' WHERE migration = '2026_04_23_211706_create_sample_type_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200445_create_sample_type_qualifications_table.php' WHERE migration = '2026_04_23_211707_create_sample_type_qualifications_table.php';
UPDATE migrations SET migration = '2026_02_05_200446_create_sample_types_table.php' WHERE migration = '2026_04_23_211708_create_sample_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200447_create_sample_worksheet_formular_mandatory_data_table.php' WHERE migration = '2026_04_23_211709_create_sample_worksheet_formular_mandatory_data_table.php';
UPDATE migrations SET migration = '2026_02_05_200448_create_sample_worksheet_formular_step_data_table.php' WHERE migration = '2026_04_23_211710_create_sample_worksheet_formular_step_data_table.php';
UPDATE migrations SET migration = '2026_02_05_200449_create_sampletype_area_relation_table.php' WHERE migration = '2026_04_23_211711_create_sampletype_area_relation_table.php';
UPDATE migrations SET migration = '2026_02_05_200450_create_sampletype_sample_point_relation_table.php' WHERE migration = '2026_04_23_211712_create_sampletype_sample_point_relation_table.php';
UPDATE migrations SET migration = '2026_02_05_200451_create_ser_header_worksheet_sample_relations_table.php' WHERE migration = '2026_04_23_211713_create_ser_header_worksheet_sample_relations_table.php';
UPDATE migrations SET migration = '2026_02_05_200452_create_ser_step_worksheet_sample_relations_table.php' WHERE migration = '2026_04_23_211714_create_ser_step_worksheet_sample_relations_table.php';
UPDATE migrations SET migration = '2026_02_05_200453_create_ser_testkit_worksheet_sample_relations_table.php' WHERE migration = '2026_04_23_211715_create_ser_testkit_worksheet_sample_relations_table.php';
UPDATE migrations SET migration = '2026_02_05_200454_create_ser_worksheet_steps_table.php' WHERE migration = '2026_04_23_211716_create_ser_worksheet_steps_table.php';
UPDATE migrations SET migration = '2026_02_05_200455_create_service_confirmation_items_table.php' WHERE migration = '2026_04_23_211717_create_service_confirmation_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200456_create_services_table.php' WHERE migration = '2026_04_23_211718_create_services_table.php';
UPDATE migrations SET migration = '2026_02_05_200457_create_severity_scales_table.php' WHERE migration = '2026_04_23_211719_create_severity_scales_table.php';
UPDATE migrations SET migration = '2026_02_05_200458_create_skill_capability_detail_table.php' WHERE migration = '2026_04_23_211720_create_skill_capability_detail_table.php';
UPDATE migrations SET migration = '2026_02_05_200459_create_skill_other_training_users_table.php' WHERE migration = '2026_04_23_211721_create_skill_other_training_users_table.php';
UPDATE migrations SET migration = '2026_02_05_200500_create_skill_training_header_table.php' WHERE migration = '2026_04_23_211722_create_skill_training_header_table.php';
UPDATE migrations SET migration = '2026_02_05_200501_create_skill_training_header_staff_table.php' WHERE migration = '2026_04_23_211723_create_skill_training_header_staff_table.php';
UPDATE migrations SET migration = '2026_02_05_200502_create_skills_capability_matrix_table.php' WHERE migration = '2026_04_23_211724_create_skills_capability_matrix_table.php';
UPDATE migrations SET migration = '2026_02_05_200503_create_skills_capability_matrix_role_table.php' WHERE migration = '2026_04_23_211725_create_skills_capability_matrix_role_table.php';
UPDATE migrations SET migration = '2026_02_05_200504_create_skills_matrix_configurations_table.php' WHERE migration = '2026_04_23_211726_create_skills_matrix_configurations_table.php';
UPDATE migrations SET migration = '2026_02_05_200505_create_skills_matrix_detail_table.php' WHERE migration = '2026_04_23_211727_create_skills_matrix_detail_table.php';
UPDATE migrations SET migration = '2026_02_05_200506_create_skills_matrix_detail_role_table.php' WHERE migration = '2026_04_23_211728_create_skills_matrix_detail_role_table.php';
UPDATE migrations SET migration = '2026_02_05_200507_create_skills_matrix_role_table.php' WHERE migration = '2026_04_23_211729_create_skills_matrix_role_table.php';
UPDATE migrations SET migration = '2026_02_05_200508_create_skills_matrix_role_requirments_table.php' WHERE migration = '2026_04_23_211730_create_skills_matrix_role_requirments_table.php';
UPDATE migrations SET migration = '2026_02_05_200509_create_skills_training_detail_table.php' WHERE migration = '2026_04_23_211731_create_skills_training_detail_table.php';
UPDATE migrations SET migration = '2026_02_05_200510_create_skills_training_planner_detail_table.php' WHERE migration = '2026_04_23_211732_create_skills_training_planner_detail_table.php';
UPDATE migrations SET migration = '2026_02_05_200511_create_skills_training_planner_header_table.php' WHERE migration = '2026_04_23_211733_create_skills_training_planner_header_table.php';
UPDATE migrations SET migration = '2026_02_05_200512_create_skillsmatrices_table.php' WHERE migration = '2026_04_23_211734_create_skillsmatrices_table.php';
UPDATE migrations SET migration = '2026_02_05_200513_create_solution_batch_history_table.php' WHERE migration = '2026_04_23_211735_create_solution_batch_history_table.php';
UPDATE migrations SET migration = '2026_02_05_200514_create_solution_consumption_metrics_table.php' WHERE migration = '2026_04_23_211736_create_solution_consumption_metrics_table.php';
UPDATE migrations SET migration = '2026_02_05_200515_create_solution_preparations_table.php' WHERE migration = '2026_04_23_211737_create_solution_preparations_table.php';
UPDATE migrations SET migration = '2026_02_05_200516_create_spatie_model_has_permissions_table.php' WHERE migration = '2026_04_23_211738_create_spatie_model_has_permissions_table.php';
UPDATE migrations SET migration = '2026_02_05_200517_create_spatie_model_has_roles_table.php' WHERE migration = '2026_04_23_211739_create_spatie_model_has_roles_table.php';
UPDATE migrations SET migration = '2026_02_05_200518_create_spatie_permissions_table.php' WHERE migration = '2026_04_23_211740_create_spatie_permissions_table.php';
UPDATE migrations SET migration = '2026_02_05_200519_create_spatie_role_has_permissions_table.php' WHERE migration = '2026_04_23_211741_create_spatie_role_has_permissions_table.php';
UPDATE migrations SET migration = '2026_02_05_200520_create_spatie_roles_table.php' WHERE migration = '2026_04_23_211742_create_spatie_roles_table.php';
UPDATE migrations SET migration = '2026_02_05_200521_create_stage_headers_table.php' WHERE migration = '2026_04_23_211743_create_stage_headers_table.php';
UPDATE migrations SET migration = '2026_02_05_200522_create_standard_values_table.php' WHERE migration = '2026_04_23_211744_create_standard_values_table.php';
UPDATE migrations SET migration = '2026_02_05_200523_create_standards_table.php' WHERE migration = '2026_04_23_211745_create_standards_table.php';
UPDATE migrations SET migration = '2026_02_05_200524_create_captured_results_table.php' WHERE migration = '2026_04_23_211746_create_captured_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200525_create_sample_details_table.php' WHERE migration = '2026_04_23_211747_create_sample_details_table.php';
UPDATE migrations SET migration = '2026_02_05_200526_create_standards_analytes_table.php' WHERE migration = '2026_04_23_211748_create_standards_analytes_table.php';
UPDATE migrations SET migration = '2026_02_05_200527_create_stock_taking_counters_table.php' WHERE migration = '2026_04_23_211749_create_stock_taking_counters_table.php';
UPDATE migrations SET migration = '2026_02_05_200528_create_stock_taking_sheets_table.php' WHERE migration = '2026_04_23_211750_create_stock_taking_sheets_table.php';
UPDATE migrations SET migration = '2026_02_05_200529_create_stock_takings_table.php' WHERE migration = '2026_04_23_211751_create_stock_takings_table.php';
UPDATE migrations SET migration = '2026_02_05_200530_create_stock_transfer_items_table.php' WHERE migration = '2026_04_23_211752_create_stock_transfer_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200531_create_stock_transfers_table.php' WHERE migration = '2026_04_23_211753_create_stock_transfers_table.php';
UPDATE migrations SET migration = '2026_02_05_200532_create_submission_form_audit_log_table.php' WHERE migration = '2026_04_23_211754_create_submission_form_audit_log_table.php';
UPDATE migrations SET migration = '2026_02_05_200533_create_submission_form_audit_logs_table.php' WHERE migration = '2026_04_23_211755_create_submission_form_audit_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200534_create_submission_form_element_holders_table.php' WHERE migration = '2026_04_23_211756_create_submission_form_element_holders_table.php';
UPDATE migrations SET migration = '2026_02_05_200535_create_submission_form_elements_table.php' WHERE migration = '2026_04_23_211757_create_submission_form_elements_table.php';
UPDATE migrations SET migration = '2026_02_05_200536_create_submission_form_instance_values_table.php' WHERE migration = '2026_04_23_211758_create_submission_form_instance_values_table.php';
UPDATE migrations SET migration = '2026_02_05_200537_create_submission_form_instances_table.php' WHERE migration = '2026_04_23_211759_create_submission_form_instances_table.php';
UPDATE migrations SET migration = '2026_02_05_200538_create_submission_form_permissions_table.php' WHERE migration = '2026_04_23_211800_create_submission_form_permissions_table.php';
UPDATE migrations SET migration = '2026_02_05_200539_create_submission_form_sample_analysis_stage_table.php' WHERE migration = '2026_04_23_211801_create_submission_form_sample_analysis_stage_table.php';
UPDATE migrations SET migration = '2026_02_05_200540_create_submission_form_sections_table.php' WHERE migration = '2026_04_23_211802_create_submission_form_sections_table.php';
UPDATE migrations SET migration = '2026_02_05_200541_create_submission_forms_table.php' WHERE migration = '2026_04_23_211803_create_submission_forms_table.php';
UPDATE migrations SET migration = '2026_02_05_200542_create_supplier_brands_table.php' WHERE migration = '2026_04_23_211804_create_supplier_brands_table.php';
UPDATE migrations SET migration = '2026_02_05_200543_create_supplier_by_categories_table.php' WHERE migration = '2026_04_23_211805_create_supplier_by_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200544_create_supplier_categories_table.php' WHERE migration = '2026_04_23_211806_create_supplier_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200545_create_supplier_contacts_table.php' WHERE migration = '2026_04_23_211807_create_supplier_contacts_table.php';
UPDATE migrations SET migration = '2026_02_05_200546_create_supplier_contract_items_table.php' WHERE migration = '2026_04_23_211808_create_supplier_contract_items_table.php';
UPDATE migrations SET migration = '2026_02_05_200547_create_supplier_contracts_table.php' WHERE migration = '2026_04_23_211809_create_supplier_contracts_table.php';
UPDATE migrations SET migration = '2026_02_05_200548_create_supplier_quote_attachments_table.php' WHERE migration = '2026_04_23_211810_create_supplier_quote_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200549_create_supplier_quote_notes_table.php' WHERE migration = '2026_04_23_211811_create_supplier_quote_notes_table.php';
UPDATE migrations SET migration = '2026_02_05_200550_create_supplier_quotes_table.php' WHERE migration = '2026_04_23_211812_create_supplier_quotes_table.php';
UPDATE migrations SET migration = '2026_02_05_200551_create_supplier_r_f_q_s_table.php' WHERE migration = '2026_04_23_211813_create_supplier_r_f_q_s_table.php';
UPDATE migrations SET migration = '2026_02_05_200552_create_supplier_rating_criteria_guide_supplier_scores_table.php' WHERE migration = '2026_04_23_211814_create_supplier_rating_criteria_guide_supplier_scores_table.php';
UPDATE migrations SET migration = '2026_02_05_200553_create_supplier_rating_criteria_guides_table.php' WHERE migration = '2026_04_23_211815_create_supplier_rating_criteria_guides_table.php';
UPDATE migrations SET migration = '2026_02_05_200554_create_suppliers_table.php' WHERE migration = '2026_04_23_211816_create_suppliers_table.php';
UPDATE migrations SET migration = '2026_02_05_200555_create_suppliers_categories_table.php' WHERE migration = '2026_04_23_211817_create_suppliers_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200556_create_suppliers_rating_criterias_table.php' WHERE migration = '2026_04_23_211818_create_suppliers_rating_criterias_table.php';
UPDATE migrations SET migration = '2026_02_05_200557_create_supporting_document_elements_table.php' WHERE migration = '2026_04_23_211819_create_supporting_document_elements_table.php';
UPDATE migrations SET migration = '2026_02_05_200558_create_supporting_document_instance_values_table.php' WHERE migration = '2026_04_23_211820_create_supporting_document_instance_values_table.php';
UPDATE migrations SET migration = '2026_02_05_200559_create_supporting_document_instances_table.php' WHERE migration = '2026_04_23_211821_create_supporting_document_instances_table.php';
UPDATE migrations SET migration = '2026_02_05_200600_create_supporting_document_sections_table.php' WHERE migration = '2026_04_23_211822_create_supporting_document_sections_table.php';
UPDATE migrations SET migration = '2026_02_05_200601_create_supporting_document_templates_table.php' WHERE migration = '2026_04_23_211823_create_supporting_document_templates_table.php';
UPDATE migrations SET migration = '2026_02_05_200602_create_system_configuration_types_table.php' WHERE migration = '2026_04_23_211824_create_system_configuration_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200603_create_system_configurations_table.php' WHERE migration = '2026_04_23_211825_create_system_configurations_table.php';
UPDATE migrations SET migration = '2026_02_05_200604_create_tat_captured_table.php' WHERE migration = '2026_04_23_211826_create_tat_captured_table.php';
UPDATE migrations SET migration = '2026_02_05_200605_create_tax_regime_table.php' WHERE migration = '2026_04_23_211827_create_tax_regime_table.php';
UPDATE migrations SET migration = '2026_02_05_200606_create_temp_sample_data_table.php' WHERE migration = '2026_04_23_211828_create_temp_sample_data_table.php';
UPDATE migrations SET migration = '2026_02_05_200607_create_test_stages_table.php' WHERE migration = '2026_04_23_211829_create_test_stages_table.php';
UPDATE migrations SET migration = '2026_02_05_200608_create_ticket_assignments_table.php' WHERE migration = '2026_04_23_211830_create_ticket_assignments_table.php';
UPDATE migrations SET migration = '2026_02_05_200609_create_ticket_categories_table.php' WHERE migration = '2026_04_23_211831_create_ticket_categories_table.php';
UPDATE migrations SET migration = '2026_02_05_200610_create_ticket_change_history_table.php' WHERE migration = '2026_04_23_211832_create_ticket_change_history_table.php';
UPDATE migrations SET migration = '2026_02_05_200611_create_ticket_chat_table.php' WHERE migration = '2026_04_23_211833_create_ticket_chat_table.php';
UPDATE migrations SET migration = '2026_02_05_200612_create_ticket_chat_attachments_table.php' WHERE migration = '2026_04_23_211834_create_ticket_chat_attachments_table.php';
UPDATE migrations SET migration = '2026_02_05_200613_create_ticket_comments_table.php' WHERE migration = '2026_04_23_211835_create_ticket_comments_table.php';
UPDATE migrations SET migration = '2026_02_05_200614_create_ticket_permissions_table.php' WHERE migration = '2026_04_23_211836_create_ticket_permissions_table.php';
UPDATE migrations SET migration = '2026_02_05_200615_create_ticket_priorities_table.php' WHERE migration = '2026_04_23_211837_create_ticket_priorities_table.php';
UPDATE migrations SET migration = '2026_02_05_200616_create_ticket_statuses_table.php' WHERE migration = '2026_04_23_211838_create_ticket_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200617_create_ticket_team_chat_table.php' WHERE migration = '2026_04_23_211839_create_ticket_team_chat_table.php';
UPDATE migrations SET migration = '2026_02_05_200618_create_topologies_table.php' WHERE migration = '2026_04_23_211840_create_topologies_table.php';
UPDATE migrations SET migration = '2026_02_05_200619_create_treatment_types_table.php' WHERE migration = '2026_04_23_211841_create_treatment_types_table.php';
UPDATE migrations SET migration = '2026_02_05_200620_create_uncertainty_budgets_table.php' WHERE migration = '2026_04_23_211842_create_uncertainty_budgets_table.php';
UPDATE migrations SET migration = '2026_02_05_200621_create_uncertainty_sources_table.php' WHERE migration = '2026_04_23_211843_create_uncertainty_sources_table.php';
UPDATE migrations SET migration = '2026_02_05_200622_create_unit_of_measure_conversions_table.php' WHERE migration = '2026_04_23_211844_create_unit_of_measure_conversions_table.php';
UPDATE migrations SET migration = '2026_02_05_200623_create_uom_conversions_table.php' WHERE migration = '2026_04_23_211845_create_uom_conversions_table.php';
UPDATE migrations SET migration = '2026_02_05_200624_create_user_alerts_table.php' WHERE migration = '2026_04_23_211846_create_user_alerts_table.php';
UPDATE migrations SET migration = '2026_02_05_200625_create_user_departmental_approvals_table.php' WHERE migration = '2026_04_23_211847_create_user_departmental_approvals_table.php';
UPDATE migrations SET migration = '2026_02_05_200626_create_user_roles_table.php' WHERE migration = '2026_04_23_211848_create_user_roles_table.php';
UPDATE migrations SET migration = '2026_02_05_200627_create_users_table.php' WHERE migration = '2026_04_23_211849_create_users_table.php';
UPDATE migrations SET migration = '2026_02_05_200628_create_batch_ammendments_table.php' WHERE migration = '2026_04_23_211850_create_batch_ammendments_table.php';
UPDATE migrations SET migration = '2026_02_05_200629_create_sample_headers_table.php' WHERE migration = '2026_04_23_211851_create_sample_headers_table.php';
UPDATE migrations SET migration = '2026_02_05_200630_create_verification_closure_statuses_table.php' WHERE migration = '2026_04_23_211852_create_verification_closure_statuses_table.php';
UPDATE migrations SET migration = '2026_02_05_200631_create_verification_logs_table.php' WHERE migration = '2026_04_23_211853_create_verification_logs_table.php';
UPDATE migrations SET migration = '2026_02_05_200632_create_verification_records_table.php' WHERE migration = '2026_04_23_211854_create_verification_records_table.php';
UPDATE migrations SET migration = '2026_02_05_200633_create_verification_results_table.php' WHERE migration = '2026_04_23_211855_create_verification_results_table.php';
UPDATE migrations SET migration = '2026_02_05_200634_create_work_order_resources_table.php' WHERE migration = '2026_04_23_211856_create_work_order_resources_table.php';
UPDATE migrations SET migration = '2026_02_05_200635_create_work_order_status_histories_table.php' WHERE migration = '2026_04_23_211857_create_work_order_status_histories_table.php';
UPDATE migrations SET migration = '2026_02_05_200636_create_work_orders_table.php' WHERE migration = '2026_04_23_211858_create_work_orders_table.php';
UPDATE migrations SET migration = '2026_02_05_200637_create_workflow_actions_table.php' WHERE migration = '2026_04_23_211859_create_workflow_actions_table.php';
UPDATE migrations SET migration = '2026_02_05_200638_create_workorder_edits_table.php' WHERE migration = '2026_04_23_211900_create_workorder_edits_table.php';
UPDATE migrations SET migration = '2026_02_05_200639_create_workorder_personnel_schedules_table.php' WHERE migration = '2026_04_23_211901_create_workorder_personnel_schedules_table.php';
UPDATE migrations SET migration = '2026_02_05_200640_create_worksheet_executions_table.php' WHERE migration = '2026_04_23_211902_create_worksheet_executions_table.php';
UPDATE migrations SET migration = '2026_02_05_200641_create_worksheet_external_samples_table.php' WHERE migration = '2026_04_23_211903_create_worksheet_external_samples_table.php';
UPDATE migrations SET migration = '2026_02_05_200642_create_zoho_api_tokens_table.php' WHERE migration = '2026_04_23_211904_create_zoho_api_tokens_table.php';
UPDATE migrations SET migration = '2026_02_05_200643_create_zoho_customers_table.php' WHERE migration = '2026_04_23_211905_create_zoho_customers_table.php';
UPDATE migrations SET migration = '2026_02_05_200644_create_zones_table.php' WHERE migration = '2026_04_23_211906_create_zones_table.php';
UPDATE migrations SET migration = '2026_02_05_200645_create_portal_access_requests_table.php' WHERE migration = '2026_04_23_211907_create_portal_access_requests_table.php';

-- Report any old names still left (should return 0 rows)
-- SELECT migration FROM migrations WHERE migration LIKE '2026_04_23_211%' ORDER BY migration;

-- Report any new names that exist without a matching update (investigate duplicates)
-- SELECT migration FROM migrations WHERE migration LIKE '2026_02_05_200%' ORDER BY migration;

COMMIT;

