<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tableColumns = [
            'registry_requests' => ['submitted_by', 'assigned_to', 'received_by'],
            'registry_request_actions' => ['performed_by'],
            'registry_request_assignments' => ['assigned_to', 'assigned_by'],
            'registry_request_documents' => ['uploaded_by'],
        ];

        foreach ($tableColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $isUuid = DB::table('information_schema.columns')
                    ->where('table_schema', 'public')
                    ->where('table_name', $table)
                    ->where('column_name', $column)
                    ->where('udt_name', 'uuid')
                    ->exists();

                if ($isUuid) {
                    continue;
                }

                DB::statement(
                    "ALTER TABLE {$table} ALTER COLUMN {$column} TYPE uuid USING (CASE WHEN {$column} IS NULL THEN NULL WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$' THEN {$column}::text::uuid ELSE NULL END)"
                );
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tableColumns = [
            'registry_requests' => ['submitted_by', 'assigned_to', 'received_by'],
            'registry_request_actions' => ['performed_by'],
            'registry_request_assignments' => ['assigned_to', 'assigned_by'],
            'registry_request_documents' => ['uploaded_by'],
        ];

        foreach ($tableColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement(
                    "ALTER TABLE {$table} ALTER COLUMN {$column} TYPE bigint USING NULLIF({$column}::text, '')::bigint"
                );
            }
        }
    }
};
