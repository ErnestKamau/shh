<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SystemConfigPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'system.configuration-types.view',
            'system.configuration-types.add',
            'system.configuration-types.edit',
            'system.configurations.view',
            'system.configurations.add',
            'system.configurations.edit',
            'system.configurations.delete',
            'system.configuration_type.view',
            'system.configuration_type.add',
            'system.configuration_type.edit',
            'system.configuration.view',
            'system.configuration.add',
            'system.configuration.edit',
            'system.configuration.delete',
            'system.companies.view',
            'system.companies.add',
            'system.companies.edit',
            'system.module-switching.view',
            'system.dashboard.view',
            'system.dashboard.actions',
            'system.dashboard.export',
            'system.translations.view',
            'system.translations.language.view',
            'system.translations.language.add',
            'system.translations.language.edit',
            'system.translations.language.delete',
            'system.translations.keys.view',
            'system.translations.keys.add',
            'system.translations.keys.edit',
            'system.translations.keys.delete',
            'system.components.translations.bulk import',
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $count = count($permissionNames);
        $this->command->info("System config permissions ensured: {$count}");
    }
}
