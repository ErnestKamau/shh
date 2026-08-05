<?php

namespace Database\Seeders;

use App\InventoryDepartment;
use App\InventoryLocation;
use App\InventoryLocationUser;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UsersSeeder extends Seeder
{
    use ResolvesAmSpecCompany;

    private const DEFAULT_PASSWORD = 'Admin@2026!';

    /** @var list<string> */
    private const MODULE_ACCESS_PERMISSIONS = [
        'laboratory.module.access',
        'inventory.module.access',
        'equipment.module.access',
        'crm.module.access',
        'personnel.module.access',
        'dms.module.access',
        'calendar.module.access',
        'matrix.module.access',
        'ai.module.access',
        'ai_analytics.module.access',
        'risk.module.access',
        'registry.module.access',
        'audit.module.access',
        'tickets.module.access',
        'settings.module.access',
        'system.module-switching.view',
        'system.module-switching.edit',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensureModuleAccessPermissions();

        $adminRole = $this->ensureFullAccessRole();

        $company = $this->resolveAmSpecCompany();
        $adminDeptId = null;
        $adminLocationId = null;

        if ($company !== null) {
            $adminDeptId = InventoryDepartment::query()
                ->where('name', 'SystemAdmin')
                ->where('company_id', $company->id)
                ->value('id');

            $adminLocationId = InventoryLocation::query()
                ->where('name', 'SystemAdmin')
                ->where('company_id', $company->id)
                ->value('id');
        }

        $user = User::query()->updateOrCreate(
            ['email' => AmSpecSeedData::SEED_USER_EMAIL],
            [
                'name' => 'Ernest Kamau',
                'salutation' => 'Mr',
                'first_name' => 'Ernest',
                'last_name' => 'Kamau',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'company_id' => $company?->id,
                'department_id' => $adminDeptId,
                'location_id' => $adminLocationId,
                'active' => 1,
            ]
        );

        if ($adminDeptId !== null) {
            $user->syncDepartmentAssignments([$adminDeptId]);
            $user->save();
        }

        if ($adminLocationId !== null) {
            InventoryLocationUser::query()->firstOrCreate([
                'user_id' => $user->id,
                'inventory_location_id' => $adminLocationId,
            ]);
        }

        $user->syncRoles([$adminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            "User '{$user->email}' ready with role '{$adminRole->name}' "
            ."({$adminRole->permissions()->count()} permissions)."
        );
    }

    private function ensureModuleAccessPermissions(): void
    {
        foreach (self::MODULE_ACCESS_PERMISSIONS as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }

    private function ensureFullAccessRole(): Role
    {
        $adminRole = Role::query()->firstOrCreate(
            [
                'name' => 'admin',
                'guard_name' => 'web',
            ],
            [
                'description' => 'System administrator with full module access',
                'level' => 1,
                'active' => true,
            ]
        );

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->get();

        $adminRole->syncPermissions($permissions);

        $this->command?->info(
            "Role '{$adminRole->name}' synced with {$permissions->count()} permission(s)."
        );

        return $adminRole;
    }
}
