<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function columnType(string $table, string $column): ?string
    {
        $rows = DB::select(
            'SELECT data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ? LIMIT 1',
            [$table, $column]
        );

        return $rows[0]->data_type ?? null;
    }

    private function convertToVarchar(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $type = $this->columnType($table, $column);
        if (in_array($type, ['character varying', 'text'], true)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ALTER COLUMN %s TYPE VARCHAR(64) USING %s::text',
            $table,
            $column,
            $column
        ));
    }

    private function convertToInteger(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $type = $this->columnType($table, $column);
        if ($type === 'integer') {
            return;
        }

        DB::statement(sprintf(
            "ALTER TABLE %s ALTER COLUMN %s TYPE INTEGER USING (CASE WHEN %s ~ '^[0-9]+$' THEN %s::integer ELSE NULL END)",
            $table,
            $column,
            $column,
            $column
        ));
    }

    public function up(): void
    {
        $this->convertToVarchar('chat_message', 'from_user_id');
        $this->convertToVarchar('chat_message', 'to_user_id');
        $this->convertToVarchar('conversation', 'from_user_id');
        $this->convertToVarchar('conversation', 'to_user_id');
    }

    public function down(): void
    {
        $this->convertToInteger('chat_message', 'from_user_id');
        $this->convertToInteger('chat_message', 'to_user_id');
        $this->convertToInteger('conversation', 'from_user_id');
        $this->convertToInteger('conversation', 'to_user_id');
    }
};
