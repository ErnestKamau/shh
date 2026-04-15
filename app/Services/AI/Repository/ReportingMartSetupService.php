<?php

namespace App\Services\AI\Repository;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ReportingMartSetupService
{
    public function setup(): array
    {
        $connection = $this->repositoryConnection();
        $this->assertPgsqlConnection($connection);

        $schema = $this->aiSchema();

        $this->createAiSchemaTables($connection, $schema);

        return [
            'connection' => $connection,
            'schema' => $schema,
        ];
    }

    protected function createAiSchemaTables(string $connection, string $schema): void
    {
        // Setup for AI Vector storage and analysis logs
        $analysisLogsTable = "{$schema}.analysis_logs";
        if (!Schema::connection($connection)->hasTable($analysisLogsTable)) {
            Schema::connection($connection)->create($analysisLogsTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('model')->nullable();
                $table->text('prompt')->nullable();
                $table->text('response')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestampTz('created_at')->useCurrent();
            });
        }

        $vectorStoreTable = "{$schema}.vector_store";
        if (!Schema::connection($connection)->hasTable($vectorStoreTable)) {
            Schema::connection($connection)->create($vectorStoreTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('entity_type')->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('content_chunk')->nullable();
                
                // Add Vector column if extension enabled
                if (config('imara_ai.pgvector.enabled', false)) {
                     // Note: We use a raw statement for the vector type usually, 
                     // but for the setup service we'll leave a placeholder or handle via DDL
                }

                $table->jsonb('metadata')->nullable();
                $table->timestampsTz();

                $table->index(['entity_type', 'entity_id']);
            });
        }
        
        // Add specific table for AI-targeted QC data
        $qcResultsAiTable = "{$schema}.qc_results_ai";
        if (!Schema::connection($connection)->hasTable($qcResultsAiTable)) {
            Schema::connection($connection)->create($qcResultsAiTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('analyte_code')->nullable();
                $table->double('result')->nullable();
                $table->timestampTz('recorded_at')->nullable();
                $table->jsonb('context_metadata')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
            });
        }

        $syncRunsTable = "{$schema}.sync_runs";
        if (!Schema::connection($connection)->hasTable($syncRunsTable)) {
            Schema::connection($connection)->create($syncRunsTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('sync_scope')->nullable();
                $table->string('source_table')->nullable();
                $table->string('target_table')->nullable();
                $table->string('status')->nullable();
                $table->string('stage')->nullable();
                $table->integer('rows_synced')->default(0);
                $table->integer('rows_failed')->default(0);
                $table->integer('rows_quarantined')->default(0);
                $table->string('lock_owner')->nullable();
                $table->text('error_message')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestampTz('started_at')->nullable();
                $table->timestampTz('finished_at')->nullable();
                $table->timestampsTz();
            });
        }

        $martRunsTable = "{$schema}.mart_runs";
        if (!Schema::connection($connection)->hasTable($martRunsTable)) {
            Schema::connection($connection)->create($martRunsTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('mart_name')->nullable();
                $table->string('status')->nullable();
                $table->integer('rows_materialized')->default(0);
                $table->text('error_message')->nullable();
                $table->timestampTz('started_at')->nullable();
                $table->timestampTz('finished_at')->nullable();
                $table->timestampsTz();
            });
        }

        // New Inventory Tables for Parity
        $invOrdersTable = "reporting.inventory_orders";
        if (!Schema::connection($connection)->hasTable($invOrdersTable)) {
            Schema::connection($connection)->create($invOrdersTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('order_number')->nullable();
                $table->string('supplier_id')->nullable();
                $table->string('status')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
                $table->jsonb('payload')->nullable();
            });
        }

        $suppliersTable = "reporting.suppliers";
        if (!Schema::connection($connection)->hasTable($suppliersTable)) {
            Schema::connection($connection)->create($suppliersTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->smallint('active')->default(1);
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
                $table->jsonb('payload')->nullable();
            });
        }

        $invCatsTable = "reporting.inventory_categories";
        if (!Schema::connection($connection)->hasTable($invCatsTable)) {
            Schema::connection($connection)->create($invCatsTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('name')->nullable();
                $table->string('description')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
                $table->jsonb('payload')->nullable();
            });
        }
    }

    protected function assertPgsqlConnection(string $connection): void
    {
        $driver = config("database.connections.{$connection}.driver");
        if ($driver !== 'pgsql') {
            throw new RuntimeException("The [{$connection}] connection must use the pgsql driver for AI/Vector features.");
        }
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
