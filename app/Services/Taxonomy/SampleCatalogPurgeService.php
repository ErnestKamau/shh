<?php

namespace App\Services\Taxonomy;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * Deletes sample taxonomy catalog rows only (no CASCADE into unrelated domains).
 */
final class SampleCatalogPurgeService
{
    /**
     * Pivot / owned rows under the catalog, deepest first.
     *
     * @var list<string>
     */
    private const DELETE_TABLES = [
        'analysis_guides',
        'analysis_type_invoicable_item',
        'analysis_type_lab_relation',
        'customer_analysis_type_standards',
        'quotation_details_analysis_type',
        'pricelist_item_elements',
        'sample_conditions',
        'sample_to_sample_analysis_stages',
        'sampletype_area_relation',
        'sampletype_sample_point_relation',
        'submission_form_sample_types',
        'audit_checklist_sample_type',
        'submission_form_sample_type_categories',
        'analysis_elements',
        'analysis_types',
        'sample_types',
        'sample_type_categories',
    ];

    /**
     * Outside tables/columns to NULL before catalog deletes.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const NULL_COLUMNS = [
        ['sample_headers', 'sample_type_id'],
        ['sample_header_staging', 'sample_type_id'],
        ['sample_details', 'analysis_type_id'],
        ['sample_details', 'sample_type_id'],
        ['sample_analysis_type_relation', 'analysis_type_id'],
        ['stage_headers', 'sample_type_id'],
        ['captured_results', 'analysis_type_id'],
        ['captured_results', 'analysis_element_id'],
        ['captured_view', 'analysis_type_id'],
        ['results', 'analysis_type_id'],
        ['qc_results', 'analysis_type_id'],
        ['qc_processed_result', 'sample_type_id'],
        ['qc_processed_result', 'analysis_type_id'],
        ['tat_captured', 'sample_type_id'],
        ['tat_captured', 'analysis_type_id'],
        ['ser_header_worksheet_sample_relations', 'analysis_type_id'],
        ['pricelist_items', 'sample_type_id'],
        ['pricelist_items', 'analysis_element_id'],
        ['sample_submission_requests', 'sample_type_id'],
        ['sample_submission_requests', 'batch_sample_type_id'],
        ['sample_submission_request_requested_analyses', 'sample_type_id'],
        ['sample_submission_request_requested_analyses', 'analysis_type_id'],
        ['sample_submission_request_requested_analyses', 'analysis_element_id'],
        ['subcontracting_dispatch_assignments', 'analysis_element_id'],
        ['analysis_acceptance_form_lines', 'sample_type_id'],
        ['analysis_acceptance_form_lines', 'analysis_type_id'],
        ['analysis_acceptance_form_lines', 'analysis_element_id'],
        ['submission_form_instances', 'selected_sample_type_id'],
        ['sampling_schedules', 'sample_type_id'],
        ['sampling_schedules', 'analysis_type_id'],
        ['shelf_life_studies', 'sample_type_id'],
        ['preparation_steps', 'sample_type_id'],
        ['preparation_steps', 'analysis_type_id'],
        ['solution_preparation_step_templates', 'sample_type_id'],
        ['solution_preparation_step_templates', 'analysis_type_id'],
    ];

    /**
     * @return array{deleted: array<string, int>, nulled: array<string, int>}
     */
    public function purge(): array
    {
        $deleted = [];
        $nulled = [];

        DB::transaction(function () use (&$deleted, &$nulled): void {
            foreach (self::NULL_COLUMNS as [$table, $column]) {
                $nulled["{$table}.{$column}"] = $this->nullColumn($table, $column);
            }

            foreach (self::DELETE_TABLES as $table) {
                $deleted[$table] = $this->deleteAll($table);
            }
        });

        return compact('deleted', 'nulled');
    }

    /**
     * @return list<string>
     */
    public function deleteAllowlist(): array
    {
        return self::DELETE_TABLES;
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
                "Failed to NULL a foreign key during taxonomy purge: {$e->getMessage()}",
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
