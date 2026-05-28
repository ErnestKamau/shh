<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy solution_preparations used bigint user/uom columns; app uses UUID users and reporting_units.
     */
    public function up(): void
    {
        if (! Schema::hasTable('solution_preparations')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->upPgsql();

            return;
        }

        if ($driver === 'mysql') {
            $this->upMysql();
        }
    }

    public function down(): void
    {
        // Irreversible without data loss when UUIDs were assigned.
    }

    private function upPgsql(): void
    {
        $schema = $this->pgsqlSchema();

        foreach (['prepared_by', 'approved_by', 'uom_id'] as $column) {
            if (! Schema::hasColumn('solution_preparations', $column)) {
                continue;
            }

            $udt = DB::table('information_schema.columns')
                ->where('table_schema', $schema)
                ->where('table_name', 'solution_preparations')
                ->where('column_name', $column)
                ->value('udt_name');

            if ($udt === 'uuid') {
                continue;
            }

            DB::statement("ALTER TABLE solution_preparations ALTER COLUMN {$column} DROP NOT NULL");

            DB::statement("
                ALTER TABLE solution_preparations
                ALTER COLUMN {$column} TYPE uuid
                USING (
                    CASE
                        WHEN {$column}::text ~ '^[0-9a-fA-F-]{36}$'
                            THEN {$column}::text::uuid
                        ELSE NULL
                    END
                )
            ");
        }
    }

    private function upMysql(): void
    {
        foreach (['prepared_by', 'approved_by', 'uom_id'] as $column) {
            if (! Schema::hasColumn('solution_preparations', $column)) {
                continue;
            }

            $col = DB::selectOne("SHOW COLUMNS FROM solution_preparations WHERE Field = '{$column}'");
            if ($col && str_contains(strtolower((string) $col->Type), 'char')) {
                continue;
            }

            DB::statement("ALTER TABLE solution_preparations MODIFY {$column} VARCHAR(36) NULL");
            DB::statement("
                UPDATE solution_preparations
                SET {$column} = NULL
                WHERE {$column} IS NOT NULL
                  AND (
                    CHAR_LENGTH({$column}) <> 36
                    OR {$column} NOT REGEXP '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                  )
            ");
            DB::statement("ALTER TABLE solution_preparations MODIFY {$column} CHAR(36) NULL");
        }
    }

    private function pgsqlSchema(): string
    {
        $path = Schema::getConnection()->getConfig('search_path');
        if (is_string($path) && $path !== '') {
            return trim(explode(',', $path)[0]);
        }

        return 'public';
    }
};
