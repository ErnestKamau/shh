<?php

namespace App\Services\AI\Repository;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class AiRepositorySetupService
{
    public function setup(bool $withPgvector = true): array
    {
        $connection = $this->repositoryConnection();
        $this->assertPgsqlConnection($connection);

        $schemas = config('imara_ai.schemas', []);
        foreach ($schemas as $schema) {
            $this->createSchema($connection, $schema);
        }

        if ($withPgvector && config('imara_ai.pgvector.enabled', true)) {
            $extension = $this->sanitizeIdentifier(config('imara_ai.pgvector.extension', 'vector'));
            DB::connection($connection)->statement("CREATE EXTENSION IF NOT EXISTS \"{$extension}\"");
        }

        return [
            'connection' => $connection,
            'schemas' => array_values($schemas),
            'pgvector_enabled' => $withPgvector && config('imara_ai.pgvector.enabled', true),
        ];
    }

    protected function assertPgsqlConnection(string $connection): void
    {
        $driver = config("database.connections.{$connection}.driver");
        if ($driver !== 'pgsql') {
            throw new RuntimeException("The [{$connection}] connection must use the pgsql driver.");
        }
    }

    protected function createSchema(string $connection, string $schema): void
    {
        $schema = $this->sanitizeIdentifier($schema);
        DB::connection($connection)->statement("CREATE SCHEMA IF NOT EXISTS \"{$schema}\"");
    }

    protected function sanitizeIdentifier(string $identifier): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', $identifier);
    }

    protected function repositoryConnection(): string
    {
        return config('imara_ai.repository_connection', 'pgsql_ai');
    }
}
