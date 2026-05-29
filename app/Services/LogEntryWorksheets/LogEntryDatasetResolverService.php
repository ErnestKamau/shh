<?php

namespace App\Services\LogEntryWorksheets;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;
use App\SampleDetails;
use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogEntryDatasetResolverService
{
    /**
     * @var array<string, class-string<Model>>
     */
    protected array $tableModelMap = [
        'sample_details' => SampleDetails::class,
        'sample_headers' => SampleHeader::class,
        'captured_results' => CapturedResult::class,
        'analysis_methods' => AnalysisMethod::class,
        'users' => User::class,
    ];

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

        $sourceRow = $this->fetchRow($sourceTable, $sourcePk);
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

            $fkValue = $this->readColumnValue($sourceRow, $fkColumn);
            if ($fkValue === null || $fkValue === '') {
                return null;
            }

            $referencedRow = $this->fetchRowByColumn($referencedTable, $referencedKeyColumn, (string) $fkValue);

            return $referencedRow
                ? $this->readColumnValueAsString($referencedRow, $displayColumn)
                : null;
        }

        $sourceColumn = $config['source_display_column'] ?? null;
        if (! $sourceColumn) {
            return null;
        }

        return $this->readColumnValueAsString($sourceRow, $sourceColumn);
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

        $modelClass = $this->modelClassForTable($table);

        if ($modelClass) {
            return $modelClass::query()
                ->orderBy($labelColumn)
                ->limit(500)
                ->get()
                ->map(fn (Model $row) => (object) [
                    'id' => (string) $row->getAttribute($idColumn),
                    'label' => $this->readColumnValueAsString($row, $labelColumn) ?? '',
                ]);
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
            SampleHeader::class => 'sample_headers',
            SampleDetails::class => 'sample_details',
            CapturedResult::class => 'captured_results',
            AnalysisMethod::class => 'analysis_methods',
        ];

        return ($map[$row->driver_type] ?? '') === $sourceTable;
    }

    /**
     * @return class-string<Model>|null
     */
    protected function modelClassForTable(string $table): ?string
    {
        return $this->tableModelMap[$table] ?? null;
    }

    protected function fetchRow(string $table, string $id): ?object
    {
        $modelClass = $this->modelClassForTable($table);
        if ($modelClass) {
            return $modelClass::find($id);
        }

        return DB::table($table)->where('id', $id)->first();
    }

    protected function fetchRowByColumn(string $table, string $keyColumn, string $keyValue): ?object
    {
        $modelClass = $this->modelClassForTable($table);
        if ($modelClass) {
            return $modelClass::query()->where($keyColumn, $keyValue)->first();
        }

        return DB::table($table)->where($keyColumn, $keyValue)->first();
    }

    protected function readColumnValue(object $row, string $column): mixed
    {
        if ($row instanceof Model) {
            return $row->getAttribute($column);
        }

        return $row->{$column} ?? null;
    }

    protected function readColumnValueAsString(object $row, string $column): ?string
    {
        $value = $this->readColumnValue($row, $column);

        return $value !== null && $value !== '' ? (string) $value : null;
    }
}
