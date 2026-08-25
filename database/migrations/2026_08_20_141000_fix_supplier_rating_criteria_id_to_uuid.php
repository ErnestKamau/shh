<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * rating_criterias.id is uuid, but related criteria_id columns were still integer.
     */
    public function up(): void
    {
        $this->convertIntegerFkToUuid('supplier_rating_criteria_guides', 'criteria_id');
        $this->convertIntegerFkToUuid('suppliers_rating_criterias', 'criteria_id');
    }

    public function down(): void
    {
        // Intentionally not reversing UUID conversions.
    }

    private function convertIntegerFkToUuid(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $type = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );

        if (! $type || $type->data_type === 'uuid') {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("
            ALTER TABLE {$table}
            ALTER COLUMN {$column} TYPE uuid
            USING CASE
                WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                    THEN {$column}::text::uuid
                ELSE NULL
            END
        ");
    }
};
