<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModuleAccessPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'access inventory',
            'access laboratory',
            'access personnel',
            'access skills matrix',
            'access equipment',
            'access crm',
            'access documents',
            'access risk management',
            'access audit',
            'access help desk',
            'access system',
        ];

        $roleAccessMap = [
            'access inventory' => [
                'Inventory Assistant Supervisor Group',
                'Inventory Procurement Group',
                'Inventory Department Head Group',
                'Inventory Manager Group',
                'Inventory Finance Group',
                'Inventory Store Manager Group',
                'Requester',
                'Procurement',
                'Financial Accountant',
                'Lab Manager',
            ],
            'access laboratory' => [
                'Laboratory Analyst',
                'Sample Reception',
                'Sample Desk',
                'Lab Manager',
                'Deputy Lab Manager',
                'Quality Manager',
                'Process Chemist',
                'Can Approve Samples',
                'Can Verify Samples',
                'Can View Qc Samples',
            ],
            'access personnel' => [
                'Access Personnel',
                'Deactivate Personnel',
                'Lab Manager',
            ],
            'access skills matrix' => [
                'Admin Skill Matrix',
                'Lab Manager',
                'Access Personnel',
            ],
            'access equipment' => [
                'Engineering Head Role',
                'Lab Manager',
                'Quality Manager',
            ],
            'access crm' => [
                'Sample Desk',
                'Lab Manager',
                'Quality Manager',
            ],
            'access documents' => [
                'Quality Manager',
                'Lab Manager',
            ],
            'access risk management' => [
                'Quality Manager',
                'Lab Manager',
            ],
            'access audit' => [
                'View All System Events',
                'Quality Manager',
                'Lab Manager',
            ],
            'access help desk' => [
                'View All System Events',
            ],
            'access system' => [
                'View All System Events',
            ],
        ];

        $adminRoleNames = ['Admin', 'admin'];

        foreach ($permissions as $permissionName) {
            $permission = Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $this->command?->info("Ensured permission: {$permissionName}");

            $targetRoleNames = array_merge($adminRoleNames, $roleAccessMap[$permissionName] ?? []);
            $targetRoleNames = array_values(array_unique($targetRoleNames));

            foreach ($targetRoleNames as $roleName) {
                $role = Role::query()
                    ->where('guard_name', 'web')
                    ->whereRaw('LOWER(name) = ?', [strtolower($roleName)])
                    ->first();

                if (! $role) {
                    $this->command?->warn("Skipped missing role '{$roleName}' for permission '{$permissionName}'.");
                    continue;
                }

                $role->givePermissionTo($permission);
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
