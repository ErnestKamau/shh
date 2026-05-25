<?php

namespace App\Services\LogEntryWorksheets;

use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogEntryDatasetResolverService
{
    /**
     * Resolve display value for a dataset column from row context.
     *
     * @param  array<string, mixed>|null  $config
     */
    public function resolveDisplayValue(?array $config, SampleLogEntryWorksheetRow $row): ?string
    {
        if (! $config || empty($config['source_table'])) {
            return null;
        }

        $sourceTable = $config['source_table'];
        if (! Schema::hasTable($sourceTable)) {
            return null;
        }

        $sourcePk = $this->resolveSourcePrimaryKey($config, $row);
        if ($sourcePk === null) {
            return null;
        }

        $sourceRow = DB::table($sourceTable)->where('id', $sourcePk)->first();
        if (! $sourceRow) {
            return null;
        }

        $mode = $config['display_mode'] ?? 'direct';

        if ($mode === 'foreign_key') {
            $fkColumn = $config['foreign_key_column'] ?? null;
            $referencedTable = $config['referenced_table'] ?? null;
            $displayColumn = $config['referenced_display_column'] ?? null;
            $referencedKeyColumn = $config['referenced_key_column'] ?? 'id';

            if (! $fkColumn || ! $referencedTable || ! $displayColumn || ! Schema::hasTable($referencedTable)) {
                return null;
            }

            $fkValue = $sourceRow->{$fkColumn} ?? null;
            if ($fkValue === null || $fkValue === '') {
                return null;
            }

            return DB::table($referencedTable)
                ->where($referencedKeyColumn, $fkValue)
                ->value($displayColumn);
        }

        $sourceColumn = $config['source_display_column'] ?? null;
        if (! $sourceColumn) {
            return null;
        }

        $value = $sourceRow->{$sourceColumn} ?? null;

        return $value !== null ? (string) $value : null;
    }

    /**
     * Dropdown options for editable dataset cells.
     *
     * @param  array<string, mixed>|null  $config
     * @return Collection<int, object{id: string, label: string}>
     */
    public function dropdownOptions(?array $config): Collection
    {
        if (! $config) {
            return collect();
        }

        $mode = $config['display_mode'] ?? 'direct';
        $table = $mode === 'foreign_key'
            ? ($config['referenced_table'] ?? null)
            : ($config['source_table'] ?? null);

        $idColumn = $mode === 'foreign_key'
            ? ($config['referenced_key_column'] ?? 'id')
            : 'id';

        $labelColumn = $mode === 'foreign_key'
            ? ($config['referenced_display_column'] ?? null)
            : ($config['source_display_column'] ?? null);

        if (! $table || ! $labelColumn || ! Schema::hasTable($table)) {
            return collect();
        }

        return DB::table($table)
            ->select([$idColumn.' as id', $labelColumn.' as label'])
            ->orderBy($labelColumn)
            ->limit(500)
            ->get()
            ->map(fn ($row) => (object) [
                'id' => (string) $row->id,
                'label' => (string) $row->label,
            ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function resolveSourcePrimaryKey(array $config, SampleLogEntryWorksheetRow $row): ?string
    {
        if (! empty($config['source_row_id']) && $row->row_source === 'manual') {
            return (string) $config['source_row_id'];
        }

        if ($row->driver_id && $this->driverMatchesSourceTable($row, $config['source_table'])) {
            return (string) $row->driver_id;
        }

        return null;
    }

    protected function driverMatchesSourceTable(SampleLogEntryWorksheetRow $row, string $sourceTable): bool
    {
        $map = [
            \App\SampleHeader::class => 'sample_headers',
            \App\SampleDetails::class => 'sample_details',
            \App\CapturedResult::class => 'captured_results',
            \App\AnalysisMethod::class => 'analysis_methods',
        ];

        return ($map[$row->driver_type] ?? '') === $sourceTable;
    }
}
