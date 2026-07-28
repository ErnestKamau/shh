<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * @var list<string>
     */
    private array $tables = [
        'inventory_sub_categories',
        'item_states',
        'unit_of_measure_conversions',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            $this->convertMaterialTypeIdToUuid($table);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            $this->convertMaterialTypeIdToInteger($table);
        }
    }

    private function convertMaterialTypeIdToUuid(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'material_type_id')) {
            return;
        }

        $isUuid = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', $table)
            ->where('column_name', 'material_type_id')
            ->where('udt_name', 'uuid')
            ->exists();

        if ($isUuid) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN material_type_id DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN material_type_id DROP NOT NULL");
        DB::statement(
            "ALTER TABLE {$table}
             ALTER COLUMN material_type_id TYPE uuid
             USING (
                CASE
                    WHEN material_type_id::text IS NULL THEN NULL
                    WHEN material_type_id::text IN ('0', '') THEN NULL
                    WHEN material_type_id::text ~ '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                        THEN material_type_id::text::uuid
                    ELSE NULL
                END
             )"
        );
    }

    private function convertMaterialTypeIdToInteger(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'material_type_id')) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN material_type_id DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN material_type_id DROP NOT NULL");
        DB::statement(
            "ALTER TABLE {$table}
             ALTER COLUMN material_type_id TYPE integer
             USING (
                CASE
                    WHEN material_type_id IS NULL THEN NULL
                    WHEN material_type_id::text ~ '^[0-9]+$' THEN material_type_id::text::integer
                    ELSE NULL
                END
             )"
        );
    }
};
