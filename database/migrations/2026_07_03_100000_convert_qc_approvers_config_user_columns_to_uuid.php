<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('qc_approvers_config')) {
            return;
        }

        if (! $this->columnIsIntegerLike('personnel_id')) {
            return;
        }

        DB::statement('ALTER TABLE qc_approvers_config ALTER COLUMN personnel_id DROP NOT NULL');
        DB::statement('ALTER TABLE qc_approvers_config ALTER COLUMN personnel_id TYPE uuid USING NULL');
        DB::statement('ALTER TABLE qc_approvers_config ALTER COLUMN created_by TYPE uuid USING NULL');
    }

    public function down(): void
    {
        // Column type conversion is not safely reversible once UUID user ids are stored.
    }

    private function columnIsIntegerLike(string $column): bool
    {
        $row = DB::selectOne(
            'SELECT data_type
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            ['qc_approvers_config', $column]
        );

        return in_array($row?->data_type, ['integer', 'bigint', 'smallint'], true);
    }
};
