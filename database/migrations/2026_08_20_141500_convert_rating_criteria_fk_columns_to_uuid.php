<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // criteria_id may already be uuid from 2026_08_20_141000; convert remaining int FKs.
        $this->convertIntegerFkToUuid('supplier_rating_criteria_guides', 'criteria_id');
        $this->convertIntegerFkToUuid('suppliers_rating_criterias', 'criteria_id');
        $this->convertIntegerFkToUuid('suppliers_rating_criterias', 'rating_by');

        if (Schema::hasTable('suppliers_rating_criterias') && Schema::hasColumn('suppliers_rating_criterias', 'request_id')) {
            $this->convertIntegerFkToUuid('suppliers_rating_criterias', 'request_id');
        }
    }

    public function down(): void
    {
        // Not reversed — legacy integer ids cannot be restored safely.
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

        DB::statement("UPDATE {$table} SET {$column} = NULL WHERE {$column} IS NOT NULL AND {$column}::text !~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE uuid USING {$column}::text::uuid");

        $nulls = DB::table($table)->whereNull($column)->count();
        if ($nulls === 0) {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} SET NOT NULL");
        }
    }
};
