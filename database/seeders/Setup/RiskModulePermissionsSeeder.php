<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RiskModulePermissionsSeeder extends Seeder
{
    /**
     * Permissions required by risk module routes (risk/*).
     *
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return [
            'risk-management.components.risks.view',
            'risk-management.components.risks.add',
            'risk-management.components.risks.edit',
            'risk-management.components.risks.delete',
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

        // Anyone who can open the Risk module should at least browse workflow stages.
        $moduleAccessRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereHas('permissions', function ($query): void {
                $query->where('name', 'risk.module.access');
            })
            ->get();

        $workflowPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::permissionNames())
            ->get();

        foreach ($moduleAccessRoles as $role) {
            $role->givePermissionTo($workflowPermissions);
        }

        $usersWithModuleAccess = User::query()
            ->where('active', 1)
            ->permission('risk.module.access')
            ->get();

        foreach ($usersWithModuleAccess as $user) {
            $user->givePermissionTo($workflowPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'Risk module permissions ensured: '.count($permissions).' permission(s), '
            .'assigned to '.$adminRoles->count().' admin role(s), '
            .$moduleAccessRoles->count().' role(s) with risk.module.access, and '
            .$usersWithModuleAccess->count().' user(s) with direct module access.'
        );
    }
}
