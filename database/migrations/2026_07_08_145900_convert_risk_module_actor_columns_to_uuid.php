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
            'risk_assessments' => ['assessed_by_user_id', 'updated_by'],
            'risk_evaluations' => ['evaluated_by_user_id', 'updated_by'],
            'risk_reviews' => ['reviewed_by_user_id'],
            'risk_treatment_plans' => ['responsible_user_id', 'updated_by'],
            'risk_treatment_implementations' => ['responsible_user_id', 'updated_by'],
            'risk_configuration_options' => ['updated_by'],
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
            'risk_assessments' => ['assessed_by_user_id', 'updated_by'],
            'risk_evaluations' => ['evaluated_by_user_id', 'updated_by'],
            'risk_reviews' => ['reviewed_by_user_id'],
            'risk_treatment_plans' => ['responsible_user_id', 'updated_by'],
            'risk_treatment_implementations' => ['responsible_user_id', 'updated_by'],
            'risk_configuration_options' => ['updated_by'],
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
