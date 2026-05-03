<?php

namespace App\Livewire\Personnel;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoleManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showRoleModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingRoleId = null;
    public string $roleName = '';
    public string $roleDescription = '';
    public int $roleLevel = 1;
    public bool $roleActive = true;
    public string $message = '';
    public string $messageType = 'success';
    /** @var array<int, string> */
    public array $expandedRoleRows = [];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function exportRoles()
    {
        $rows = $this->buildRolesQuery()
            ->orderBy('name')
            ->get(['name', 'description', 'level', 'active'])
            ->map(function ($role): array {
                return [
                    (string) $role->name,
                    (string) ($role->description ?? ''),
                    (string) $role->level,
                    ((int) $role->active === 1) ? 'Yes' : 'No',
                ];
            })
            ->values()
            ->all();

        return Excel::download(new class($rows) implements FromArray, WithHeadings {
            /** @var array<int, array<int, string>> */
            private array $rows;

            /**
             * @param array<int, array<int, string>> $rows
             */
            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            /**
             * @return array<int, array<int, string>>
             */
            public function array(): array
            {
                return $this->rows;
            }

            /**
             * @return array<int, string>
             */
            public function headings(): array
            {
                return ['Name', 'Description', 'Level', 'Active'];
            }
        }, 'organizational_roles_' . now()->format('Y_m_d_His') . '.xlsx');
    }

    public function openCreateModal(): void
    {
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleLevel = 1;
        $this->roleActive = true;
        $this->showRoleModal = true;
    }

    public function openEditModal(string $roleId): void
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($roleId);
        $this->editingRoleId = (string) $role->id;
        $this->roleName = (string) $role->name;
        $this->roleDescription = (string) $role->description;
        $this->roleLevel = (int) $role->level;
        $this->roleActive = (int) $role->active === 1;
        $this->showRoleModal = true;
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:255',
            'roleDescription' => 'required|string|max:1000',
            'roleLevel' => 'required|integer|min:1',
            'roleActive' => 'boolean',
        ]);

        if ($this->editingRoleId) {
            $role = Role::query()->where('guard_name', 'web')->findOrFail($this->editingRoleId);
        } else {
            $role = new Role();
            $role->guard_name = 'web';
        }

        $role->name = $this->roleName;
        $role->description = $this->roleDescription;
        $role->level = $this->roleLevel;
        $role->active = $this->roleActive ? 1 : 0;
        $role->save();

        $this->showRoleModal = false;
        $this->message = $this->editingRoleId ? 'Role updated successfully.' : 'Role added successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function openDeleteModal(string $roleId): void
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($roleId);
        $this->editingRoleId = (string) $role->id;
        $this->roleName = (string) $role->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteRole(): void
    {
        $this->validate([
            'editingRoleId' => 'required|string',
        ]);

        $role = Role::query()->where('guard_name', 'web')->findOrFail($this->editingRoleId);
        $role->delete();
        $this->showDeleteModal = false;
        $this->message = 'Role deleted successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function toggleRoleRow(string $roleId): void
    {
        $key = $roleId;
        if (in_array($key, $this->expandedRoleRows, true)) {
            $this->expandedRoleRows = array_values(
                array_filter($this->expandedRoleRows, fn (string $k): bool => $k !== $key)
            );
        } else {
            $this->expandedRoleRows[] = $key;
        }
    }

    public function togglePermission(string $roleId, string $permissionName): void
    {
        $permission = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', $permissionName)
            ->first();

        if (!$permission) {
            return;
        }

        $role = Role::query()->where('guard_name', 'web')->findOrFail($roleId);

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
        } else {
            $role->givePermissionTo($permissionName);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function toggleModulePermissions(string $roleId, string $moduleKey): void
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions:id,name')
            ->findOrFail($roleId);

        $moduleData = $this->allPermissionsGroupedByModule->firstWhere('module_key', $moduleKey);

        if (!$moduleData) {
            return;
        }

        $allModulePerms = collect($moduleData['resources'])
            ->flatMap(fn (array $r): array => array_values($r['permission_names']))
            ->all();

        if (empty($allModulePerms)) {
            return;
        }

        $currentPerms = $role->permissions->pluck('name')->all();
        $allAssigned  = count(array_intersect($allModulePerms, $currentPerms)) === count($allModulePerms);

        if ($allAssigned) {
            $role->revokePermissionTo($allModulePerms);
        } else {
            $role->givePermissionTo($allModulePerms);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function getRolePermissionNames(string $roleId): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->with('permissions:id,name')
            ->findOrFail($roleId)
            ->permissions
            ->pluck('name')
            ->all();
    }

    public function getAllPermissionsGroupedByModuleProperty(): Collection
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);

        $modules = [];

        foreach ($permissions as $permission) {
            $permissionName = (string) $permission->name;
            $parsed         = $this->parsePermissionName($permissionName);
            $moduleKey      = $parsed['module_key'];
            $resourceKey    = $parsed['resource_key'];
            $actionKey      = $parsed['action_key'];

            if (!isset($modules[$moduleKey])) {
                $modules[$moduleKey] = [
                    'module_key'   => $moduleKey,
                    'module_label' => $parsed['module_label'],
                    'resources'    => [],
                ];
            }

            if (!isset($modules[$moduleKey]['resources'][$resourceKey])) {
                $modules[$moduleKey]['resources'][$resourceKey] = [
                    'resource_key'     => $resourceKey,
                    'resource_label'   => $parsed['resource_label'],
                    'permission_names' => [],
                ];
            }

            $modules[$moduleKey]['resources'][$resourceKey]['permission_names'][$actionKey] = $permissionName;
        }

        return collect($modules)
            ->sortBy(fn (array $m): string => sprintf('%03d_%s', $this->permissionModulePriority($m['module_key']), strtolower($m['module_label'])))
            ->map(function (array $module): array {
                $module['resources'] = collect($module['resources'])
                    ->sortBy('resource_label', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all();

                return $module;
            })
            ->values();
    }

    public function getRolesProperty()
    {
        return $this->buildRolesQuery()
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    private function buildRolesQuery()
    {
        $query = Role::query()
            ->where('guard_name', 'web');

        if ($this->search !== '') {
            $query->where(function ($builder): void {
                $searchText = '%' . $this->search . '%';
                $builder->where('name', 'like', $searchText)
                    ->orWhere('description', 'like', $searchText)
                    ->orWhere('level', 'like', $searchText);
            });
        }

        return $query;
    }

    public function render()
    {
        return view('livewire.personnel.role-manager');
    }

    // ── Permission parsing helpers (mirrors PersonnelDetailManager) ──────────

    private function parsePermissionName(string $permissionName): array
    {
        $normalizedName = strtolower(trim($permissionName));
        $knownActions   = ['access', 'view', 'index', 'list', 'read', 'create', 'store', 'edit', 'update', 'delete', 'destroy', 'approve', 'publish', 'import', 'export', 'print', 'download', 'upload', 'manage'];
        $knownModules   = $this->knownPermissionModules();
        $actionAliases  = ['add' => 'create', 'bulk import' => 'import'];

        foreach ($actionAliases as $from => $to) {
            if (str_ends_with($normalizedName, '.' . $from)) {
                $normalizedName = substr($normalizedName, 0, -strlen($from)) . $to;
            }
        }

        $parts        = array_values(array_filter(explode('.', $normalizedName), fn (string $p): bool => $p !== ''));
        $modulePart   = $this->resolvePermissionModuleKey($normalizedName, $parts);
        $resourcePart = 'general';
        $actionPart   = 'access';

        if (str_contains($normalizedName, '.')) {
            $moduleIndex = array_search($modulePart, $parts, true);

            if ($modulePart === 'module' && isset($parts[1]) && !in_array($parts[1], $knownActions, true)) {
                $modulePart   = $parts[1];
                $resourcePart = 'module';
                $actionPart   = 'access';
            } else {
                if (!is_int($moduleIndex)) {
                    $moduleIndex = 0;
                }
                $segmentsAfterModule = array_slice($parts, $moduleIndex + 1);
                if (!empty($segmentsAfterModule)) {
                    $lastSegment = $segmentsAfterModule[count($segmentsAfterModule) - 1];
                    if (in_array($lastSegment, $knownActions, true)) {
                        $actionPart       = $lastSegment;
                        $resourceSegments = array_slice($segmentsAfterModule, 0, -1);
                        $resourcePart     = implode('_', $resourceSegments ?: ['general']);
                    } else {
                        $resourcePart = implode('_', $segmentsAfterModule);
                    }
                }
            }
        } else {
            $tokens = preg_split('/\s+/', $normalizedName) ?: [];
            $first  = $tokens[0] ?? '';
            $last   = $tokens[count($tokens) - 1] ?? '';

            if (in_array($first, $knownActions, true) && isset($tokens[1])) {
                $actionPart   = $first;
                $resourcePart = implode('_', array_slice($tokens, 1));
                if (in_array($tokens[1], $knownModules, true)) {
                    $modulePart = $tokens[1];
                }
            } elseif (in_array($last, $knownActions, true) && count($tokens) > 1) {
                $actionPart   = $last;
                $resourcePart = implode('_', array_slice($tokens, 0, -1));
            } else {
                $resourcePart = implode('_', $tokens ?: ['general']);
            }
        }

        $resourcePart = str_replace(' ', '_', trim($resourcePart));
        if ($resourcePart === '') {
            $resourcePart = 'general';
        }

        return [
            'module_key'     => $modulePart,
            'module_label'   => $this->formatPermissionLabel($modulePart),
            'resource_key'   => $resourcePart,
            'resource_label' => $this->formatPermissionLabel($resourcePart),
            'action_key'     => $actionPart,
            'action_label'   => $this->formatPermissionLabel($actionPart),
        ];
    }

    private function resolvePermissionModuleKey(string $normalizedName, array $parts): string
    {
        $knownModules = $this->knownPermissionModules();

        foreach ($knownModules as $knownModule) {
            if (str_starts_with($normalizedName, $knownModule . '.') || str_starts_with($normalizedName, $knownModule . ' ')) {
                return $knownModule;
            }
        }

        foreach ($parts as $part) {
            if (in_array($part, $knownModules, true)) {
                return $part;
            }
        }

        return 'general';
    }

    private function formatPermissionLabel(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $value));
    }

    private function permissionModulePriority(string $module): int
    {
        return match ($module) {
            'personnel'    => 1,
            'inventory'    => 2,
            'laboratory'   => 3,
            'equipment'    => 4,
            'dms'          => 5,
            'crm'          => 6,
            'tickets'      => 7,
            'system'       => 8,
            'settings'     => 9,
            'ai'           => 10,
            'ai_analytics' => 11,
            'audit'        => 12,
            'calendar'     => 13,
            'risk'         => 14,
            'matrix'       => 15,
            'general'      => 99,
            default        => 50,
        };
    }

    private function knownPermissionModules(): array
    {
        return ['personnel', 'inventory', 'laboratory', 'equipment', 'dms', 'crm', 'tickets', 'system', 'settings', 'ai', 'ai_analytics', 'audit', 'calendar', 'risk', 'matrix', 'module'];
    }
}
