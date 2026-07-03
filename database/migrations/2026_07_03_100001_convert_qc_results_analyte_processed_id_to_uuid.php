<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('qc_results') || ! Schema::hasTable('qc_processed_result')) {
            return;
        }

        if (! $this->columnIsIntegerLike('qc_results', 'analyte_processed_id')) {
            return;
        }

        DB::statement('ALTER TABLE qc_results ALTER COLUMN analyte_processed_id DROP NOT NULL');
        DB::statement('ALTER TABLE qc_results ALTER COLUMN analyte_processed_id TYPE uuid USING NULL');
    }

    public function down(): void
    {
        // Not safely reversible once UUID processed-result links are stored.
    }

    private function columnIsIntegerLike(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT data_type
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            [$table, $column]
        );

        return in_array($row?->data_type, ['integer', 'bigint', 'smallint'], true);
    }
};
