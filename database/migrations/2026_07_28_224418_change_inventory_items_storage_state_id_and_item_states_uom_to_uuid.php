<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $this->convertColumnToUuid('inventory_items', 'storage_state_id');
        $this->convertColumnToUuid('item_states', 'uom');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $this->convertColumnToVarchar('inventory_items', 'storage_state_id');
        $this->convertColumnToInteger('item_states', 'uom');
    }

    private function convertColumnToUuid(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $isUuid = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->where('udt_name', 'uuid')
            ->exists();

        if ($isUuid) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement(
            "ALTER TABLE {$table}
             ALTER COLUMN {$column} TYPE uuid
             USING (
                CASE
                    WHEN {$column}::text IS NULL THEN NULL
                    WHEN BTRIM({$column}::text) IN ('', '0') THEN NULL
                    WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                        THEN {$column}::text::uuid
                    ELSE NULL
                END
             )"
        );
    }

    private function convertColumnToVarchar(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE character varying(100) USING {$column}::text");
    }

    private function convertColumnToInteger(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement(
            "ALTER TABLE {$table}
             ALTER COLUMN {$column} TYPE integer
             USING (
                CASE
                    WHEN {$column} IS NULL THEN NULL
                    WHEN {$column}::text ~ '^[0-9]+$' THEN {$column}::text::integer
                    ELSE NULL
                END
             )"
        );
    }
};
