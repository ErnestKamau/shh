SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE sample_headers;
TRUNCATE TABLE sample_details;
TRUNCATE TABLE captured_results;
TRUNCATE TABLE results;
TRUNCATE TABLE submission_form_instances;
TRUNCATE TABLE submission_form_instance_values;
TRUNCATE TABLE chain_of_custodies;
TRUNCATE TABLE tat_captured;
Truncate Table sample_dates;
Truncate Table sample_detail_staging;
Truncate Table sample_analysis_type_relation;
Truncate Table sample_analysis_dates;
Truncate Table batch_attachments;
Truncate Table batch_comments;
Truncate Table batch_labsection_approval;
TRUNCATE TABLE ser_header_worksheet_sample_relations;
TRUNCATE TABLE ser_step_worksheet_sample_relations;
TRUNCATE TABLE ser_testkit_worksheet_sample_relations;
TRUNCATE TABLE method_sequence_stage_sample_results;
TRUNCATE TABLE method_sequence_stage_control_results;

SET FOREIGN_KEY_CHECKS = 1;
 