<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-section integrity assignments can exceed varchar(100)
     * (3 UUIDs + commas = 110 chars).
     */
    public function up(): void
    {
        if (! Schema::hasTable('sample_headers') || ! Schema::hasColumn('sample_headers', 'lab_section_ids')) {
            return;
        }

        DB::statement('
            ALTER TABLE sample_headers
            ALTER COLUMN lab_section_ids TYPE text
            USING lab_section_ids::text
        ');
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_headers') || ! Schema::hasColumn('sample_headers', 'lab_section_ids')) {
            return;
        }

        DB::statement("
            ALTER TABLE sample_headers
            ALTER COLUMN lab_section_ids TYPE varchar(100)
            USING LEFT(lab_section_ids, 100)
        ");
    }
};
