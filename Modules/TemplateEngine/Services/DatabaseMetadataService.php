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
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        $ignored = [
            'migrations', 'password_resets', 'failed_jobs', 'jobs', 
            'form_templates', 'form_fields', 'form_template_sections', 
            'form_field_options', 'form_template_submissions', 
            'form_template_dataset_bindings', 'sessions', 'cache'
        ];

        return collect($tables)
            ->map(fn($t) => $t->$key)
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
     * This relies on information_schema and is MySQL specific.
     */
    public function getForeignKeys(string $tableName): array
    {
        $dbName = DB::getDatabaseName();
        
        $fks = DB::select("
            SELECT 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = ? AND 
                TABLE_NAME = ? AND 
                REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $tableName]);

        return collect($fks)->map(function($fk) {
            return [
                'column' => $fk->COLUMN_NAME,
                'referenced_table' => $fk->REFERENCED_TABLE_NAME,
                'referenced_column' => $fk->REFERENCED_COLUMN_NAME
            ];
        })->toArray();
    }
}
