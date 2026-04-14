<?php

namespace App\Services\Auth;

use App\Role;
use App\User;
use App\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class LegacyPermissionSyncService
{
    protected string $guardName = 'web';
    /** @var array<int, string> */
    protected array $defaultActions = ['Add', 'Edit', 'View', 'Delete'];

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

        $permissionNames = $this->extractPermissionNames($legacyRole->permissions);

        foreach ($permissionNames as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $this->guardName,
            ]);
        }

        $spatieRole->syncPermissions($permissionNames);

        return $spatieRole;
    }

    public function syncUser(User $user): void
    {
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
            SpatieRole::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => $this->guardName,
            ]);
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
