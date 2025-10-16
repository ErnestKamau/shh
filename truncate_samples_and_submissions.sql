-- ============================================================================
-- TRUNCATE SCRIPT FOR SAMPLE HEADERS AND SUBMISSION FORM INSTANCES
-- ============================================================================
-- WARNING: This will delete ALL data from these tables and related tables!
-- Make sure you have a backup before running this script.
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
TRUNCATE TABLE `sample_progress`;
TRUNCATE TABLE `tat_captured`;
TRUNCATE TABLE `sample_staging_rejection_log`;
TRUNCATE TABLE `report_header_details`;
TRUNCATE TABLE `qc_results`;

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

-- Sample header staging
TRUNCATE TABLE `sample_header_staging`;

-- ============================================================================
-- 3. METHOD SEQUENCE RELATED TABLES
-- ============================================================================
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
SELECT 'submission_form_instances', COUNT(*) FROM submission_form_instances
UNION ALL
SELECT 'submission_form_instance_values', COUNT(*) FROM submission_form_instance_values
UNION ALL
SELECT 'results', COUNT(*) FROM results
UNION ALL
SELECT 'captured_results', COUNT(*) FROM captured_results;
*/

