<?php

namespace App\Services\System;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class SystemModuleBackupService
{
    public function __construct(private readonly SystemBackupCatalog $catalog)
    {
    }

    /**
     * @param array<int, string> $targetKeys
     * @return array{path: string, filename: string, mime: string}
     */
    public function createExport(array $targetKeys, string $format): array
    {
        $selectedTargets = $this->catalog->selected($targetKeys);

        if ($selectedTargets === []) {
            throw new RuntimeException('Please select at least one backup target.');
        }

        $format = strtolower(trim($format));
        if (!in_array($format, ['csv', 'sql', 'dump'], true)) {
            throw new RuntimeException('Unsupported backup format selected.');
        }

        $flattenedTables = $this->flattenTables($selectedTargets);
        $fullDatabaseSnapshot = in_array('*', $flattenedTables, true);

        if ($fullDatabaseSnapshot) {
            $flattenedTables = $this->allApplicationTables();
        }

        if ($flattenedTables === []) {
            throw new RuntimeException('No tables were resolved for the selected backup targets.');
        }

        $timestamp = now()->format('Ymd_His');
        $directory = storage_path('app/system/module-backups');

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the backup directory.');
        }

        return match ($format) {
            'csv' => $this->createCsvExport($selectedTargets, $flattenedTables, $directory, $timestamp),
            'sql', 'dump' => $this->createPgDumpExport($flattenedTables, $directory, $timestamp, $format, $fullDatabaseSnapshot),
        };
    }

    /**
     * @param array<int, array<string, mixed>> $selectedTargets
     * @return array<int, string>
     */
    private function flattenTables(array $selectedTargets): array
    {
        $tables = [];

        foreach ($selectedTargets as $target) {
            foreach (($target['tables'] ?? []) as $table) {
                $tableName = (string) $table;

                if ($tableName === '') {
                    continue;
                }

                $tables[$tableName] = $tableName;
            }
        }

        return array_values($tables);
    }

    /**
     * @param array<int, array<string, mixed>> $selectedTargets
     * @param array<int, string> $tables
     * @return array{path: string, filename: string, mime: string}
     */
    private function createCsvExport(array $selectedTargets, array $tables, string $directory, string $timestamp): array
    {
        $multipleOutputs = count($tables) > 1 || count($selectedTargets) > 1;

        if (!$multipleOutputs) {
            $table = $tables[0];
            $path = $directory . DIRECTORY_SEPARATOR . $this->buildFileName('backup_' . $table, $timestamp, 'csv');
            $this->writeTableCsv($table, $path);

            return [
                'path' => $path,
                'filename' => basename($path),
                'mime' => 'text/csv',
            ];
        }

        $zipPath = $directory . DIRECTORY_SEPARATOR . $this->buildFileName('backup_bundle', $timestamp, 'zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create the CSV archive.');
        }

        $temporaryFiles = [];
        try {
            foreach ($tables as $tableName) {
                $tempPath = $directory . DIRECTORY_SEPARATOR . $this->buildFileName('table_' . $tableName, $timestamp, 'csv');
                $this->writeTableCsv($tableName, $tempPath);
                $zip->addFile($tempPath, basename($tempPath));
                $temporaryFiles[] = $tempPath;
            }
        } finally {
            $zip->close();

            foreach ($temporaryFiles as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    @unlink($temporaryFile);
                }
            }
        }

        return [
            'path' => $zipPath,
            'filename' => basename($zipPath),
            'mime' => 'application/zip',
        ];
    }

    /**
     * @param array<int, string> $tables
     * @return array{path: string, filename: string, mime: string}
     */
    private function createPgDumpExport(array $tables, string $directory, string $timestamp, string $format, bool $fullDatabaseSnapshot = false): array
    {
        $connectionName = (string) config('database.default');
        $config = config("database.connections.{$connectionName}", []);

        if ($config === []) {
            throw new RuntimeException('Unknown database connection selected for backup.');
        }

        if ((string) ($config['driver'] ?? '') !== 'pgsql') {
            throw new RuntimeException('Selected backup targets can only be exported from PostgreSQL connections.');
        }

        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '5432');

        if ($database === '' || $username === '') {
            throw new RuntimeException('Missing PostgreSQL credentials for backup export.');
        }

        $safeConnection = preg_replace('/[^A-Za-z0-9_\-]/', '_', $connectionName) ?: 'database';
        $outputFile = $directory . DIRECTORY_SEPARATOR . $this->buildFileName('module_backup_' . $safeConnection, $timestamp, $format);

        $command = [
            'pg_dump',
            '--host=' . $host,
            '--port=' . $port,
            '--username=' . $username,
            '--no-owner',
            '--no-privileges',
            '--file=' . $outputFile,
            '--format=' . ($format === 'sql' ? 'plain' : 'custom'),
        ];

        if (!$fullDatabaseSnapshot) {
            foreach ($tables as $table) {
                $command[] = '--table=' . $table;
            }
        }

        $command[] = $database;

        $process = new Process($command, base_path(), [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ], null, 300);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $output = trim($process->getOutput());

            throw new RuntimeException('Backup export failed: ' . ($errorOutput !== '' ? $errorOutput : $output));
        }

        if (!is_file($outputFile)) {
            throw new RuntimeException('Backup export file was not created.');
        }

        return [
            'path' => $outputFile,
            'filename' => basename($outputFile),
            'mime' => $format === 'sql' ? 'application/sql' : 'application/octet-stream',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allApplicationTables(): array
    {
        $driver = (string) config('database.connections.' . config('database.default') . '.driver', '');

        if ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' ORDER BY tablename");

            return array_values(array_map(static fn ($row): string => (string) $row->tablename, $rows));
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $rows = DB::select('SHOW TABLES');
            $tables = [];
            foreach ($rows as $row) {
                $tables[] = (string) array_values((array) $row)[0];
            }

            sort($tables);

            return $tables;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

            return array_values(array_map(static fn ($row): string => (string) $row->name, $rows));
        }

        $tables = [];
        foreach (Schema::getAllTables() as $row) {
            foreach ((array) $row as $value) {
                if (is_string($value) && $value !== '') {
                    $tables[] = $value;
                    break;
                }
            }
        }

        return array_values(array_unique($tables));
    }

    private function writeTableCsv(string $table, string $path): void
    {
        if (!Schema::hasTable($table)) {
            throw new RuntimeException('The table ' . $table . ' does not exist.');
        }

        $columns = Schema::getColumnListing($table);
        if ($columns === []) {
            throw new RuntimeException('The table ' . $table . ' has no exportable columns.');
        }

        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Unable to create CSV file for ' . $table . '.');
        }

        try {
            fputcsv($handle, $columns);

            $query = DB::table($table);
            if (in_array('id', $columns, true)) {
                $query->orderBy('id');
            }

            foreach ($query->cursor() as $record) {
                $row = [];
                foreach ($columns as $column) {
                    $row[] = $this->normalizeCsvValue($record->{$column} ?? null);
                }

                fputcsv($handle, $row);
            }
        } finally {
            fclose($handle);
        }
    }

    private function normalizeCsvValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return (string) $value;
    }

    private function buildFileName(string $prefix, string $timestamp, string $extension): string
    {
        $safePrefix = preg_replace('/[^A-Za-z0-9_\-]/', '_', $prefix) ?: 'backup';

        return $safePrefix . '_' . $timestamp . '.' . $extension;
    }
}