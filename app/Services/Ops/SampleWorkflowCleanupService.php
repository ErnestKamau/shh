<?php

namespace App\Services\Ops;

use App\Services\Ops\Concerns\PurgesDatabaseTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SampleWorkflowCleanupService
{
    use PurgesDatabaseTables;

    /**
     * Delete sample batches (jobs), submission requests, request-linked TRF instances,
     * and sampling schedules. Preserves TRF templates, catalog/master data, quotations,
     * and pricelists.
     *
     * @return array<string, int>
     */
    public function deleteAll(): array
    {
        $counts = [];

        $this->purgeSamples($counts);
        $this->purgeRequests($counts);
        $this->purgeSamplingSchedules($counts);

        return $counts;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function purgeSamples(array &$counts): void
    {
        $counts['sample_submission_requests.sample_header_id'] = $this->nullColumn(
            'sample_submission_requests',
            'sample_header_id'
        );
        $counts['analysis_methods.sample_header_id'] = $this->nullColumn('analysis_methods', 'sample_header_id');
        $counts['lab_results_excel.sample_header_id'] = $this->nullColumn('lab_results_excel', 'sample_header_id');
        $counts['worksheet_executions.batch_id'] = $this->nullColumn('worksheet_executions', 'batch_id');
        $counts['worksheet_executions.sample_id'] = $this->nullColumn('worksheet_executions', 'sample_id');
        $counts['invoice_payment_details.batch_id'] = $this->nullColumn('invoice_payment_details', 'batch_id');
        $counts['risks.sample_id'] = $this->nullColumn('risks', 'sample_id');
        $counts['non_conformances.sample_id'] = $this->nullColumn('non_conformances', 'sample_id');

        foreach ($this->sampleBatchTables() as $table) {
            $counts[$table] = $this->deleteAllRows($table);
        }
    }

    /**
     * @return list<string>
     */
    private function sampleBatchTables(): array
    {
        return [
            'method_sequence_stage_control_results',
            'method_sequence_stage_control_usage',
            'method_sequence_stage_equipment_usage',
            'method_sequence_stage_media_usage',
            'method_sequence_stage_sample_results',
            'method_sequence_run_stage_data',
            'method_sequence_run_samples',
            'method_sequence_runs',
            'sample_worksheet_formular_mandatory_data',
            'sample_worksheet_formular_step_data',
            'sample_formula_step_table_cell_values',
            'sample_formula_step_table_rows',
            'sample_formula_step_table_instances',
            'sample_procedure_step_table_cell_values',
            'sample_procedure_step_table_rows',
            'sample_procedure_step_table_instances',
            'sample_log_entry_worksheet_cell_values',
            'sample_log_entry_worksheet_mandatory_data',
            'sample_log_entry_worksheet_rows',
            'sample_log_entry_worksheet_instances',
            'captured_procedure_config_values',
            'captured_procedure_values',
            'procedure_test_kit_values',
            'procedure_test_kit_rows',
            'procedure_section_input_values',
            'procedure_worksheet_step_analysts',
            'track_sample_results',
            'sample_captured_test_stages_track',
            'sample_captured_worksheet_formulas',
            'ser_step_worksheet_sample_relations',
            'ser_testkit_worksheet_sample_relations',
            'ser_header_worksheet_sample_relations',
            'grouped_worksheet_results_capture_sample_drafts',
            'grouped_worksheet_results_capture_drafts',
            'grouped_worksheet_results_capture_posts',
            'grouped_worksheet_run_items',
            'grouped_worksheet_runs',
            'qc_results',
            'results',
            'captured_results',
            'captured_view',
            'tat_captured',
            'sample_progress',
            'sample_analysis_dates',
            'sample_analysis_type_relation',
            'analysis_acceptance_form_lines',
            'analysis_acceptance_forms',
            'interzone_transfer_samples',
            'interzone_transfers',
            'chain_of_custodies',
            'batch_ammendments',
            'batch_attachments',
            'batch_labsection_approval',
            'batch_notifications',
            'batch_comments',
            'sample_header_user_assignments',
            'sample_dates',
            'sample_detail_staging',
            'sample_header_staging',
            'sample_imports',
            'import_lab_results',
            'case_file_review_forms',
            'workflow_approval_logs',
            'workflow_checklist_responses',
            'equipment_usage_request_samples',
            'sample_interlab_log',
            'sample_attachment_relations',
            'test_request_report_language_files',
            'test_request_report_deliveries',
            'test_request_report_revisions',
            'report_header_details',
            'invoice_details',
            'supporting_document_instance_values',
            'supporting_document_instances',
            'shelf_life_pull_points',
            'shelf_life_study_parameter_specs',
            'shelf_life_studies',
            'subcontracting_dispatch_assignments',
            'request_workflow_forms',
            'sample_details',
            'sample_headers',
            'sample_sequences',
        ];
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function purgeRequests(array &$counts): void
    {
        $counts['sample_headers.sample_submission_request_id'] = $this->nullColumn(
            'sample_headers',
            'sample_submission_request_id'
        );
        $counts['quotation_headers.sample_submission_request_id'] = $this->nullColumn(
            'quotation_headers',
            'sample_submission_request_id'
        );
        $counts['quotation_approval_logs.sample_submission_request_id'] = $this->nullColumn(
            'quotation_approval_logs',
            'sample_submission_request_id'
        );

        $requestIds = Schema::hasTable('sample_submission_requests')
            ? DB::table('sample_submission_requests')->pluck('id')->all()
            : [];

        $instanceIds = $this->resolveRequestLinkedInstanceIds($requestIds);
        $this->deleteSubmissionFormInstances($instanceIds, $counts);

        foreach ([
            'sample_submission_request_exhibits',
            'sample_submission_request_suspects',
            'sample_submission_request_requested_analyses',
            'sample_submission_request_supporting_document_templates',
            'enquiry_quotations',
            'sample_rejection_logs',
        ] as $table) {
            $counts[$table] = $this->deleteAllRows($table);
        }

        $counts['sample_submission_requests'] = $this->deleteAllRows('sample_submission_requests');
    }

    /**
     * @param  list<string>  $requestIds
     * @return list<string>
     */
    private function resolveRequestLinkedInstanceIds(array $requestIds): array
    {
        if ($requestIds === [] || ! Schema::hasTable('sample_submission_requests')) {
            return [];
        }

        $instanceIds = DB::table('sample_submission_requests')
            ->whereNotNull('submission_form_instance_id')
            ->pluck('submission_form_instance_id')
            ->map(fn ($id): string => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (Schema::hasTable('submission_form_instances')
            && Schema::hasColumn('submission_form_instances', 'portal_request_id')) {
            $portalLinked = DB::table('submission_form_instances')
                ->whereIn('portal_request_id', $requestIds)
                ->pluck('id')
                ->map(fn ($id): string => (string) $id)
                ->all();
            $instanceIds = array_values(array_unique(array_merge($instanceIds, $portalLinked)));
        }

        return $instanceIds;
    }

    /**
     * @param  list<string>  $instanceIds
     * @param  array<string, int>  $counts
     */
    private function deleteSubmissionFormInstances(array $instanceIds, array &$counts): void
    {
        if ($instanceIds === []) {
            return;
        }

        foreach ([
            'submission_form_instance_values',
            'submission_form_instance_attachments',
            'submission_form_instance_notes',
            'submission_form_instance_intrays',
            'submission_form_audit_log',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $column = Schema::hasColumn($table, 'submission_form_instance_id')
                ? 'submission_form_instance_id'
                : (Schema::hasColumn($table, 'instance_id') ? 'instance_id' : null);

            if ($column === null) {
                continue;
            }

            $counts[$table] = ($counts[$table] ?? 0)
                + (int) DB::table($table)->whereIn($column, $instanceIds)->delete();
        }

        if (Schema::hasTable('submission_form_instances')) {
            $counts['submission_form_instances'] = ($counts['submission_form_instances'] ?? 0)
                + (int) DB::table('submission_form_instances')->whereIn('id', $instanceIds)->delete();
        }
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function purgeSamplingSchedules(array &$counts): void
    {
        $counts['submission_form_instances.sampling_schedule_id'] = $this->nullColumn(
            'submission_form_instances',
            'sampling_schedule_id'
        );

        $counts['sampling_schedule_sample_plan_histories'] = $this->deleteAllRows(
            'sampling_schedule_sample_plan_histories'
        );
        $counts['sampling_schedules'] = $this->deleteAllRows('sampling_schedules');
    }
}
