<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CRMPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'crm.dashboard.view',
            'crm.customers.view',
            'crm.customers.add',
            'crm.customers.edit',
            'crm.customers.delete',
            'crm.sample-points.view',
            'crm.sample-points.add',
            'crm.sample-points.edit',
            'crm.areas.view',
            'crm.complaints.view',
            'crm.complaints.add',
            'crm.complaints.edit',
            'crm.complaints-approval.edit',
            'crm.complaints-approval.delete',
            'crm.complaints-resolution.add',
            'crm.complaints-resolution.edit',
            'crm.complaint-types.view',
            'crm.complaint-types.add',
            'crm.complaint-types.edit',
            'crm.feedback.view',
            'crm.feedback.add',
            'crm.feedback.edit',
            'crm.resolution-approval.add',
            'crm.resolution-approval.edit',
            'crm.resolution-approval.delete',
            'crm.company-units.add',
            'crm.company-units.edit',
            'crm.certifications.add',
            'crm.certifications.edit',
            'crm.certifications.delete',
            'crm.contacts.view',
            'crm.contacts.add',
            'crm.contacts.edit',
            'crm.products.view',
            'crm.products.add',
            'crm.products.edit',
            'crm.results.view',
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

        $this->command?->info('CRM permissions ensured: ' . count($permissionNames));
    }
}
