<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ensures inventory workflow roles + component permissions used by RFQ / PO / Goods Receipt UI.
 *
 * Button visibility on requisition show also checks Spatie role names via hasAnyRole():
 * - Procurement → Inventory Procurement Group | Procurement | Admin
 * - Store Manager → Inventory Store Manager Group | Store Manager | Store | Admin
 * - Finance → Inventory Finance Group | Financial Accountant | Finance | Admin
 */
class InventoryWorkflowRolesPermissionsSeeder extends Seeder
{
    /** @var list<string> */
    private const ACTIONS = ['view', 'add', 'edit', 'delete', 'approve', 'decline'];

    /**
     * Component permission stems (inventory.components.{component}.{action}).
     *
     * @var list<string>
     */
    private const COMPONENTS = [
        'purchase request',
        'request for quotation',
        'purchase orders',
        'goods receipt',
        'goods return',
        'request to store',
        'material issuance',
        'suppliers',
        'store',
        'stock-taking',
        'stock-transfer',
        'categories',
        'departments',
        'general requisition',
        'inventory-movement',
        'approval-requests',
        'configuration',
        'stage',
    ];

    /**
     * Roles used by the inventory requisition UI (group names + legacy aliases).
     *
     * @var array<string, array{description: string, level: int}>
     */
    private const ROLES = [
        'Inventory Procurement Group' => [
            'description' => 'Procurement workflow: RFQ, award quotes, create/send purchase orders',
            'level' => 20,
        ],
        'Procurement' => [
            'description' => 'Legacy alias for Inventory Procurement Group',
            'level' => 20,
        ],
        'Inventory Store Manager Group' => [
            'description' => 'Store workflow: goods receipt, returns, material issuance',
            'level' => 20,
        ],
        'Store Manager' => [
            'description' => 'Legacy alias for Inventory Store Manager Group',
            'level' => 20,
        ],
        'Store' => [
            'description' => 'Store operator (goods receipt / issuance)',
            'level' => 15,
        ],
        'Inventory Finance Group' => [
            'description' => 'Finance approval on goods receipt / purchase orders',
            'level' => 20,
        ],
        'Financial Accountant' => [
            'description' => 'Legacy alias for Inventory Finance Group',
            'level' => 20,
        ],
        'Finance' => [
            'description' => 'Legacy alias for Inventory Finance Group',
            'level' => 20,
        ],
        'Inventory Department Head Group' => [
            'description' => 'Department head approvals for purchase / store requests',
            'level' => 25,
        ],
        'Department Head' => [
            'description' => 'Legacy alias for Inventory Department Head Group',
            'level' => 25,
        ],
        'Inventory Manager Group' => [
            'description' => 'Inventory manager oversight',
            'level' => 30,
        ],
        'Manager' => [
            'description' => 'Legacy alias for Inventory Manager Group',
            'level' => 30,
        ],
        'Inventory Assistant Supervisor Group' => [
            'description' => 'Inventory assistant / supervisor',
            'level' => 15,
        ],
        'Admin' => [
            'description' => 'Administrator (inventory workflow button gates)',
            'level' => 100,
        ],
        'admin' => [
            'description' => 'Administrator alias (lowercase)',
            'level' => 100,
        ],
    ];

    /**
     * Role → component action map. Empty list for Admin means all inventory permissions.
     *
     * @var array<string, array<string, list<string>>>
     */
    private const ROLE_BUNDLES = [
        'Inventory Procurement Group' => [
            'request for quotation' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'purchase orders' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'purchase request' => ['view', 'edit'],
            'goods receipt' => ['view'],
            'goods return' => ['view', 'approve', 'decline'],
            'suppliers' => ['view', 'add', 'edit', 'delete'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'edit', 'delete', 'approve', 'decline'],
        ],
        'Procurement' => [
            'request for quotation' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'purchase orders' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'purchase request' => ['view', 'edit'],
            'goods receipt' => ['view'],
            'goods return' => ['view', 'approve', 'decline'],
            'suppliers' => ['view', 'add', 'edit', 'delete'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'edit', 'delete', 'approve', 'decline'],
        ],
        'Inventory Store Manager Group' => [
            'store' => ['view', 'add', 'edit', 'delete'],
            'goods receipt' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'goods return' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'material issuance' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'stock-transfer' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'stock-taking' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'purchase orders' => ['view'],
            'purchase request' => ['view', 'approve', 'decline'],
            'request to store' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'edit', 'delete', 'approve', 'decline'],
        ],
        'Store Manager' => [
            'store' => ['view', 'add', 'edit', 'delete'],
            'goods receipt' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'goods return' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'material issuance' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'stock-transfer' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'stock-taking' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'purchase orders' => ['view'],
            'purchase request' => ['view', 'approve', 'decline'],
            'request to store' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'edit', 'delete', 'approve', 'decline'],
        ],
        'Store' => [
            'store' => ['view', 'add', 'edit', 'delete'],
            'goods receipt' => ['view', 'add', 'edit', 'delete'],
            'goods return' => ['view', 'add', 'edit', 'delete'],
            'material issuance' => ['view', 'add', 'edit', 'delete', 'approve', 'decline'],
            'stock-transfer' => ['view', 'add', 'edit'],
            'stock-taking' => ['view', 'edit'],
            'purchase orders' => ['view'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'edit', 'approve', 'decline'],
        ],
        'Inventory Finance Group' => [
            'purchase orders' => ['view', 'approve', 'decline'],
            'goods receipt' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Financial Accountant' => [
            'purchase orders' => ['view', 'approve', 'decline'],
            'goods receipt' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Finance' => [
            'purchase orders' => ['view', 'approve', 'decline'],
            'goods receipt' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Inventory Department Head Group' => [
            'purchase request' => ['view', 'approve', 'decline'],
            'request to store' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Department Head' => [
            'purchase request' => ['view', 'approve', 'decline'],
            'request to store' => ['view', 'approve', 'decline'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Inventory Manager Group' => [
            'purchase request' => ['view', 'approve', 'decline'],
            'request to store' => ['view', 'approve', 'decline'],
            'purchase orders' => ['view'],
            'goods receipt' => ['view'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'categories' => ['view'],
            'departments' => ['view'],
            'suppliers' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Manager' => [
            'purchase request' => ['view', 'approve', 'decline'],
            'request to store' => ['view', 'approve', 'decline'],
            'purchase orders' => ['view'],
            'goods receipt' => ['view'],
            'approval-requests' => ['view', 'approve', 'decline'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'approve', 'decline'],
        ],
        'Inventory Assistant Supervisor Group' => [
            'purchase request' => ['view', 'add', 'edit'],
            'request to store' => ['view', 'add', 'edit'],
            'store' => ['view'],
            'inventory-movement' => ['view'],
            'stage' => ['view', 'edit'],
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionModels = $this->ensurePermissions();
        $this->ensureRoles();
        $this->assignBundles($permissionModels);
        $this->ensureModuleAccess();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Inventory workflow roles + permissions ensured.');
    }

    /**
     * @return list<Permission>
     */
    private function ensurePermissions(): array
    {
        $names = [
            'inventory.module.access',
            'inventory.permission',
            'access inventory',
        ];

        foreach (self::COMPONENTS as $component) {
            foreach (self::ACTIONS as $action) {
                $names[] = "inventory.components.{$component}.{$action}";
            }
        }

        $models = [];
        foreach (array_values(array_unique($names)) as $permissionName) {
            $models[] = Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $this->command?->info('Inventory permissions ensured: '.count($models));

        return $models;
    }

    private function ensureRoles(): void
    {
        foreach (self::ROLES as $roleName => $meta) {
            Role::query()->firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                [
                    'description' => $meta['description'],
                    'level' => $meta['level'],
                    'active' => true,
                ]
            );
        }

        $this->command?->info('Inventory workflow roles ensured: '.count(self::ROLES));
    }

    /**
     * @param  list<Permission>  $allInventoryPermissions
     */
    private function assignBundles(array $allInventoryPermissions): void
    {
        foreach (self::ROLE_BUNDLES as $roleName => $bundle) {
            $role = $this->findRole($roleName);
            if ($role === null) {
                continue;
            }

            $permissionNames = $this->expandBundle($bundle);
            $permissionNames[] = 'inventory.module.access';
            $permissionNames[] = 'inventory.permission';
            $permissionNames[] = 'access inventory';

            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $permissionNames)
                ->get();

            $role->givePermissionTo($permissions);
            $this->command?->info("Bundled {$roleName}: {$permissions->count()} permissions");
        }

        foreach (['Admin', 'admin'] as $adminName) {
            $admin = Role::query()
                ->where('guard_name', 'web')
                ->where('name', $adminName)
                ->first();
            if ($admin === null) {
                continue;
            }

            $admin->givePermissionTo($allInventoryPermissions);
            $this->command?->info("Admin role '{$admin->name}' granted all inventory permissions");
        }
    }

    private function ensureModuleAccess(): void
    {
        $access = Permission::query()->firstOrCreate([
            'name' => 'access inventory',
            'guard_name' => 'web',
        ]);

        foreach (array_keys(self::ROLES) as $roleName) {
            $role = $this->findRole($roleName);
            if ($role === null) {
                continue;
            }

            $role->givePermissionTo($access);
        }
    }

    /**
     * @param  array<string, list<string>>  $bundle
     * @return list<string>
     */
    private function expandBundle(array $bundle): array
    {
        $names = [];
        foreach ($bundle as $component => $actions) {
            foreach ($actions as $action) {
                $names[] = "inventory.components.{$component}.{$action}";
            }
        }

        return $names;
    }

    private function findRole(string $roleName): ?Role
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($roleName)])
            ->first();
    }
}
