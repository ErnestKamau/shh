<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('iso_audits') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            'lead_auditor_id',
            'auditee_department_id',
            'updated_by',
            'closed_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('iso_audits', $column)) {
                continue;
            }

            $isUuid = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', 'iso_audits')
                ->where('column_name', $column)
                ->where('udt_name', 'uuid')
                ->exists();

            if ($isUuid) {
                continue;
            }

            DB::statement(
                "ALTER TABLE iso_audits ALTER COLUMN {$column} TYPE uuid USING NULLIF({$column}::text, '')::uuid"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('iso_audits') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            'lead_auditor_id',
            'auditee_department_id',
            'updated_by',
            'closed_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('iso_audits', $column)) {
                continue;
            }

            DB::statement(
                "ALTER TABLE iso_audits ALTER COLUMN {$column} TYPE bigint USING NULLIF({$column}::text, '')::bigint"
            );
        }
    }
};
