<?php

namespace Database\Seeders\Setup;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ensures the customer purchase order permissions exist, grants all of them to admin roles,
 * and grants view access to every role that could already see purchase orders via quotations.
 */
class PurchaseOrderPermissionsSeeder extends Seeder
{
    public const LEGACY_VIEW_PERMISSION = 'laboratory.components.quotation.view';

    /**
     * @var list<string>
     */
    public const PERMISSIONS = [
        CustomerPurchaseOrder::PERMISSION_VIEW,
        CustomerPurchaseOrder::PERMISSION_CREATE,
        CustomerPurchaseOrder::PERMISSION_AMEND,
        CustomerPurchaseOrder::PERMISSION_CLOSE,
        CustomerPurchaseOrder::PERMISSION_CHANGE_AT_RECEPTION,
        CustomerPurchaseOrder::PERMISSION_APPLY_TO_HELD_JOB,
        CustomerPurchaseOrder::PERMISSION_CANCEL_HELD_JOB,
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::PERMISSIONS)
            ->map(fn (string $name): Permission => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]));

        $adminRoles = Role::query()
            ->where('guard_name', 'web')
            ->where(function (Builder $query): void {
                foreach (['admin', 'super admin', 'super-admin', 'system admin', 'system-admin', 'system admin group', 'super admin group'] as $name) {
                    $query->orWhereRaw('LOWER(name) = ?', [$name]);
                }
            })
            ->get();

        foreach ($adminRoles as $adminRole) {
            $adminRole->givePermissionTo($permissions->all());
        }

        $viewPermission = $permissions->firstWhere('name', CustomerPurchaseOrder::PERMISSION_VIEW);

        $legacyViewerRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereHas('permissions', function (Builder $query): void {
                $query->where('name', self::LEGACY_VIEW_PERMISSION);
            })
            ->get();

        foreach ($legacyViewerRoles as $role) {
            $role->givePermissionTo($viewPermission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(sprintf(
            'Purchase order permissions ensured: %d (admin roles: %d, quotation viewers granted view: %d)',
            count(self::PERMISSIONS),
            $adminRoles->count(),
            $legacyViewerRoles->count(),
        ));
    }
}
