<?php

namespace App\Services\System;

use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseExportService
{
    /**
     * @return array{path: string, filename: string, mime: string}
     */
    public function createExport(string $format, ?string $connectionName = null): array
    {
        if (!in_array($format, ['sql', 'dump'], true)) {
            throw new RuntimeException('Unsupported export format.');
        }

        $connection = $connectionName !== null && $connectionName !== ''
            ? $connectionName
            : (string) config('database.default');
        $config = config("database.connections.{$connection}", []);

        if ($config === []) {
            throw new RuntimeException('Unknown database connection selected for export.');
        }

        $driver = (string) ($config['driver'] ?? '');

        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '5432');

        if ($database === '' || $username === '') {
            throw new RuntimeException('Missing PostgreSQL credentials for export.');
        }

        $timestamp = now()->format('Ymd_His');
        $directory = storage_path('app/system/database-exports');

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create export directory.');
        }

        $extension = $format === 'sql' ? 'sql' : 'dump';
    $safeConnection = preg_replace('/[^A-Za-z0-9_\-]/', '_', $connection) ?: 'database';
    $outputFile = $directory . DIRECTORY_SEPARATOR . "database_export_{$safeConnection}_{$timestamp}.{$extension}";

        if ($driver !== 'pgsql') {
            throw new RuntimeException('Database export currently supports PostgreSQL connections only.');
        }

        $this->exportPostgres($config, $database, $username, $host, $port, $format, $outputFile);

        if (!is_file($outputFile)) {
            throw new RuntimeException('Database export file was not created.');
        }

        return [
            'path' => $outputFile,
            'filename' => basename($outputFile),
            'mime' => $format === 'sql' ? 'application/sql' : 'application/octet-stream',
        ];
    }

    private function exportPostgres(array $config, string $database, string $username, string $host, string $port, string $format, string $outputFile): void
    {
        $command = [
            'pg_dump',
            '--host=' . $host,
            '--port=' . $port,
            '--username=' . $username,
            '--no-owner',
            '--no-privileges',
            '--file=' . $outputFile,
            '--format=' . ($format === 'sql' ? 'plain' : 'custom'),
            $database,
        ];

        $environment = [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ];

        $this->runProcess($command, $environment, 'Database export failed');
    }

    private function runProcess(array $command, array $environment, string $failureMessage): void
    {
        $process = new Process($command, base_path(), $environment, null, 300);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $output = trim($process->getOutput());

            throw new RuntimeException($failureMessage . ': ' . ($errorOutput !== '' ? $errorOutput : $output));
        }
    }
}
