<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * inventory_items.received_by / edited_by were integers while users.id is uuid.
     */
    public function up(): void
    {
        $this->convertIntegerFkToUuid('inventory_items', 'received_by');
        $this->convertIntegerFkToUuid('inventory_items', 'edited_by');
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
