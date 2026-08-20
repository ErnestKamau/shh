<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('request_entities')) {
            $this->convertIntegerFkToUuid('request_entities', 'parent_request_id');
            $this->convertIntegerFkToUuid('request_entities', 'parent_material_requisition');
            $this->convertIntegerFkToUuid('request_entities', 'request_initiator');
            $this->convertIntegerFkToUuid('request_entities', 'issue_to');
        }

        if (Schema::hasTable('supplier_categories') && Schema::hasColumn('supplier_categories', 'inventory_item_brand_id')) {
            DB::statement('ALTER TABLE supplier_categories ALTER COLUMN inventory_item_brand_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Intentionally not reversing UUID conversions — legacy integer values cannot be restored safely.
    }

    private function convertIntegerFkToUuid(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        $type = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );

        if (! $type || $type->data_type === 'uuid') {
            return;
        }

        // Null out non-UUID legacy integers, then cast column to uuid.
        DB::statement("UPDATE {$table} SET {$column} = NULL WHERE {$column} IS NOT NULL AND {$column}::text !~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE uuid USING {$column}::text::uuid");
    }
};
