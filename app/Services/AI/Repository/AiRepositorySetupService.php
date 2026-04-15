<?php

namespace App\Services\AI\Repository;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $this->createReportingTables($connection, $schemas['reporting'] ?? 'reporting');

        return [
            'connection' => $connection,
            'schemas' => array_values($schemas),
            'pgvector_enabled' => $withPgvector && config('imara_ai.pgvector.enabled', true),
        ];
    }

    protected function createReportingTables(string $connection, string $schema): void
    {
        $syncRunsTable = "{$schema}.sync_runs";
        if (!Schema::connection($connection)->hasTable($syncRunsTable)) {
            Schema::connection($connection)->create($syncRunsTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('sync_scope');
                $table->string('source_table')->nullable();
                $table->string('target_table')->nullable();
                $table->string('status')->default('pending');
                $table->unsignedInteger('rows_synced')->default(0);
                $table->timestampTz('started_at')->nullable();
                $table->timestampTz('finished_at')->nullable();
                $table->text('error_message')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestampsTz();
            });
        }

        $sampleHeadersTable = "{$schema}.sample_headers";
        if (!Schema::connection($connection)->hasTable($sampleHeadersTable)) {
            Schema::connection($connection)->create($sampleHeadersTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('batch_code')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('crm_customer_id')->nullable();
                $table->unsignedBigInteger('verify_user_id')->nullable();
                $table->unsignedBigInteger('approve_user_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->boolean('is_qc_batch')->default(false);
                $table->boolean('isactive')->default(true);
                $table->timestampTz('processing_date')->nullable();
                $table->timestampTz('approval_date_at')->nullable();
                $table->string('approval_date_raw')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->jsonb('payload')->nullable();

                $table->index('status');
                $table->index('crm_customer_id');
                $table->index('is_qc_batch');
            });
        }

        $sampleDatesTable = "{$schema}.sample_dates";
        if (!Schema::connection($connection)->hasTable($sampleDatesTable)) {
            Schema::connection($connection)->create($sampleDatesTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->unsignedBigInteger('sample_header_id')->nullable();
                $table->string('name')->nullable();
                $table->timestampTz('event_date')->nullable();
                $table->string('event_date_raw')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->jsonb('payload')->nullable();

                $table->index('sample_header_id');
                $table->index('name');
                $table->index('event_date');
            });
        }

        $capturedResultsTable = "{$schema}.captured_results";
        if (!Schema::connection($connection)->hasTable($capturedResultsTable)) {
            Schema::connection($connection)->create($capturedResultsTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->unsignedBigInteger('sample_header_id')->nullable();
                $table->unsignedBigInteger('sample_detail_id')->nullable();
                $table->unsignedBigInteger('analysis_type_id')->nullable();
                $table->unsignedBigInteger('analyte_id')->nullable();
                $table->unsignedBigInteger('operator_id')->nullable();
                $table->unsignedBigInteger('equipment_id')->nullable();
                $table->text('result_value')->nullable();
                $table->string('analyte_code')->nullable();
                $table->string('sample_detail_code')->nullable();
                $table->timestampTz('machine_update_date')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->jsonb('payload')->nullable();

                $table->index('sample_header_id');
                $table->index('sample_detail_id');
                $table->index('analysis_type_id');
                $table->index('analyte_id');
                $table->index('operator_id');
            });
        }

        $equipmentLogsTable = "{$schema}.equipment_logs";
        if (!Schema::connection($connection)->hasTable($equipmentLogsTable)) {
            Schema::connection($connection)->create($equipmentLogsTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('source_table');
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('equipment_id')->nullable();
                $table->string('event_type');
                $table->timestampTz('event_date')->nullable();
                $table->string('event_date_raw')->nullable();
                $table->string('service_provider')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->unsignedBigInteger('edited_by')->nullable();
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->jsonb('payload')->nullable();

                $table->unique(['source_table', 'source_id']);
                $table->index('equipment_id');
                $table->index('event_type');
                $table->index('event_date');
            });
        }

        $auditFindingsTable = "{$schema}.audit_findings";
        if (!Schema::connection($connection)->hasTable($auditFindingsTable)) {
            Schema::connection($connection)->create($auditFindingsTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->unsignedBigInteger('audit_id')->nullable();
                $table->string('finding_number')->nullable();
                $table->string('category')->nullable();
                $table->string('iso_clause')->nullable();
                $table->string('risk_level')->nullable();
                $table->string('status')->nullable();
                $table->string('responsible_person')->nullable();
                $table->date('due_date')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->jsonb('payload')->nullable();

                $table->index('audit_id');
                $table->index('category');
                $table->index('status');
                $table->index('risk_level');
            });
        }

        $correctiveActionsTable = "{$schema}.corrective_actions";
        if (!Schema::connection($connection)->hasTable($correctiveActionsTable)) {
            Schema::connection($connection)->create($correctiveActionsTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('capa_number')->nullable();
                $table->unsignedBigInteger('non_conformance_id')->nullable();
                $table->string('category')->nullable();
                $table->string('action_type')->nullable();
                $table->string('status')->nullable();
                $table->string('priority')->nullable();
                $table->string('owner')->nullable();
                $table->date('due_date')->nullable();
                $table->date('implementation_date')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->nullable();
                $table->jsonb('payload')->nullable();

                $table->index('status');
                $table->index('priority');
                $table->index('due_date');
            });
        }

        // New Inventory Tables for Parity
        $invItemsTable = "{$schema}.inventory_items";
        if (!Schema::connection($connection)->hasTable($invItemsTable)) {
            Schema::connection($connection)->create($invItemsTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->unsignedBigInteger('inventory_sub_category_id')->nullable();
                $table->unsignedBigInteger('inventory_store_id')->nullable();
                $table->unsignedBigInteger('inventory_store_slot_id')->nullable();
                $table->unsignedBigInteger('inventory_department_id')->nullable();
                $table->string('status')->nullable();
                $table->integer('stock_in')->default(0);
                $table->integer('stock_out')->default(0);
                $table->date('expiry')->nullable();
                $table->double('price')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
                $table->jsonb('payload')->nullable();
            });
        }

        $invSubCatsTable = "{$schema}.inventory_sub_categories";
        if (!Schema::connection($connection)->hasTable($invSubCatsTable)) {
            Schema::connection($connection)->create($invSubCatsTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->unsignedBigInteger('inventory_category_id')->nullable();
                $table->string('name')->nullable();
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
                $table->jsonb('payload')->nullable();
            });
        }

        $invOrdersTable = "{$schema}.inventory_orders";
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

        $suppliersTable = "{$schema}.suppliers";
        if (!Schema::connection($connection)->hasTable($suppliersTable)) {
            Schema::connection($connection)->create($suppliersTable, function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->smallInteger('active')->default(1);
                $table->timestampTz('source_created_at')->nullable();
                $table->timestampTz('source_updated_at')->nullable();
                $table->timestampTz('synced_at')->useCurrent();
                $table->jsonb('payload')->nullable();
            });
        }

        $invCatsTable = "{$schema}.inventory_categories";
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
