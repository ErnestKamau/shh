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

        if (Schema::hasColumn('quotation_headers', 'prepared_by_id')) {
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN prepared_by_id TYPE uuid USING NULL');
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN prepared_by_id DROP NOT NULL');
        }

        if (Schema::hasColumn('quotation_headers', 'approved_by')) {
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN approved_by TYPE uuid USING NULL');
            DB::statement('ALTER TABLE quotation_headers ALTER COLUMN approved_by DROP NOT NULL');
        }
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
