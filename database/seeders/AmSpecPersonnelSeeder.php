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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AmSpecPersonnelSeeder extends Seeder
{
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = $this->resolveAmSpecCompany();
        if ($company === null) {
            $this->command?->error('No AmSpec company found. Run SystemSetupSeeder / company seeders first.');

            return;
        }

        $locationId = $this->resolveLocationId($company->id);
        $adminRole = $this->ensureAdminRole();
        $departmentIdsByName = $this->organizationalDepartments($company->id);
        $allOrgDepartmentIds = array_values($departmentIdsByName);

        if ($allOrgDepartmentIds === []) {
            $this->command?->warn('No organizational departments found; users will be seeded without department assignments.');
        }

        $roleCache = [];

        foreach (AmSpecSeedData::limsSheetPersonnel() as $person) {
            $email = strtolower(trim((string) $person['email']));
            $roleName = trim((string) $person['role']);
            $accessProfile = (string) $person['access_profile'];
            $fullAccess = (bool) $person['full_access'];

            $role = $roleCache[$roleName] ?? $this->ensureRoleWithAccess($roleName, $accessProfile);
            $roleCache[$roleName] = $role;

            $departmentIds = $this->resolveDepartmentIds(
                (string) $person['department'],
                $departmentIdsByName,
                $allOrgDepartmentIds
            );

            $firstName = trim((string) $person['first_name']);
            $lastName = trim((string) $person['last_name']);
            $salutation = isset($person['salutation']) ? trim((string) $person['salutation']) : null;
            $displayName = trim(($salutation ? $salutation.'. ' : '').$firstName.' '.$lastName);

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $displayName,
                    'salutation' => $salutation !== '' ? $salutation : null,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => Hash::make(AmSpecSeedData::LIMS_SHEET_PASSWORD),
                    'password_changed_at' => null,
                    'company_id' => $company->id,
                    'location_id' => $locationId,
                    'position' => (string) $role->id,
                    'active' => 1,
                    'is_client' => false,
                ]
            );

            // Personnel list excludes users linked as CRM/portal contacts.
            $user->forceFill([
                'is_client' => false,
                'is_tablet' => false,
                'crm_contact_id' => null,
                'crmcontact_id' => null,
            ]);

            $user->syncDepartmentAssignments($departmentIds);
            $user->save();

            if ($locationId !== null) {
                InventoryLocationUser::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'inventory_location_id' => $locationId,
                ]);
            }

            $rolesToAssign = [$role->name];
            if ($fullAccess) {
                $rolesToAssign[] = $adminRole->name;
            }

            $user->syncRoles($rolesToAssign);

            $this->command?->info(sprintf(
                'Seeded %s → role "%s"%s, %d department(s), %d permission(s) on role.',
                $email,
                $role->name,
                $fullAccess ? ' + admin' : '',
                count($departmentIds),
                $role->permissions()->count()
            ));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('AmSpec LIMS sheet personnel seeded. Temp password: '.AmSpecSeedData::LIMS_SHEET_PASSWORD.' (no invite email sent).');
    }

    private function ensureAdminRole(): Role
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

        if ($adminRole->permissions()->count() === 0) {
            $adminRole->syncPermissions(
                Permission::query()->where('guard_name', 'web')->get()
            );
        }

        return $adminRole;
    }

    private function ensureRoleWithAccess(string $roleName, string $accessProfile): Role
    {
        $role = $this->findOrCreateRole($roleName);
        $permissions = $this->permissionsForProfile($accessProfile);

        if ($permissions->isEmpty()) {
            $this->command?->warn("No permissions resolved for access profile '{$accessProfile}' on role '{$roleName}'.");
        } else {
            $role->syncPermissions($permissions);
        }

        return $role->fresh(['permissions']) ?? $role;
    }

    private function findOrCreateRole(string $roleName): Role
    {
        $existing = Role::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($roleName))])
            ->first();

        if ($existing !== null) {
            if ((string) $existing->name !== $roleName) {
                $existing->name = $roleName;
                $existing->description = $roleName;
                $existing->active = true;
                $existing->save();
            }

            return $existing;
        }

        return Role::query()->create([
            'name' => $roleName,
            'guard_name' => 'web',
            'description' => $roleName,
            'level' => 1,
            'active' => true,
        ]);
    }

    /**
     * @return Collection<int, Permission>
     */
    private function permissionsForProfile(string $accessProfile): Collection
    {
        return match ($accessProfile) {
            'full_access' => Permission::query()->where('guard_name', 'web')->get(),
            'customer_service' => $this->permissionsMatching([
                'laboratory.module.access',
                'laboratory.permission',
                'crm.module.access',
                'crm.permission',
                'inventory.module.access',
                'inventory.permission',
                'laboratory.components.dashboard.view',
                'laboratory.components.all samples.*',
                'laboratory.components.samples receiving.*',
                'laboratory.components.quotation.*',
                'laboratory.components.proforma invoices.*',
                'laboratory.components.generate invoice.*',
                'laboratory.components.draft-invoices.*',
                'laboratory.components.sales-orders.*',
                'laboratory.components.pricelists.*',
                'laboratory.components.tax regime.*',
                'laboratory.components.lab-reports.view',
                'crm.components.customer-list.*',
                'crm.components.contacts.*',
                'crm.components.results.view',
                'crm.components.certificates.view',
                'crm.components.details.view',
                'crm.components.details.edit',
                'inventory.components.suppliers.*',
                'inventory.components.store.*',
                'inventory.components.stock-taking.*',
                'inventory.components.stock-transfer.*',
                'inventory.components.categories.*',
                'inventory.components.departments.*',
                'inventory.components.purchase request.*',
                'inventory.components.purchase orders.*',
                'inventory.components.request for quotation.*',
                'inventory.components.request to store.*',
                'inventory.components.goods receipt.*',
                'inventory.components.general requisition.*',
                'inventory.components.inventory-movement.*',
            ]),
            'senior_chemist' => $this->permissionsMatching([
                'laboratory.module.access',
                'laboratory.permission',
                'laboratory.components.dashboard.view',
                'laboratory.components.all samples.*',
                'laboratory.components.samples receiving.*',
                'laboratory.components.samples in lab.*',
                'laboratory.components.sample verification.*',
                'laboratory.components.sample approval.*',
                'laboratory.components.completed sample.*',
                'laboratory.components.reports for collection.*',
                'laboratory.components.reports in payment.view',
                'laboratory.components.methods.*',
                'laboratory.components.reporting-units.*',
                'laboratory.components.standards.*',
                'laboratory.components.uncertainty-budget.*',
                'laboratory.components.analytes.*',
                'laboratory.components.analysis types.*',
                'laboratory.components.lab-reports.*',
                'laboratory.components.qc sample.*',
                'laboratory.components.verification-approvals.*',
                'crm.module.access',
                'crm.components.results.view',
                'crm.components.certificates.view',
            ]),
            'laboratory_assistant' => $this->permissionsMatching([
                'laboratory.module.access',
                'laboratory.permission',
                'laboratory.components.dashboard.view',
                'laboratory.components.all samples.view',
                'laboratory.components.samples receiving.*',
                'inventory.module.access',
                'inventory.permission',
                'inventory.components.store.*',
                'inventory.components.stock-taking.*',
                'inventory.components.inventory-movement.*',
                'inventory.components.request to store.*',
                'inventory.components.material issuance.*',
            ]),
            'junior_microbiologist' => $this->permissionsMatching([
                'laboratory.module.access',
                'laboratory.permission',
                'laboratory.components.dashboard.view',
                'laboratory.components.all samples.view',
                'laboratory.components.all samples.edit',
                'laboratory.components.samples receiving.*',
                'laboratory.components.samples in lab.*',
                'laboratory.components.methods.view',
                'laboratory.components.methods.edit',
                'laboratory.components.reporting-units.view',
                'laboratory.components.analytes.view',
            ]),
            default => collect(),
        };
    }

    /**
     * @param  list<string>  $patterns  Exact permission names, or prefixes ending with ".*"
     * @return Collection<int, Permission>
     */
    private function permissionsMatching(array $patterns): Collection
    {
        $exact = [];
        $prefixes = [];

        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if ($pattern === '') {
                continue;
            }

            if (str_ends_with($pattern, '.*')) {
                $prefixes[] = substr($pattern, 0, -1);
            } else {
                $exact[] = $pattern;
            }
        }

        $query = Permission::query()->where('guard_name', 'web');

        $query->where(function ($builder) use ($exact, $prefixes): void {
            if ($exact !== []) {
                $builder->orWhereIn('name', $exact);
            }

            foreach ($prefixes as $prefix) {
                $builder->orWhere('name', 'like', $prefix.'%');
            }
        });

        return $query->get();
    }

    /**
     * @param  array<string, string>  $departmentIdsByName
     * @param  list<string>  $allOrgDepartmentIds
     * @return list<string>
     */
    private function resolveDepartmentIds(
        string $departmentKey,
        array $departmentIdsByName,
        array $allOrgDepartmentIds
    ): array {
        $key = strtolower(trim($departmentKey));

        if ($key === 'all' || $key === 'over all' || $key === 'overall') {
            return $allOrgDepartmentIds;
        }

        foreach ($departmentIdsByName as $name => $id) {
            if (strtolower(trim($name)) === $key) {
                return [$id];
            }
        }

        $this->command?->warn("Department '{$departmentKey}' not found; leaving unassigned.");

        return [];
    }

    /**
     * @return array<string, string> name => id
     */
    private function organizationalDepartments(string $companyId): array
    {
        $query = InventoryDepartment::query()
            ->where('module', 'organizational')
            ->where('active', 1);

        $forCompany = (clone $query)->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        if ($forCompany->isEmpty()) {
            $forCompany = $query->orderBy('name')->get(['id', 'name']);
        }

        $map = [];
        foreach ($forCompany as $department) {
            $map[(string) $department->name] = (string) $department->id;
        }

        return $map;
    }

    private function resolveLocationId(string $companyId): ?string
    {
        $preferred = InventoryLocation::query()
            ->where('id', AmSpecSeedData::DUBAI_HQ_LOCATION_ID)
            ->value('id');

        if ($preferred !== null) {
            return (string) $preferred;
        }

        $byName = InventoryLocation::query()
            ->where('company_id', $companyId)
            ->where(function ($query): void {
                $query->where('name', 'like', '%Dubai HQ%')
                    ->orWhere('name', 'like', '%AmSpec Dubai%');
            })
            ->orderBy('name')
            ->value('id');

        if ($byName !== null) {
            return (string) $byName;
        }

        $any = InventoryLocation::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->value('id');

        return $any !== null ? (string) $any : null;
    }
}
