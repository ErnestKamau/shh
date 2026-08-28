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
            $adminRole->givePermissionTo($moduleAccessPermission);
        }

        $labModuleAccessRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereHas('permissions', function ($query): void {
                $query->where('name', 'laboratory.module.access');
            })
            ->get();

        foreach ($labModuleAccessRoles as $role) {
            $role->givePermissionTo($moduleAccessPermission);
        }

        $usersWithLabModuleAccess = User::query()
            ->where('active', 1)
            ->permission('laboratory.module.access')
            ->get();

        foreach ($usersWithLabModuleAccess as $user) {
            $user->givePermissionTo($moduleAccessPermission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'User Manual module permissions ensured: '.count($permissions).' permission(s), assigned to '
            .$adminRoles->count().' admin role(s), '
            .$labModuleAccessRoles->count().' role(s) with laboratory.module.access, and '
            .$usersWithLabModuleAccess->count().' user(s) with direct laboratory module access.'
        );
    }
}
