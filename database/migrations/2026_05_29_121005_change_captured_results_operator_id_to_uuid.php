<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Users use UUID primary keys; captured_results.operator_id was still integer.
     */
    public function up(): void
    {
        if (! Schema::hasTable('captured_results') || ! Schema::hasColumn('captured_results', 'operator_id')) {
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
        if (! Schema::hasTable('captured_results') || ! Schema::hasColumn('captured_results', 'operator_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('
                ALTER TABLE captured_results
                ALTER COLUMN operator_id TYPE integer
                USING (
                    CASE
                        WHEN operator_id IS NULL THEN NULL
                        ELSE 0
                    END
                )
            ');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE captured_results MODIFY operator_id BIGINT NULL');
        }
    }

    private function upPgsql(): void
    {
        $schema = $this->pgsqlSchema();

        $udtName = DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', 'captured_results')
            ->where('column_name', 'operator_id')
            ->value('udt_name');

        if ($udtName === 'uuid') {
            return;
        }

        DB::statement("
            ALTER TABLE captured_results
            ALTER COLUMN operator_id TYPE uuid
            USING (
                CASE
                    WHEN operator_id IS NULL THEN NULL
                    WHEN operator_id::text ~ '^[0-9a-fA-F-]{36}$'
                        THEN operator_id::text::uuid
                    ELSE NULL
                END
            )
        ");

        DB::statement('
            UPDATE captured_results
            SET operator_id = user_id
            WHERE operator_id IS NULL
              AND user_id IS NOT NULL
        ');
    }

    private function upMysql(): void
    {
        $column = DB::selectOne("SHOW COLUMNS FROM captured_results WHERE Field = 'operator_id'");
        if ($column && str_contains(strtolower((string) $column->Type), 'char')) {
            return;
        }

        DB::statement('ALTER TABLE captured_results MODIFY operator_id VARCHAR(36) NULL');

        DB::update(
            "UPDATE captured_results SET operator_id = NULL WHERE operator_id IS NOT NULL AND (CHAR_LENGTH(operator_id) <> 36 OR operator_id NOT REGEXP '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')"
        );

        DB::statement('
            UPDATE captured_results
            SET operator_id = user_id
            WHERE operator_id IS NULL
              AND user_id IS NOT NULL
        ');

        DB::statement('ALTER TABLE captured_results MODIFY operator_id CHAR(36) NULL');
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
