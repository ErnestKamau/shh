<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasTable('risks')) {
            return;
        }

        $columns = [
            'risk_owner_id',
            'department_id',
            'identified_by_user_id',
            'personnel_id',
            'closed_by',
            'updated_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('risks', $column)) {
                continue;
            }

            $isUuid = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', 'risks')
                ->where('column_name', $column)
                ->where('udt_name', 'uuid')
                ->exists();

            if ($isUuid) {
                continue;
            }

            DB::statement(
                "ALTER TABLE risks ALTER COLUMN {$column} TYPE uuid USING (CASE WHEN {$column} IS NULL THEN NULL WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$' THEN {$column}::text::uuid ELSE NULL END)"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (! Schema::hasTable('risks')) {
            return;
        }

        $columns = [
            'risk_owner_id',
            'department_id',
            'identified_by_user_id',
            'personnel_id',
            'closed_by',
            'updated_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('risks', $column)) {
                continue;
            }

            DB::statement(
                "ALTER TABLE risks ALTER COLUMN {$column} TYPE bigint USING NULLIF({$column}::text, '')::bigint"
            );
        }
    }
};
