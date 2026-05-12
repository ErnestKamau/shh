<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SubmissionFormPermissionsSeeder extends Seeder
{
    /**
     * Permissions used on submission-forms routes.
     * These must exist in the Spatie permissions table before the
     * can:* middleware can pass.
     */
    private array $permissions = [
        'submission-forms.access',  // view/show instances
        'submission-forms.submit',  // create / fill instances
        'submission-forms.process', // update / review / apply to batches
    ];

    /** Admin-equivalent role names that should receive all three permissions. */
    private array $adminRoleNames = [
        'admin',
        'super admin',
        'super-admin',
        'system admin',
        'system-admin',
        'system admin group',
        'super admin group',
        'sample reception',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create (or find) each permission.
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        }

        $permissions = Permission::query()
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->get();

        // Assign all three to every admin-level role that exists.
        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->get()
            ->filter(fn (Role $role) => in_array(
                strtolower(trim((string) $role->name)),
                $this->adminRoleNames,
                true,
            ));

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(
            'Submission-form permissions seeded and assigned to ' . $adminRoles->count() . ' admin role(s).'
        );
    }
}
