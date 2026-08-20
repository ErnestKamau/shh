<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * request_entities / inventory stores use uuid ids, but request_entity_items
     * still had integer FK columns for request_id, store_id and slot_id.
     */
    public function up(): void
    {
        $this->convertIntegerFkToUuid('request_entity_items', 'request_id');
        $this->convertIntegerFkToUuid('request_entity_items', 'store_id');
        $this->convertIntegerFkToUuid('request_entity_items', 'slot_id');
        $this->convertIntegerFkToUuid('request_entity_items', 'issued_by');
        $this->convertIntegerFkToUuid('request_entity_items', 'request_entity_item_ammended_id');
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
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE uuid USING {$column}::text::uuid");
    }
};
