<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy lab_stock_movement.uom_id is integer; app uses UUID reporting_units.
     */
    public function up(): void
    {
        if (! Schema::hasTable('lab_stock_movement') || ! Schema::hasColumn('lab_stock_movement', 'uom_id')) {
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

        $udt = DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', 'lab_stock_movement')
            ->where('column_name', 'uom_id')
            ->value('udt_name');

        if ($udt === 'uuid') {
            return;
        }

        DB::statement('ALTER TABLE lab_stock_movement ALTER COLUMN uom_id DROP NOT NULL');

        DB::statement("
            ALTER TABLE lab_stock_movement
            ALTER COLUMN uom_id TYPE uuid
            USING (
                CASE
                    WHEN uom_id::text ~ '^[0-9a-fA-F-]{36}$'
                        THEN uom_id::text::uuid
                    ELSE NULL
                END
            )
        ");
    }

    private function upMysql(): void
    {
        $col = DB::selectOne("SHOW COLUMNS FROM lab_stock_movement WHERE Field = 'uom_id'");
        if ($col && str_contains(strtolower((string) $col->Type), 'char')) {
            return;
        }

        DB::statement('ALTER TABLE lab_stock_movement MODIFY uom_id VARCHAR(36) NULL');
        DB::statement("
            UPDATE lab_stock_movement
            SET uom_id = NULL
            WHERE uom_id IS NOT NULL
              AND (
                CHAR_LENGTH(uom_id) <> 36
                OR uom_id NOT REGEXP '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
              )
        ");
        DB::statement('ALTER TABLE lab_stock_movement MODIFY uom_id CHAR(36) NULL');
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
