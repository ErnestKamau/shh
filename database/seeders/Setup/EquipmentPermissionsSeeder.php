<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class EquipmentPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            // Legacy gates used in routes/components.
            'equipment.module.access',
            'equipment.permission',

            // Dashboard.
            'equipment.components.dashboard.view',

            // Equipment list/detail.
            'equipment.components.equipment-list.view',
            'equipment.components.equipment-list.add',
            'equipment.components.equipment-list.edit',
            'equipment.components.equipment-list.delete',

            // Checks and daily logs.
            'equipment.components.equipment-checks.view',
            'equipment.components.equipment-checks.add',
            'equipment.components.equipment-checks.edit',
            'equipment.components.equipment-daily-log.view',
            'equipment.components.equipment-daily-log.add',
            'equipment.components.equipment-daily-log.edit',
            'equipment.components.equipment-daily-log.delete',

            // Maintenance/calibration/verification/operator logs.
            'equipment.components.maintainance-log.view',
            'equipment.components.maintainance-log.add',
            'equipment.components.maintainance-log.edit',
            'equipment.components.maintainance-log.delete',
            'equipment.components.verification-log.view',
            'equipment.components.verification-log.add',
            'equipment.components.verification-log.edit',
            'equipment.components.verification-log.delete',
            'equipment.components.operator-log.view',
            'equipment.components.operator-log.add',
            'equipment.components.operator-log.edit',
            'equipment.components.operator-log.delete',
            'equipment.components.repair-log.view',
            'equipment.components.repair-log.add',
            'equipment.components.repair-log.edit',
            'equipment.components.repair-log.delete',

            // Asset setup.
            'equipment.components.asset-type.view',
            'equipment.components.asset-type.add',
            'equipment.components.asset-type.edit',
            'equipment.components.asset-type.delete',
            'equipment.components.asset-location.view',
            'equipment.components.asset-location.add',
            'equipment.components.asset-location.edit',
            'equipment.components.asset-location.delete',

            // Disposal/workflow.
            'equipment.components.equipment-disposal.view',
            'equipment.components.equipment-disposal.add',
            'equipment.components.equipment-disposal.edit',
            'equipment.components.equipment-disposal.delete',
            'equipment.components.equipment-disposal.approve',
            'equipment.components.equipment-disposal.execute',
            'equipment.components.equipment-disposal.report',
            'equipment.components.disposal-workflow.view',
            'equipment.components.disposal-workflow.add',
            'equipment.components.disposal-workflow.edit',
            'equipment.components.disposal-workflow.delete',

            // Asset depreciation.
            'equipment.components.depreciation.view',
            'equipment.components.depreciation.configure',
            'equipment.components.depreciation.recalculate',
            'equipment.components.depreciation.appraisal.create',
            'equipment.components.depreciation.appraisal.approve',
            'equipment.components.depreciation.export',
            'equipment.components.depreciation.methods.view',
            'equipment.components.depreciation.methods.add',
            'equipment.components.depreciation.methods.edit',
            'equipment.components.depreciation.methods.delete',
        ];

        $permissions = [];
        foreach ($permissionNames as $permissionName) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        Role::query()->firstOrCreate(
            [
                'name' => 'admin',
                'guard_name' => 'web',
            ],
            [
                'description' => 'System administrator group role',
                'level' => 1,
                'active' => true,
            ]
        );

        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($query): void {
                $query->whereRaw('LOWER(name) = ?', ['admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['super admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['super-admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['system admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['system-admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['system admin group'])
                    ->orWhereRaw('LOWER(name) = ?', ['super admin group']);
            })
            ->get();

        foreach ($adminRoles as $adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Equipment permissions ensured: ' . count($permissionNames));

        $this->call(DepreciationMethodsSeeder::class);
    }
}
