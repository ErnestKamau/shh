<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SkillsMatrixPermissionsSeeder extends Seeder
{
    /**
     * All Skills Matrix permissions used by routes, controllers, and Livewire UI.
     *
     * @return array<int, string>
     */
    public static function permissionNames(): array
    {
        return [
            'matrix.module.access',
            'skills-matrix.components.dashboard.view',
            'skills-matrix.components.skills-matrix.view',
            'skills-matrix.components.skills-matrix.add',
            'skills-matrix.components.skills-matrix.edit',
            'skills-matrix.components.skills-matrix.delete',
            'skills-matrix.components.capability.view',
            'skills-matrix.components.capability.add',
            'skills-matrix.components.capability.edit',
            'skills-matrix.components.capability.delete',
            'skills-matrix.components.training-needs.view',
            'skills-matrix.components.training-needs.add',
            'skills-matrix.components.training-needs.edit',
            'skills-matrix.components.training-needs.delete',
            'skills-matrix.components.training-plan.view',
            'skills-matrix.components.training-plan.add',
            'skills-matrix.components.training-plan.edit',
            'skills-matrix.components.training-plan.delete',
            'skills-matrix.components.training-plan.attend.confirm',
            'skills-matrix.components.training-plan.attend.manage',
            'skills-matrix.components.training-plan.materials.manage',
            'skills-matrix.components.training-plan.evaluation.submit',
            'skills-matrix.components.training-plan.evaluation.approve',
            'skills-matrix.components.module-preconfigs.view',
            'skills-matrix.components.module-preconfigs.add',
            'skills-matrix.components.module-preconfigs.edit',
            'skills-matrix.components.module-preconfigs.delete',
            'skills-matrix.components.matrix-configuration.view',
            'skills-matrix.components.matrix-configuration.add',
            'skills-matrix.components.matrix-configuration.edit',
            'skills-matrix.components.matrix-configuration.delete',
            'skills-matrix.components.other-training.view',
            'skills-matrix.components.other-training.add',
            'skills-matrix.components.other-training.edit',
            'skills-matrix.permission',
            'access skills matrix',
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];
        foreach (self::permissionNames() as $name) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        Role::query()->firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['description' => 'System administrator group role', 'level' => 1, 'active' => true]
        );

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

        if ($adminRoles->isEmpty()) {
            $this->command?->warn('No admin-group Spatie roles found — permissions created but not assigned.');
        }

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Skills Matrix permissions ensured: '.count($permissions).' permissions, assigned to '.$adminRoles->count().' admin role(s).');
    }
}
