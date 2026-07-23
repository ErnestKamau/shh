<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * lab_section_ids stores a comma-separated list of UUIDs and can exceed varchar(255).
     */
    public function up(): void
    {
        if (
            ! Schema::hasTable('lab_section_approver_configuration')
            || ! Schema::hasColumn('lab_section_approver_configuration', 'lab_section_ids')
        ) {
            return;
        }

        DB::statement('
            ALTER TABLE lab_section_approver_configuration
            ALTER COLUMN lab_section_ids TYPE text
            USING lab_section_ids::text
        ');
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('lab_section_approver_configuration')
            || ! Schema::hasColumn('lab_section_approver_configuration', 'lab_section_ids')
        ) {
            return;
        }

        DB::statement('
            ALTER TABLE lab_section_approver_configuration
            ALTER COLUMN lab_section_ids TYPE character varying(255)
            USING LEFT(lab_section_ids::text, 255)
        ');
    }
};
