<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EquipmentMaintenancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'equipment.maintenance.view',
            'equipment.maintenance.add',
            'equipment.maintenance.edit',
            'equipment.maintenance.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);

            $this->command->info("Ensured permission: {$permissionName}");
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Equipment Maintenance permissions seeded successfully.');
    }
}
