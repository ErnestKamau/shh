<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('qc_results')) {
            return;
        }

        if (! $this->columnIsIntegerLike('analyte_processed_id')) {
            return;
        }

        DB::statement('ALTER TABLE qc_results ALTER COLUMN analyte_processed_id TYPE uuid USING NULL::uuid');
    }

    public function down(): void
    {
        // Column type conversion is not safely reversible once UUID processed-result ids are stored.
    }

    private function columnIsIntegerLike(string $column): bool
    {
        $row = DB::selectOne(
            'SELECT data_type
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            ['qc_results', $column]
        );

        return in_array($row?->data_type, ['integer', 'bigint', 'smallint'], true);
    }
};
