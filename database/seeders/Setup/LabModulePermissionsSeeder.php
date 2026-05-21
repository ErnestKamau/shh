<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class LabModulePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'laboratory.module.access',
            'laboratory.permission',
            'laboratory.admin',
            'laboratory.samples.view',
            'laboratory.components.dashboard.view',
            'laboratory.components.all samples.view',
            'laboratory.components.all samples.add',
            'laboratory.components.all samples.edit',
            'laboratory.components.all samples.delete',
            'laboratory.components.inter-lab-logs.view',
            'laboratory.components.inter-lab-logs.add',
            'laboratory.components.inter-lab-logs.edit',
            'laboratory.components.inter-lab-logs.delete',
            'laboratory.components.proforma invoices.view',
            'laboratory.components.proforma invoices.add',
            'laboratory.components.proforma invoices.edit',
            'laboratory.components.tax regime.view',
            'laboratory.components.tax regime.add',
            'laboratory.components.tax regime.edit',
            'laboratory.components.quotation.view',
            'laboratory.components.quotation.add',
            'laboratory.components.quotation.edit',
            'laboratory.components.quotation.delete',
            'laboratory.components.qc sample.view',
            'laboratory.components.qc sample.add',
            'laboratory.components.qc sample.edit',
            'laboratory.components.analytes.view',
            'laboratory.components.labs.view',
            'laboratory.components.labs.add',
            'laboratory.components.labs.edit',
            'laboratory.components.sample-tracking-stages.view',
            'laboratory.components.sample-tracking-stages.add',
            'laboratory.components.sample-tracking-stages.edit',
            'laboratory.components.sample-tracking-stages.delete',
            'laboratory.components.sample-types.view',
            'laboratory.components.sample-types.add',
            'laboratory.components.sample-types.edit',
            'laboratory.components.sample-types.delete',
            'laboratory.components.rft form.view',
            'laboratory.components.rft form.add',
            'laboratory.components.rft form.edit',
            'laboratory.components.rft form.delete',
            'laboratory.components.method-validation.registration.view',
            'laboratory.components.method-validation.data-review.view',
            'laboratory.components.uncertainty-budget.view',
            'laboratory.components.uncertainty-budget.add',
            'laboratory.components.uncertainty-budget.edit',
            'laboratory.components.uncertainty-budget.delete',
            'laboratory.components.stock-monitoring.view',
            'laboratory.components.stock-monitoring.add',
            'laboratory.components.stock-monitoring.edit',
            'laboratory.components.stock-monitoring.delete',
            'laboratory.components.lab-reports.view',
            'laboratory.components.lab-reports.edit',
            'laboratory.components.reporting-units.view',
            'laboratory.components.reporting-units.add',
            'laboratory.components.reporting-units.edit',
            'laboratory.components.analysis types.view',
            'laboratory.components.analysis types.add',
            'laboratory.components.analysis types.edit',
            'laboratory.components.analysis types.delete',
            'laboratory.components.methods.view',
            'laboratory.components.methods.add',
            'laboratory.components.methods.edit',
            'laboratory.components.methods.delete',
            'laboratory.components.approve for analysis.edit',
            'laboratory.components.generate invoice.view',
            'laboratory.components.draft-invoices.view',
            'laboratory.components.draft-invoices.add',
            'laboratory.components.draft-invoices.delete',
            'laboratory.components.pricelists.view',
            'laboratory.components.customer-focus.view',
            'laboratory.components.customer-focus.edit',
            'laboratory.components.finished sample.edit',
            'laboratory.components.standards.view',
            'laboratory.components.standards.add',
            'laboratory.components.standards.edit',
            'laboratory.components.status.view',
            'laboratory.components.verification-approvals.edit',
            'laboratory.components.verification-approvals.delete',
            'laboratory.components.checklist-approvals.view',
            'laboratory.components.checklist-approvals.add',
            'laboratory.components.checklist-approvals.edit',
            'laboratory.components.checklist-approvals.delete',
            'laboratory.components.sample-approval-checklist.view',
            'laboratory.components.sample-approval-checklist.edit',
            'laboratory.components.equipment-requests.view',
            'laboratory.components.equipment-requests.add',
            'laboratory.components.equipment-requests.approve',
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

        $this->command?->info('Lab module permissions ensured: ' . count($permissionNames));
    }
}
