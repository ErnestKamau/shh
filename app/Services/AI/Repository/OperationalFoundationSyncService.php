<?php

namespace App\Services\AI\Repository;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class OperationalFoundationSyncService
{
    public function sync(array $tables = [], ?int $chunkSize = null): array
    {
        $configuredTables = config('imara_ai.etl.tables', []);
        $selectedTables = $tables ?: array_keys($configuredTables);
        $chunkSize = $chunkSize ?: (int) config('imara_ai.etl.chunk_size', 500);
        $results = [];

        foreach ($selectedTables as $table) {
            if (!isset($configuredTables[$table])) {
                throw new RuntimeException("Unsupported ETL table [{$table}].");
            }

            $mapping = $configuredTables[$table];
            $results[$table] = $this->syncConfiguredTable($table, $mapping, $chunkSize);
        }

        return $results;
    }

    protected function syncConfiguredTable(string $tableKey, array $mapping, int $chunkSize): array
    {
        $runId = $this->startRun($tableKey, $mapping, $chunkSize);
        $rowsSynced = 0;

        try {
            switch ($tableKey) {
                case 'qc_results':
                    $rowsSynced = $this->syncAiQcResults($mapping, $chunkSize);
                    break;
                // Future AI-Related ETls would be added here
                default:
                    throw new RuntimeException("No AI-sync handler is defined for [{$tableKey}].");
            }

            $this->finishRun($runId, 'completed', $rowsSynced);

            return [
                'status' => 'completed',
                'rows_synced' => $rowsSynced,
                'target_table' => $this->targetTable($mapping),
            ];
        } catch (Throwable $exception) {
            $this->finishRun($runId, 'failed', $rowsSynced, $exception->getMessage());
            throw $exception;
        }
    }

    protected function syncAiQcResults(array $mapping, int $chunkSize): int
    {
        $rowsSynced = 0;
        $source = DB::connection($this->sourceConnection())
            ->table($mapping['source_table'])
            ->orderBy($mapping['primary_key']);

        $source->chunkById($chunkSize, function ($rows) use (&$rowsSynced, $mapping) {
            $payload = [];
            foreach ($rows as $row) {
                $payload[] = [
                    'source_id' => $row->id,
                    'analyte_code' => $row->analyte_code,
                    'result' => (float)$row->result,
                    'recorded_at' => $row->created_at,
                    'context_metadata' => json_encode([
                        'sample' => $row->sample_detail_code,
                        'analysis_type_id' => $row->analysis_type_id,
                    ]),
                    'synced_at' => now(),
                ];
            }

            $this->repositoryTable($mapping)->upsert(
                $payload,
                ['source_id'],
                ['analyte_code', 'result', 'recorded_at', 'context_metadata', 'synced_at']
            );

            $rowsSynced += count($payload);
        }, $mapping['primary_key']);

        return $rowsSynced;
    }

    protected function startRun(string $tableKey, array $mapping, int $chunkSize): int
    {
        return $this->repositoryTable([
            'schema' => 'ai',
            'target_table' => 'sync_runs',
        ])->insertGetId([
            'sync_scope' => $tableKey,
            'source_table' => $mapping['source_table'] ?? null,
            'target_table' => $this->targetTable($mapping),
            'status' => 'running',
            'rows_synced' => 0,
            'started_at' => now()->toDateTimeString(),
            'metadata' => json_encode([
                'chunk_size' => $chunkSize,
            ]),
        ]);
    }

    protected function finishRun(int $runId, string $status, int $rowsSynced, ?string $errorMessage = null): void
    {
        $this->repositoryTable([
            'schema' => 'ai',
            'target_table' => 'sync_runs',
        ])->where('id', $runId)->update([
            'status' => $status,
            'rows_synced' => $rowsSynced,
            'finished_at' => now()->toDateTimeString(),
            'error_message' => $errorMessage,
        ]);
    }

    protected function repositoryTable(array $mapping)
    {
        return DB::connection($this->repositoryConnection())->table($this->targetTable($mapping));
    }

    protected function targetTable(array $mapping): string
    {
        $schema = $mapping['schema'] ?? 'ai';
        $schemaName = config("imara_ai.schemas.{$schema}", $schema);

        return "{$schemaName}.{$mapping['target_table']}";
    }

    protected function sourceConnection(): string
    {
        return config('imara_ai.source_connection', 'mysql');
    }

    public function repositoryConnection(): string
    {
        return config('imara_ai.repository_connection', 'pgsql_ai');
    }

    public function aiSchema(): string
    {
        return config('imara_ai.schemas.ai', 'ai');
    }
}
