<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DmsModulePermissionsSeeder extends Seeder
{
    /**
     * Permissions required by DMS routes (dms/*) and document policies.
     *
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return [
            'documents.permission',

            'documents.components.document management.view',
            'documents.components.document management.add',
            'documents.components.document management.edit',
            'documents.components.document management.delete',

            'documents.components.document types.view',
            'documents.components.document types.add',
            'documents.components.document types.edit',
            'documents.components.document types.delete',

            'documents.components.document publishing.view',
            'documents.components.document publishing.add',
            'documents.components.document publishing.edit',

            'documents.components.reports.view',

            'documents.components.notification frequencies.view',
            'documents.components.notification frequencies.add',
            'documents.components.notification frequencies.edit',
            'documents.components.notification frequencies.delete',
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

        $moduleAccessRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereHas('permissions', function ($query): void {
                $query->where('name', 'dms.module.access');
            })
            ->get();

        $componentPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::permissionNames())
            ->get();

        foreach ($moduleAccessRoles as $role) {
            $role->givePermissionTo($componentPermissions);
        }

        $usersWithModuleAccess = User::query()
            ->where('active', 1)
            ->permission('dms.module.access')
            ->get();

        foreach ($usersWithModuleAccess as $user) {
            $user->givePermissionTo($componentPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'DMS module permissions ensured: '.count($permissions).' permission(s), '
            .'assigned to '.$adminRoles->count().' admin role(s), '
            .$moduleAccessRoles->count().' role(s) with dms.module.access, and '
            .$usersWithModuleAccess->count().' user(s) with direct module access.'
        );
    }
}
