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
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('unit_of_measure_conversions')) {
            return;
        }

        $this->convertColumnToUuid('uom1');
        $this->convertColumnToUuid('uom2');
        $this->convertColumnToUuid('location_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('unit_of_measure_conversions')) {
            return;
        }

        $this->convertColumnToInteger('uom1');
        $this->convertColumnToInteger('uom2');
        $this->convertColumnToInteger('location_id');
    }

    private function convertColumnToUuid(string $column): void
    {
        if (! Schema::hasColumn('unit_of_measure_conversions', $column)) {
            return;
        }

        $dataType = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'unit_of_measure_conversions')
            ->where('column_name', $column)
            ->value('data_type');

        if ($dataType === 'uuid') {
            return;
        }

        DB::statement("ALTER TABLE unit_of_measure_conversions ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE unit_of_measure_conversions ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("
            ALTER TABLE unit_of_measure_conversions
            ALTER COLUMN {$column} TYPE uuid
            USING CASE
                WHEN {$column}::text IS NULL THEN NULL
                WHEN {$column}::text IN ('0', '') THEN NULL
                WHEN {$column}::text ~ '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                    THEN {$column}::text::uuid
                ELSE NULL
            END
        ");
    }

    private function convertColumnToInteger(string $column): void
    {
        if (! Schema::hasColumn('unit_of_measure_conversions', $column)) {
            return;
        }

        DB::statement("ALTER TABLE unit_of_measure_conversions ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE unit_of_measure_conversions ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("
            ALTER TABLE unit_of_measure_conversions
            ALTER COLUMN {$column} TYPE integer
            USING CASE
                WHEN {$column} IS NULL THEN NULL
                WHEN {$column}::text ~ '^[0-9]+$' THEN {$column}::text::integer
                ELSE NULL
            END
        ");
    }
};
