<?php

namespace Database\Seeders;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class EquipmentMaintenancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            // Canonical names used by the equipment permission UI / admin role grants.
            'equipment.components.equipment-maintenance.view',
            'equipment.components.equipment-maintenance.add',
            'equipment.components.equipment-maintenance.edit',
            'equipment.components.equipment-maintenance.delete',

            // Legacy aliases kept for environments that already granted the older keys.
            'equipment.maintenance.view',
            'equipment.maintenance.add',
            'equipment.maintenance.edit',
            'equipment.maintenance.delete',
        ];

        $permissions = [];
        foreach ($permissionNames as $permissionName) {
            $permissions[] = Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $this->command?->info("Ensured permission: {$permissionName}");
        }

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

        $this->command?->info('Equipment Maintenance permissions seeded successfully.');
    }
}
