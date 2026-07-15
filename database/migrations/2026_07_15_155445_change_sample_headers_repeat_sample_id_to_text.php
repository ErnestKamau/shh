<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repeat sample IDs are UUIDs (and may be comma-separated for multi-select).
     * The legacy integer column cannot store them.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sample_headers') || ! Schema::hasColumn('sample_headers', 'repeat_sample_id')) {
            return;
        }

        DB::statement("
            ALTER TABLE sample_headers
            ALTER COLUMN repeat_sample_id TYPE text
            USING NULLIF(repeat_sample_id::text, '')
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_headers') || ! Schema::hasColumn('sample_headers', 'repeat_sample_id')) {
            return;
        }

        DB::statement("
            ALTER TABLE sample_headers
            ALTER COLUMN repeat_sample_id TYPE integer
            USING CASE
                WHEN repeat_sample_id IS NULL OR repeat_sample_id = '' THEN NULL
                WHEN repeat_sample_id ~ '^[0-9]+$' THEN repeat_sample_id::integer
                ELSE NULL
            END
        ");
    }
};
