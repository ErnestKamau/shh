<?php

namespace Modules\TemplateEngine\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseMetadataService
{
    /**
     * Get a list of all tables in the database.
     * Exclude system tables and migrations.
     */
    public function getTables(): array
    {
        $ignored = [
            'migrations', 'password_resets', 'failed_jobs', 'jobs', 
            'form_templates', 'form_fields', 'form_template_sections', 
            'form_field_options', 'form_template_submissions', 
            'form_template_dataset_bindings', 'sessions', 'cache'
        ];

        return collect($this->tableNames())
            ->reject(fn($name) => in_array($name, $ignored))
            ->values()
            ->toArray();
    }

    /**
     * Get columns for a specific table.
     */
    public function getColumns(string $tableName): array
    {
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        return Schema::getColumnListing($tableName);
    }

    /**
     * Get foreign keys for a specific table.
     */
    public function getForeignKeys(string $tableName): array
    {
        $fks = DB::connection()->getDriverName() === 'pgsql'
            ? $this->postgresForeignKeys($tableName)
            : $this->mysqlForeignKeys($tableName);

        return collect($fks)->map(function ($fk) {
            return [
                'column' => $fk->column_name,
                'referenced_table' => $fk->referenced_table_name,
                'referenced_column' => $fk->referenced_column_name,
            ];
        })->toArray();
    }

    private function tableNames(): array
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return collect(DB::select("
                SELECT tablename AS name
                FROM pg_catalog.pg_tables
                WHERE schemaname = current_schema()
                ORDER BY tablename
            "))->pluck('name')->all();
        }

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        return collect($tables)->map(fn ($table) => $table->$key)->all();
    }

    private function postgresForeignKeys(string $tableName): array
    {
        return DB::select("
            SELECT
                kcu.column_name,
                ccu.table_name AS referenced_table_name,
                ccu.column_name AS referenced_column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = current_schema()
                AND tc.table_name = ?
        ", [$tableName]);
    }

    private function mysqlForeignKeys(string $tableName): array
    {
        return DB::select("
            SELECT
                COLUMN_NAME AS column_name,
                REFERENCED_TABLE_NAME AS referenced_table_name,
                REFERENCED_COLUMN_NAME AS referenced_column_name
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [DB::getDatabaseName(), $tableName]);
    }
}
