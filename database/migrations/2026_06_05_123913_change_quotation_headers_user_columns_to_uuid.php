<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_headers')) {
            return;
        }

        if ($this->columnIsInteger('prepared_by_id')) {
            DB::statement('DROP VIEW IF EXISTS quotation_header_view');
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN prepared_by_id TYPE uuid USING NULL');
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN prepared_by_id DROP NOT NULL');
        }

        if ($this->columnIsInteger('approved_by')) {
            DB::statement('DROP VIEW IF EXISTS quotation_header_view');
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN approved_by TYPE uuid USING NULL');
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN approved_by DROP NOT NULL');
        }
    }

    private function columnIsInteger(string $column): bool
    {
        $result = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?',
            ['quotation_headers', $column]
        );

        return $result !== null && $result->data_type === 'integer';
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotation_headers')) {
            return;
        }

        if (Schema::hasColumn('quotation_headers', 'prepared_by_id')) {
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN prepared_by_id TYPE integer USING NULL');
        }

        if (Schema::hasColumn('quotation_headers', 'approved_by')) {
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN approved_by TYPE integer USING NULL');
        }
    }
};
