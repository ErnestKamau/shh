<?php

namespace App\Console\Commands;

use App\Jobs\SyncOperationalFoundationTable;
use App\Services\AI\Repository\OperationalFoundationSyncService;
use Illuminate\Console\Command;

class SyncImaraFoundation extends Command
{
    protected $signature = 'imara:sync-foundation
                            {tables?* : Optional configured source tables to sync}
                            {--chunk= : Override the default ETL chunk size}
                            {--queue : Dispatch each table sync as a queued job}';

    protected $description = 'Sync the Phase 1 operational foundation tables from MySQL into PostgreSQL';

    public function handle(OperationalFoundationSyncService $syncService): int
    {
        $configuredTables = array_keys(config('imara_ai.etl.tables', []));
        $tables = $this->argument('tables') ?: $configuredTables;
        $chunkSize = $this->option('chunk') ? (int) $this->option('chunk') : null;

        $invalidTables = array_values(array_diff($tables, $configuredTables));
        if (!empty($invalidTables)) {
            $this->error('Unsupported tables: ' . implode(', ', $invalidTables));
            $this->line('Configured tables: ' . implode(', ', $configuredTables));

            return self::FAILURE;
        }

        if ($this->option('queue')) {
            foreach ($tables as $table) {
                SyncOperationalFoundationTable::dispatch($table, $chunkSize);
                $this->info("Queued sync for [{$table}]");
            }

            return self::SUCCESS;
        }

        try {
            $results = $syncService->sync($tables, $chunkSize);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($results as $table => $result) {
            $this->info("[{$table}] {$result['status']} - {$result['rows_synced']} row(s) synced to {$result['target_table']}");
        }

        return self::SUCCESS;
    }
}
