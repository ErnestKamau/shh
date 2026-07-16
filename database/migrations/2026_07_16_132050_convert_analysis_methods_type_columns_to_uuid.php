<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analysis_methods') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (['reference_type_id', 'method_type_id'] as $column) {
            if (! Schema::hasColumn('analysis_methods', $column)) {
                continue;
            }

            $isUuid = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', 'analysis_methods')
                ->where('column_name', $column)
                ->where('udt_name', 'uuid')
                ->exists();

            if ($isUuid) {
                continue;
            }

            DB::statement(
                "ALTER TABLE analysis_methods ALTER COLUMN {$column} TYPE uuid USING (
                    CASE
                        WHEN {$column} IS NULL THEN NULL
                        WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$' THEN {$column}::text::uuid
                        ELSE NULL
                    END
                )"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('analysis_methods') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (['reference_type_id', 'method_type_id'] as $column) {
            if (! Schema::hasColumn('analysis_methods', $column)) {
                continue;
            }

            DB::statement(
                "ALTER TABLE analysis_methods ALTER COLUMN {$column} TYPE integer USING NULL"
            );
        }
    }
};
