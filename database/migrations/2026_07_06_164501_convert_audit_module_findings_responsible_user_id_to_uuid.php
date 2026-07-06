<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_module_findings') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('audit_module_findings', 'responsible_user_id')) {
            return;
        }

        $isUuid = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'audit_module_findings')
            ->where('column_name', 'responsible_user_id')
            ->where('udt_name', 'uuid')
            ->exists();

        if ($isUuid) {
            return;
        }

        DB::statement(
            'ALTER TABLE audit_module_findings ALTER COLUMN responsible_user_id TYPE uuid USING NULLIF(responsible_user_id::text, \'\')::uuid'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('audit_module_findings') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasColumn('audit_module_findings', 'responsible_user_id')) {
            return;
        }

        DB::statement(
            'ALTER TABLE audit_module_findings ALTER COLUMN responsible_user_id TYPE bigint USING NULLIF(responsible_user_id::text, \'\')::bigint'
        );
    }
};
