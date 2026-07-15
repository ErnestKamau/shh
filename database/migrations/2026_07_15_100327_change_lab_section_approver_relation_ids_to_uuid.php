<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('lab_section_approver_relation')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        if (Schema::hasColumn('lab_section_approver_relation', 'lab_section_id')) {
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN lab_section_id DROP DEFAULT');
            DB::statement('DELETE FROM lab_section_approver_relation');
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN lab_section_id TYPE uuid USING NULL');
        }

        if (Schema::hasColumn('lab_section_approver_relation', 'parent_id')) {
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN parent_id DROP DEFAULT');
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN parent_id TYPE uuid USING NULL');
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN parent_id DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('lab_section_approver_relation')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        if (Schema::hasColumn('lab_section_approver_relation', 'lab_section_id')) {
            DB::statement('UPDATE lab_section_approver_relation SET lab_section_id = NULL WHERE true');
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN lab_section_id TYPE integer USING NULL');
        }

        if (Schema::hasColumn('lab_section_approver_relation', 'parent_id')) {
            DB::statement('UPDATE lab_section_approver_relation SET parent_id = NULL');
            DB::statement('ALTER TABLE lab_section_approver_relation ALTER COLUMN parent_id TYPE integer USING NULL');
        }
    }
};
