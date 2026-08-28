<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class UserManualModulePermissionsSeeder extends Seeder
{
    /**
     * Permissions required by user manual routes (usermanual/*).
     *
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return [
            'usermanual.module.access',
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];
        foreach (self::permissionNames() as $permissionName) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $moduleAccessPermission = $permissions[0];

        $allRoles = Role::query()
            ->where('guard_name', 'web')
            ->get();

        foreach ($allRoles as $role) {
            $role->givePermissionTo($moduleAccessPermission);
        }

        $activeUsers = User::query()
            ->where('active', 1)
            ->get();

        foreach ($activeUsers as $user) {
            $user->givePermissionTo($moduleAccessPermission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'User Manual module permissions ensured: '.count($permissions).' permission(s), assigned to '
            .$allRoles->count().' role(s) and '.$activeUsers->count().' active user(s).'
        );
    }
}
