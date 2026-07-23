<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * lab_section_id stores a comma-separated list of UUIDs and can exceed varchar(255).
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'lab_section_id')) {
            return;
        }

        DB::statement('
            ALTER TABLE users
            ALTER COLUMN lab_section_id TYPE text
            USING lab_section_id::text
        ');
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'lab_section_id')) {
            return;
        }

        DB::statement('
            ALTER TABLE users
            ALTER COLUMN lab_section_id TYPE character varying(255)
            USING LEFT(lab_section_id::text, 255)
        ');
    }
};
