<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AuditModulePermissionsSeeder extends Seeder
{
    /**
     * Permissions required by audit module routes (audit/*).
     *
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return [
            'audit.module.access',

            'audit.components.dashboard.view',

            'audit.components.audits.view',
            'audit.components.audits.add',
            'audit.components.audits.edit',
            'audit.components.audits.delete',

            'audit.components.non-conformances.view',
            'audit.components.non-conformances.add',
            'audit.components.non-conformances.edit',
            'audit.components.non-conformances.delete',

            'audit.components.corrective actions.view',
            'audit.components.corrective actions.add',
            'audit.components.corrective actions.edit',
            'audit.components.corrective actions.delete',

            'audit.components.reports.view',

            'audit.components.configuration.view',
            'audit.components.configuration.add',
            'audit.components.configuration.edit',
            'audit.components.configuration.delete',
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'Audit module permissions ensured: '.count($permissions).' permission(s), assigned to '.$adminRoles->count().' admin role(s).'
        );
    }
}
