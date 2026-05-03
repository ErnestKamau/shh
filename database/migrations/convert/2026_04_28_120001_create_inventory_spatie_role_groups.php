<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names', []);
        $rolesTable = $tableNames['roles'] ?? 'spatie_roles';

        if (! Schema::hasTable($rolesTable)) {
            return;
        }

        $now = now();
        $groups = [
            'Inventory Assistant Supervisor Group',
            'Inventory Procurement Group',
            'Inventory Department Head Group',
            'Inventory Manager Group',
            'Inventory Finance Group',
            'Inventory Store Manager Group',
        ];

        foreach ($groups as $groupName) {
            $existingRole = DB::table($rolesTable)
                ->where('name', $groupName)
                ->where('guard_name', 'web')
                ->first();

            if ($existingRole) {
                DB::table($rolesTable)
                    ->where('name', $groupName)
                    ->where('guard_name', 'web')
                    ->update(['updated_at' => $now]);

                continue;
            }

            $payload = [
                'name' => $groupName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn($rolesTable, 'id')) {
                $idType = Schema::getColumnType($rolesTable, 'id');
                if (in_array($idType, ['uuid', 'string', 'char'], true)) {
                    $payload['id'] = (string) Str::uuid();
                }
            }

            if (Schema::hasColumn($rolesTable, 'description')) {
                $payload['description'] = $groupName;
            }

            if (Schema::hasColumn($rolesTable, 'level')) {
                $payload['level'] = 1;
            }

            if (Schema::hasColumn($rolesTable, 'active')) {
                $payload['active'] = 1;
            }

            DB::table($rolesTable)->insert($payload);
        }
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names', []);
        $rolesTable = $tableNames['roles'] ?? 'spatie_roles';

        if (! Schema::hasTable($rolesTable)) {
            return;
        }

        DB::table($rolesTable)
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'Inventory Assistant Supervisor Group',
                'Inventory Procurement Group',
                'Inventory Department Head Group',
                'Inventory Manager Group',
                'Inventory Finance Group',
                'Inventory Store Manager Group',
            ])
            ->delete();
    }
};