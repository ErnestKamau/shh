<?php

namespace App\Services\Ops;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * Deletes pricelists, quotations, sample batches, and sample submission requests only.
 * Explicit allowlists; never CASCADE into unrelated domains.
 */
final class OperationalDataPurgeService
{
    /**
     * @return array{deleted: array<string, int>, nulled: array<string, int>}
     */
    public function purge(): array
    {
        $deleted = [];
        $nulled = [];

        DB::transaction(function () use (&$deleted, &$nulled): void {
            $this->purgeSamples($deleted, $nulled);
            $this->purgeRequests($deleted, $nulled);
            $this->purgeQuotations($deleted, $nulled);
            $this->purgePricelists($deleted, $nulled);
        });

        return compact('deleted', 'nulled');
    }

    /**
     * @param  array<string, int>  $deleted
     * @param  array<string, int>  $nulled
     */
    private function purgeSamples(array &$deleted, array &$nulled): void
    {
        $nulled['sample_submission_requests.sample_header_id'] = $this->nullColumn('sample_submission_requests', 'sample_header_id');
        $nulled['analysis_methods.sample_header_id'] = $this->nullColumn('analysis_methods', 'sample_header_id');
        $nulled['lab_results_excel.sample_header_id'] = $this->nullColumn('lab_results_excel', 'sample_header_id');
        $nulled['worksheet_executions.batch_id'] = $this->nullColumn('worksheet_executions', 'batch_id');
        $nulled['worksheet_executions.sample_id'] = $this->nullColumn('worksheet_executions', 'sample_id');
        $nulled['invoice_payment_details.batch_id'] = $this->nullColumn('invoice_payment_details', 'batch_id');
        $nulled['risks.sample_id'] = $this->nullColumn('risks', 'sample_id');
        $nulled['non_conformances.sample_id'] = $this->nullColumn('non_conformances', 'sample_id');

        $tables = [
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

        foreach ($tables as $table) {
            $deleted[$table] = ($deleted[$table] ?? 0) + $this->deleteAll($table);
        }
    }

    /**
     * @param  array<string, int>  $deleted
     * @param  array<string, int>  $nulled
     */
    private function purgeRequests(array &$deleted, array &$nulled): void
    {
        $nulled['sample_headers.sample_submission_request_id'] = $this->nullColumn('sample_headers', 'sample_submission_request_id');
        $nulled['quotation_headers.sample_submission_request_id'] = $this->nullColumn('quotation_headers', 'sample_submission_request_id');
        $nulled['quotation_approval_logs.sample_submission_request_id'] = $this->nullColumn('quotation_approval_logs', 'sample_submission_request_id');

        $requestIds = Schema::hasTable('sample_submission_requests')
            ? DB::table('sample_submission_requests')->pluck('id')->all()
            : [];

        $instanceIds = [];
        if ($requestIds !== [] && Schema::hasTable('sample_submission_requests')) {
            $instanceIds = DB::table('sample_submission_requests')
                ->whereNotNull('submission_form_instance_id')
                ->pluck('submission_form_instance_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (Schema::hasTable('submission_form_instances') && Schema::hasColumn('submission_form_instances', 'portal_request_id')) {
                $portalLinked = DB::table('submission_form_instances')
                    ->whereIn('portal_request_id', $requestIds)
                    ->pluck('id')
                    ->all();
                $instanceIds = array_values(array_unique(array_merge($instanceIds, $portalLinked)));
            }
        }

        $tables = [
            'sample_submission_request_exhibits',
            'sample_submission_request_suspects',
            'sample_submission_request_requested_analyses',
            'sample_submission_request_supporting_document_templates',
            'enquiry_quotations',
            'sample_rejection_logs',
        ];

        foreach ($tables as $table) {
            $deleted[$table] = ($deleted[$table] ?? 0) + $this->deleteAll($table);
        }

        if ($instanceIds !== []) {
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
                $deleted[$table] = ($deleted[$table] ?? 0) + (int) DB::table($table)->whereIn($column, $instanceIds)->delete();
            }

            if (Schema::hasTable('submission_form_instances')) {
                $deleted['submission_form_instances'] = ($deleted['submission_form_instances'] ?? 0)
                    + (int) DB::table('submission_form_instances')->whereIn('id', $instanceIds)->delete();
            }
        }

        $deleted['sample_submission_requests'] = ($deleted['sample_submission_requests'] ?? 0)
            + $this->deleteAll('sample_submission_requests');
    }

    /**
     * @param  array<string, int>  $deleted
     * @param  array<string, int>  $nulled
     */
    private function purgeQuotations(array &$deleted, array &$nulled): void
    {
        $nulled['sample_headers.quote_id'] = $this->nullColumn('sample_headers', 'quote_id');
        $nulled['sample_submission_requests.current_quotation_header_id'] = $this->nullColumn('sample_submission_requests', 'current_quotation_header_id');
        $nulled['sample_submission_requests.accepted_quotation_header_id'] = $this->nullColumn('sample_submission_requests', 'accepted_quotation_header_id');
        $nulled['sample_submission_requests.created_from_quotation_header_id'] = $this->nullColumn('sample_submission_requests', 'created_from_quotation_header_id');
        $nulled['customer_invoice.quotation_header_id'] = $this->nullColumn('customer_invoice', 'quotation_header_id');

        foreach ([
            'quotation_details_analysis_type',
            'quotation_detail_analysis_splits',
            'quotation_attachments',
            'quotation_notes',
            'quotation_details',
            'quotation_approval_logs',
            'enquiry_quotations',
            'quotation_headers',
        ] as $table) {
            $deleted[$table] = ($deleted[$table] ?? 0) + $this->deleteAll($table);
        }
    }

    /**
     * @param  array<string, int>  $deleted
     * @param  array<string, int>  $nulled
     */
    private function purgePricelists(array &$deleted, array &$nulled): void
    {
        $nulled['quotation_headers.pricelist_id'] = $this->nullColumn('quotation_headers', 'pricelist_id');
        // customer_invoice.pricelist_id is NOT NULL and FK is ON DELETE CASCADE — must detach
        // before deleting pricelists or invoice headers would be wiped.
        $nulled['customer_invoice.pricelist_id'] = $this->nullColumn('customer_invoice', 'pricelist_id');
        $nulled['analysis_acceptance_forms.pricelist_id'] = $this->nullColumn('analysis_acceptance_forms', 'pricelist_id');

        foreach ([
            'pricelist_item_elements',
            'pricelist_items',
            'pricelist_customers',
            'pricelist_email_logs',
            'pricelists',
        ] as $table) {
            $deleted[$table] = ($deleted[$table] ?? 0) + $this->deleteAll($table);
        }
    }

    private function nullColumn(string $table, string $column): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return $this->withSavepoint(function () use ($table, $column): int {
            if (! $this->columnIsNullable($table, $column)) {
                $this->dropNotNull($table, $column);
            }

            return (int) DB::table($table)->whereNotNull($column)->update([$column => null]);
        });
    }

    private function deleteAll(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)->delete();
    }

    /**
     * @param  callable(): int  $callback
     */
    private function withSavepoint(callable $callback): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            return $callback();
        }

        $savepoint = 'sp_'.bin2hex(random_bytes(8));

        DB::statement("SAVEPOINT {$savepoint}");

        try {
            $result = $callback();
            DB::statement("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $e) {
            DB::statement("ROLLBACK TO SAVEPOINT {$savepoint}");
            DB::statement("RELEASE SAVEPOINT {$savepoint}");

            throw new RuntimeException(
                "Failed to NULL a foreign key during operational purge: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT is_nullable
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            [$table, $column]
        );

        return strtoupper((string) ($row->is_nullable ?? 'NO')) === 'YES';
    }

    private function dropNotNull(string $table, string $column): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException(
                "Column {$table}.{$column} is NOT NULL; cannot detach safely on this driver."
            );
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ALTER COLUMN %s DROP NOT NULL',
            $this->quoteIdent($table),
            $this->quoteIdent($column)
        ));
    }

    private function quoteIdent(string $ident): string
    {
        return '"'.str_replace('"', '""', $ident).'"';
    }
}
