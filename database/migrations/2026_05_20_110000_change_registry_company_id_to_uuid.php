<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['workflow_definitions', 'registry_request_categories', 'registry_requests'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            DB::statement("ALTER TABLE {$table} ALTER COLUMN company_id DROP DEFAULT");
            DB::statement("ALTER TABLE {$table} ALTER COLUMN company_id DROP NOT NULL");

            // Legacy rows used bigint 0 for "global"; companies table uses UUID ids.
            DB::statement("ALTER TABLE {$table} ALTER COLUMN company_id TYPE UUID USING (NULL::uuid)");
        }
    }

    public function down(): void
    {
        foreach (['workflow_definitions', 'registry_request_categories', 'registry_requests'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            DB::statement("ALTER TABLE {$table} ALTER COLUMN company_id TYPE BIGINT USING (
                CASE WHEN company_id IS NULL THEN 0 ELSE 0 END
            )");

            DB::statement("ALTER TABLE {$table} ALTER COLUMN company_id SET DEFAULT 0");
            DB::statement("ALTER TABLE {$table} ALTER COLUMN company_id SET NOT NULL");
        }
    }
};
