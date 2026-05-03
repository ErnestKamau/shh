<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PersonnelPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'personnel.personnel.view',
            'personnel.personnel.add',
            'personnel.personnel.edit',
            'personnel.personnel.delete',
            'personnel.departments.view',
            'personnel.departments.add',
            'personnel.departments.edit',
            'personnel.departments.delete',
            'personnel.roles.view',
            'personnel.roles.add',
            'personnel.roles.edit',
            'personnel.roles.delete',
            'personnel.configurations.view',
            'personnel.configurations.add',
            'personnel.configurations.edit',
            'personnel.configurations.delete',
            'personnel.audit trail.view',
        ];

        $permissions = [];
        foreach ($permissionNames as $permissionName) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['admin', 'Admin'])
            ->get();

        foreach ($adminRoles as $adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        $this->command?->info('Personnel permissions ensured: ' . count($permissionNames));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}