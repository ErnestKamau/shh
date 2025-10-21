-- ============================================================================
-- TRUNCATE SCRIPT FOR SAMPLE HEADERS AND SUBMISSION FORM INSTANCES
-- ============================================================================
-- WARNING: This will delete ALL data from these tables and related tables!
-- Make sure you have a backup before running this script.
-- ============================================================================
--
-- RECENTLY ADDED TABLES (October 2025):
-- - equipment_usage (tracks equipment used for sample analysis)
-- - batch_approval_checklist (batch approval tracking)
-- - sample_sequences (sample sequence numbering)
-- - method_sequence_run_stage_data (method sequence stage execution data)
-- - method_sequence_stage_control_results (quality control results per stage)
-- - method_sequence_stage_control_usage (control usage tracking)
-- - method_sequence_stage_equipment_usage (equipment usage per stage)
-- - method_sequence_stage_media_usage (media/reagent usage per stage)
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. SUBMISSION FORM INSTANCES AND RELATED TABLES
-- ============================================================================

-- Tables that depend on submission_form_instances
TRUNCATE TABLE `worksheet_executions`;
TRUNCATE TABLE `batch_sequences`;
TRUNCATE TABLE `certificate_template_reports`;
TRUNCATE TABLE `submission_form_audit_log`;
TRUNCATE TABLE `submission_form_audit_logs`;
TRUNCATE TABLE `submission_form_instance_values`;

-- Main submission form instances table
TRUNCATE TABLE `submission_form_instances`;


-- ============================================================================
-- 2. SAMPLE HEADERS AND RELATED TABLES (in dependency order)
-- ============================================================================

-- Tables that depend on sample_details (must be truncated before sample_details)
TRUNCATE TABLE `results`;
TRUNCATE TABLE `captured_results`;
TRUNCATE TABLE `sample_analysis_type_relation`;
TRUNCATE TABLE `sample_interlab_log`;
TRUNCATE TABLE `method_sequence_run_samples`;
TRUNCATE TABLE `method_sequence_stage_sample_results`;
TRUNCATE TABLE `sample_captured_worksheet_formulas`;
TRUNCATE TABLE `sample_worksheet_formular_step_data`;
TRUNCATE TABLE `sample_worksheet_formular_mandatory_data`;
TRUNCATE TABLE `sample_attachment_relations`;
TRUNCATE TABLE `sample_to_sample_analysis_stages`;
TRUNCATE TABLE `sample_detail_staging`;
TRUNCATE TABLE `sample_progress`;

-- Sample details (depends on sample_headers)
TRUNCATE TABLE `sample_details`;

-- Tables that depend directly on sample_headers
TRUNCATE TABLE `batch_ammendments`;
TRUNCATE TABLE `batch_attachments`;
TRUNCATE TABLE `batch_comments`;
TRUNCATE TABLE `batch_labsection_approval`;
TRUNCATE TABLE `batch_notifications`;
TRUNCATE TABLE `chain_of_custodies`;
TRUNCATE TABLE `sample_analysis_dates`;
TRUNCATE TABLE `sample_dates`;
TRUNCATE TABLE `sample_approval_checklist`;
TRUNCATE TABLE `tat_captured`;
TRUNCATE TABLE `sample_staging_rejection_log`;
TRUNCATE TABLE `report_header_details`;
TRUNCATE TABLE `qc_results`;
TRUNCATE TABLE `equipment_usage`;
TRUNCATE TABLE `batch_approval_checklist`;

-- Invoice tables that may reference sample_headers/batches
TRUNCATE TABLE `invoice_details`;
TRUNCATE TABLE `invoice_payment_details`;

-- Import and temp tables
TRUNCATE TABLE `import_lab_results`;
TRUNCATE TABLE `lab_results_excel`;
TRUNCATE TABLE `temp_sample_data`;
TRUNCATE TABLE `sample_imports`;

-- Main sample headers table
TRUNCATE TABLE `sample_headers`;

-- Sequence tracking (references batch_code from sample_headers)
TRUNCATE TABLE `sample_sequences`;

-- Sample header staging
TRUNCATE TABLE `sample_header_staging`;

-- ============================================================================
-- 3. METHOD SEQUENCE RELATED TABLES
-- ============================================================================

-- Tables that depend on method_sequence_run_stage_data
TRUNCATE TABLE `method_sequence_stage_control_results`;
TRUNCATE TABLE `method_sequence_stage_control_usage`;
TRUNCATE TABLE `method_sequence_stage_equipment_usage`;
TRUNCATE TABLE `method_sequence_stage_media_usage`;

-- Method sequence run stage data (depends on method_sequence_runs)
TRUNCATE TABLE `method_sequence_run_stage_data`;

-- Main method sequence runs table
TRUNCATE TABLE `method_sequence_runs`;


-- ============================================================================
-- RE-ENABLE FOREIGN KEY CHECKS
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VERIFICATION QUERIES (Optional - run these to verify truncation)
-- ============================================================================
/*
SELECT 'sample_headers' AS table_name, COUNT(*) AS row_count FROM sample_headers
UNION ALL
SELECT 'sample_details', COUNT(*) FROM sample_details
UNION ALL
SELECT 'sample_sequences', COUNT(*) FROM sample_sequences
UNION ALL
SELECT 'submission_form_instances', COUNT(*) FROM submission_form_instances
UNION ALL
SELECT 'submission_form_instance_values', COUNT(*) FROM submission_form_instance_values
UNION ALL
SELECT 'submission_form_audit_logs', COUNT(*) FROM submission_form_audit_logs
UNION ALL
SELECT 'results', COUNT(*) FROM results
UNION ALL
SELECT 'captured_results', COUNT(*) FROM captured_results
UNION ALL
SELECT 'equipment_usage', COUNT(*) FROM equipment_usage
UNION ALL
SELECT 'batch_approval_checklist', COUNT(*) FROM batch_approval_checklist
UNION ALL
SELECT 'method_sequence_runs', COUNT(*) FROM method_sequence_runs
UNION ALL
SELECT 'method_sequence_run_stage_data', COUNT(*) FROM method_sequence_run_stage_data;
*/

