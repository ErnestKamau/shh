<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('non_conformances') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            'identified_by_user_id',
            'personnel_id',
            'closed_by',
            'updated_by',
            'department_id',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('non_conformances', $column)) {
                continue;
            }

            $isUuid = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', 'non_conformances')
                ->where('column_name', $column)
                ->where('udt_name', 'uuid')
                ->exists();

            if ($isUuid) {
                continue;
            }

            DB::statement(
                "ALTER TABLE non_conformances ALTER COLUMN {$column} TYPE uuid USING NULLIF({$column}::text, '')::uuid"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('non_conformances') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            'identified_by_user_id',
            'personnel_id',
            'closed_by',
            'updated_by',
            'department_id',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('non_conformances', $column)) {
                continue;
            }

            DB::statement(
                "ALTER TABLE non_conformances ALTER COLUMN {$column} TYPE bigint USING NULLIF({$column}::text, '')::bigint"
            );
        }
    }
};
