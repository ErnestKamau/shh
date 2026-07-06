<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('corrective_actions') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            'action_owner_id',
            'updated_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('corrective_actions', $column)) {
                continue;
            }

            $isUuid = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', 'corrective_actions')
                ->where('column_name', $column)
                ->where('udt_name', 'uuid')
                ->exists();

            if ($isUuid) {
                continue;
            }

            DB::statement(
                "ALTER TABLE corrective_actions ALTER COLUMN {$column} TYPE uuid USING NULLIF({$column}::text, '')::uuid"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('corrective_actions') || DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            'action_owner_id',
            'updated_by',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('corrective_actions', $column)) {
                continue;
            }

            DB::statement(
                "ALTER TABLE corrective_actions ALTER COLUMN {$column} TYPE bigint USING NULLIF({$column}::text, '')::bigint"
            );
        }
    }
};
