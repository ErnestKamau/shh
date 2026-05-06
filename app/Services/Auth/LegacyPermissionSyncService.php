<?php

namespace App\Services\Auth;

use App\User;
use App\Models\Auth\Permission;
use App\Models\Auth\Role as SpatieRole;
use Illuminate\Support\Facades\Schema;

/**
 * @deprecated SCHEDULED FOR REMOVAL. This service is deprecated and will be removed in a future release.
 * The application has been migrated to use Spatie/laravel-permission exclusively.
 * Users should be assigned roles via the Spatie role/permission system instead.
 * This service is no longer called during the authentication flow as of the Spatie-only migration.
 * For new role assignments, use the Spatie methods: $user->assignRole() and $user->givePermissionTo()
 *
 * NOTE: Legacy Role and UserRole model classes have been deleted as of May 2026.
 * The legacy roles and user_roles database tables have been dropped.
 */
class LegacyPermissionSyncService
{
    protected string $guardName = 'web';
    /** @var array<int, string> */
    protected array $defaultActions = ['add', 'edit', 'view', 'delete'];

    public function syncAll(): void
    {
        $this->syncModulePermissions();

        $legacyRoles = Role::query()->get();

        foreach ($legacyRoles as $legacyRole) {
            $this->syncRole($legacyRole);
        }

        $this->syncAllUserRoles();
    }

    /**
     * Ensure module/component/action permissions from helper catalog exist in Spatie.
     */
    public function syncModulePermissions(): void
    {
        if (! function_exists('getModulePermissions')) {
            return;
        }

        $modulePermissions = getModulePermissions();

        if (! is_array($modulePermissions)) {
            return;
        }

        $permissionNames = [];

        foreach ($modulePermissions as $moduleName => $moduleConfig) {
            $moduleKey = trim((string) $moduleName);
            if ($moduleKey === '') {
                continue;
            }

            $permissionNames[] = $moduleKey . '.permission';

            $components = $moduleConfig['components'] ?? [];
            if (! is_array($components)) {
                continue;
            }

            foreach ($components as $componentName) {
                $componentKey = trim((string) $componentName);
                if ($componentKey === '') {
                    continue;
                }

                foreach ($this->defaultActions as $action) {
                    $permissionNames[] = $moduleKey . '.components.' . $componentKey . '.' . $action;
                }
            }
        }

        foreach (array_values(array_unique($permissionNames)) as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $this->guardName,
            ]);
        }
    }

    public function syncRole(Role $legacyRole): SpatieRole
    {
        $roleName = trim((string) $legacyRole->name);

        $spatieRole = SpatieRole::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => $this->guardName,
        ]);

        if (Schema::hasTable('spatie_roles')) {
            if (Schema::hasColumn('spatie_roles', 'description')) {
                $spatieRole->description = $legacyRole->description;
            }

            if (Schema::hasColumn('spatie_roles', 'level')) {
                $spatieRole->level = (int) ($legacyRole->level ?? 1);
            }

            if (Schema::hasColumn('spatie_roles', 'company_id')) {
                $spatieRole->company_id = $legacyRole->company_id;
            }

            if (Schema::hasColumn('spatie_roles', 'active')) {
                $spatieRole->active = (int) ($legacyRole->active ?? 1);
            }

            $spatieRole->save();
        }

        $permissionNames = $this->extractPermissionNames($legacyRole->permissions);

        // Expand permissions: if role has module.permission, auto-add all helper-defined components
        $permissionNames = $this->expandModulePermissions($permissionNames);

        foreach ($permissionNames as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $this->guardName,
            ]);
        }

        $spatieRole->syncPermissions($permissionNames);

        return $spatieRole;
    }

    /**
     * Expand module.permission to include all components/actions if the module is enabled.
     * @param array<int, string> $permissionNames
     * @return array<int, string>
     */
    protected function expandModulePermissions(array $permissionNames): array
    {
        if (! function_exists('getModulePermissions')) {
            return $permissionNames;
        }

        $modulePermisions = getModulePermissions();
        if (! is_array($modulePermisions)) {
            return $permissionNames;
        }

        $expanded = $permissionNames;

        foreach ($modulePermisions as $moduleName => $moduleConfig) {
            $moduleKey = trim((string) $moduleName);
            if ($moduleKey === '') {
                continue;
            }

            $modulePermKey = $moduleKey . '.permission';

            // If role has module.permission set, automatically add all defined components/actions
            if (in_array($modulePermKey, $expanded, true)) {
                $components = $moduleConfig['components'] ?? [];
                if (! is_array($components)) {
                    continue;
                }

                foreach ($components as $componentName) {
                    $componentKey = trim((string) $componentName);
                    if ($componentKey === '') {
                        continue;
                    }

                    foreach ($this->defaultActions as $action) {
                        $permKey = $moduleKey . '.components.' . $componentKey . '.' . $action;
                        if (! in_array($permKey, $expanded, true)) {
                            $expanded[] = $permKey;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($expanded));
    }

    public function syncUser(User $user): void
    {
        $this->syncModulePermissions();

        $roleNames = UserRole::query()
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $user->id)
            ->whereNotNull('roles.name')
            ->pluck('roles.name')
            ->map(static fn ($name): string => trim((string) $name))
            ->filter(static fn (string $name): bool => $name !== '')
            ->unique()
            ->values();

        foreach ($roleNames as $roleName) {
            $spatieRole = SpatieRole::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => $this->guardName,
            ]);

            $legacyRole = Role::query()->where('name', $roleName)->first();

            if ($legacyRole) {
                $spatieRole = $this->syncRole($legacyRole);
            }

            if (Schema::hasTable('spatie_roles')) {
                if (Schema::hasColumn('spatie_roles', 'description') && $spatieRole->description === null) {
                    $spatieRole->description = $legacyRole?->description;
                }

                if (Schema::hasColumn('spatie_roles', 'level') && (int) ($spatieRole->level ?? 0) === 0) {
                    $spatieRole->level = (int) ($legacyRole?->level ?? 1);
                }

                if (Schema::hasColumn('spatie_roles', 'company_id') && $spatieRole->company_id === null) {
                    $spatieRole->company_id = $legacyRole?->company_id ?? $user->company_id;
                }

                if (Schema::hasColumn('spatie_roles', 'active') && $spatieRole->active === null) {
                    $spatieRole->active = (int) ($legacyRole?->active ?? 1);
                }

                $spatieRole->save();
            }
        }

        $user->syncRoles($roleNames->all());
    }

    protected function syncAllUserRoles(): void
    {
        $users = User::query()->select('id')->get();

        foreach ($users as $user) {
            $this->syncUser($user);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function extractPermissionNames(?string $legacyPermissions): array
    {
        if ($legacyPermissions === null || trim($legacyPermissions) === '') {
            return [];
        }

        $decoded = json_decode($legacyPermissions, true);

        if (! is_array($decoded)) {
            return [];
        }

        $names = [];
        $this->flattenPermissions($decoded, [], $names);

        return array_values(array_unique($names));
    }

    /**
     * @param array<int|string, mixed> $node
     * @param array<int, string> $segments
     * @param array<int, string> $names
     */
    protected function flattenPermissions(array $node, array $segments, array &$names): void
    {
        foreach ($node as $key => $value) {
            $keySegment = trim((string) $key);
            $currentSegments = $segments;
            $currentSegments[] = $keySegment;

            if (is_array($value)) {
                $this->flattenPermissions($value, $currentSegments, $names);
                continue;
            }

            if ($this->isTruthyPermissionValue($value)) {
                $names[] = implode('.', $currentSegments);
            }
        }
    }

    /**
     * @param mixed $value
     */
    protected function isTruthyPermissionValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return strtolower(trim((string) $value)) === 'true';
    }
}
