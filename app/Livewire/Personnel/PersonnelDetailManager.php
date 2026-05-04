<?php

namespace App\Livewire\Personnel;

use App\Directorate;
use App\InventoryDepartment;
use App\Lab;
use App\ModulePreConfigs;
use App\User;
use App\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Auth\Role;

class PersonnelDetailManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $userId;
    public string $activeTab = 'roles';
    public string $designationSearch = '';
    public string $educationSearch = '';
    public string $positionSearch = '';
    public string $departmentSearch = '';
    public string $licenseSearch = '';
    public string $zoneSearch = '';
    public string $directorateSearch = '';
    public string $labSearch = '';
    public bool $showDesignationDropdown = false;
    public bool $showEducationDropdown = false;
    public bool $showPositionDropdown = false;
    public bool $showDepartmentDropdown = false;
    public bool $showLicenseDropdown = false;
    public bool $showZoneDropdown = false;
    public bool $showDirectorateDropdown = false;
    public bool $showLabDropdown = false;
    public ?string $selectedDesignationId = null;
    public ?string $selectedEducationId = null;
    public ?string $selectedPositionId = null;
    public ?string $selectedDepartmentId = null;
    public ?string $selectedLicenseKey = null;
    /** @var array<int, string> */
    public array $selectedZoneIds = [];
    /** @var array<int, string> */
    public array $selectedDirectorateIds = [];
    /** @var array<int, string> */
    public array $selectedLabIds = [];

    public bool $showAddRoleModal = false;
    public bool $showDeleteRoleModal = false;
    public ?string $selectedRoleId = null;
    public string $selectedRoleName = '';

    /** @var array<int, string> */
    public array $selectedRoleIds = [];

    public string $roleSearch = '';
    public bool $showRoleDropdown = false;
    public string $message = '';
    public string $messageType = 'success';

    public string $rolesSearch = '';
    public int $rolesPerPage = 10;

    /** @var array<int, int> */
    public array $perPageOptions = [5, 10, 25, 50];

    public string $workHistorySearch = '';
    public int $workHistoryPerPage = 10;

    public bool $showCertificationModal = false;
    public bool $showDeleteCertificationModal = false;
    public ?string $editingPersonnelCertificationId = null;
    public string $certificationTitle = '';
    public string $certificationBody = '';
    public ?string $certificationValidFrom = null;
    public ?string $certificationValidTo = null;
    public $certificationAttachment;
    public ?string $certificationExistingAttachmentPath = null;

    /** @var array<int, string> */
    public array $expandedRoleRows = [];

    public function mount(string $userId): void
    {
        $this->userId = $userId;

        $user = $this->user;
        $this->selectedDesignationId = $user->designation ? (string) $user->designation : null;
        $this->selectedEducationId = $user->education_level ? (string) $user->education_level : null;
        $this->selectedPositionId = $user->position ? (string) $user->position : null;
        $this->selectedDepartmentId = $user->department_id ? (string) $user->department_id : null;
        $this->selectedLicenseKey = $user->license_type ? (string) $user->license_type : null;
        $this->selectedZoneIds = [];
        $this->selectedDirectorateIds = [];
        $this->selectedLabIds = [];

        if (Schema::hasColumn('users', 'zone_id') && $user->zone_id) {
            $this->selectedZoneIds[] = (string) $user->zone_id;
        }

        if (Schema::hasTable('user_zone_relation')) {
            $zoneRelations = DB::table('user_zone_relation')
                ->where('user_id', $user->id)
                ->pluck('zone_id')
                ->map(fn ($id): string => (string) $id)
                ->all();
            $this->selectedZoneIds = array_merge($this->selectedZoneIds, $zoneRelations);
        }

        if (Schema::hasTable('user_directorate_relation')) {
            $this->selectedDirectorateIds = DB::table('user_directorate_relation')
                ->where('user_id', $user->id)
                ->pluck('directorate_id')
                ->map(fn ($id): string => (string) $id)
                ->all();
        }

        if (Schema::hasTable('user_lab_relation')) {
            $this->selectedLabIds = DB::table('user_lab_relation')
                ->where('user_id', $user->id)
                ->pluck('lab_id')
                ->map(fn ($id): string => (string) $id)
                ->all();
        }

        $this->selectedZoneIds = array_values(array_unique(array_filter($this->selectedZoneIds, fn ($id): bool => (string) $id !== '')));
        $this->selectedDirectorateIds = array_values(array_unique(array_filter($this->selectedDirectorateIds, fn ($id): bool => (string) $id !== '')));
        $this->selectedLabIds = array_values(array_unique(array_filter($this->selectedLabIds, fn ($id): bool => (string) $id !== '')));
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['roles', 'details', 'work_history', 'certifications', 'capability_matrix'], true) ? $tab : 'roles';
        $this->resetPage('rolesPage');
        $this->resetPage('workPage');

        if ($this->activeTab !== 'roles') {
            $this->expandedRoleRows = [];
        }
    }

    public function updatingRolesSearch(): void
    {
        $this->expandedRoleRows = [];
        $this->resetPage('rolesPage');
    }

    public function updatingRolesPerPage(): void
    {
        $this->expandedRoleRows = [];
        $this->resetPage('rolesPage');
    }

    public function updatingWorkHistorySearch(): void
    {
        $this->resetPage('workPage');
    }

    public function updatingWorkHistoryPerPage(): void
    {
        $this->resetPage('workPage');
    }

    public function toggleRoleDetails(string $roleId): void
    {
        if (in_array($roleId, $this->expandedRoleRows, true)) {
            $this->expandedRoleRows = array_values(array_filter($this->expandedRoleRows, fn (string $id): bool => $id !== $roleId));
            return;
        }

        $this->expandedRoleRows[] = $roleId;
        $this->expandedRoleRows = array_values(array_unique($this->expandedRoleRows));
    }

    public function expandAllRoleDetails(): void
    {
        $this->expandedRoleRows = $this->rolesPage
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->values()
            ->all();
    }

    public function collapseAllRoleDetails(): void
    {
        $this->expandedRoleRows = [];
    }

    public function closeSelectDropdowns(): void
    {
        $this->showDesignationDropdown = false;
        $this->showEducationDropdown = false;
        $this->showPositionDropdown = false;
        $this->showDepartmentDropdown = false;
        $this->showLicenseDropdown = false;
        $this->showZoneDropdown = false;
        $this->showDirectorateDropdown = false;
        $this->showLabDropdown = false;
        $this->showRoleDropdown = false;
    }

    public function searchDesignation(): void
    {
        $this->showDesignationDropdown = true;
    }

    public function searchEducation(): void
    {
        $this->showEducationDropdown = true;
    }

    public function searchPosition(): void
    {
        $this->showPositionDropdown = true;
    }

    public function searchDepartment(): void
    {
        $this->showDepartmentDropdown = true;
    }

    public function searchLicense(): void
    {
        $this->showLicenseDropdown = true;
    }

    public function searchZone(): void
    {
        $this->showZoneDropdown = true;
    }

    public function searchDirectorate(): void
    {
        $this->showDirectorateDropdown = true;
    }

    public function searchLab(): void
    {
        $this->showLabDropdown = true;
    }

    public function selectDesignation(string $id): void
    {
        $this->selectedDesignationId = $id;
        $this->showDesignationDropdown = false;
        $this->designationSearch = '';
    }

    public function selectEducation(string $id): void
    {
        $this->selectedEducationId = $id;
        $this->showEducationDropdown = false;
        $this->educationSearch = '';
    }

    public function selectPosition(string $id): void
    {
        $this->selectedPositionId = $id;
        $this->showPositionDropdown = false;
        $this->positionSearch = '';
    }

    public function selectDepartment(string $id): void
    {
        $this->selectedDepartmentId = $id;
        $this->showDepartmentDropdown = false;
        $this->departmentSearch = '';
    }

    public function selectLicense(string $key): void
    {
        $this->selectedLicenseKey = $key;
        $this->showLicenseDropdown = false;
        $this->licenseSearch = '';
    }

    public function clearDesignation(): void
    {
        $this->selectedDesignationId = null;
    }

    public function clearEducation(): void
    {
        $this->selectedEducationId = null;
    }

    public function clearPosition(): void
    {
        $this->selectedPositionId = null;
    }

    public function clearDepartment(): void
    {
        $this->selectedDepartmentId = null;
    }

    public function clearLicense(): void
    {
        $this->selectedLicenseKey = null;
    }

    public function selectZone(string $id): void
    {
        if (in_array($id, $this->selectedZoneIds, true)) {
            $this->selectedZoneIds = array_values(array_filter($this->selectedZoneIds, fn (string $zoneId): bool => $zoneId !== $id));
        } else {
            $this->selectedZoneIds[] = $id;
            $this->selectedZoneIds = array_values(array_unique($this->selectedZoneIds));
        }

        $this->zoneSearch = '';

        $availableDirectorateIds = $this->directorates
            ->pluck('id')
            ->map(fn ($value): string => (string) $value)
            ->all();

        $this->selectedDirectorateIds = array_values(array_filter(
            $this->selectedDirectorateIds,
            fn (string $directorateId): bool => in_array($directorateId, $availableDirectorateIds, true)
        ));

        $availableLabIds = $this->labs
            ->pluck('id')
            ->map(fn ($value): string => (string) $value)
            ->all();

        $this->selectedLabIds = array_values(array_filter(
            $this->selectedLabIds,
            fn (string $labId): bool => in_array($labId, $availableLabIds, true)
        ));
    }

    public function selectDirectorate(string $id): void
    {
        if (in_array($id, $this->selectedDirectorateIds, true)) {
            $this->selectedDirectorateIds = array_values(array_filter($this->selectedDirectorateIds, fn (string $directorateId): bool => $directorateId !== $id));
        } else {
            $this->selectedDirectorateIds[] = $id;
            $this->selectedDirectorateIds = array_values(array_unique($this->selectedDirectorateIds));
        }

        $this->directorateSearch = '';

        $availableLabIds = $this->labs
            ->pluck('id')
            ->map(fn ($value): string => (string) $value)
            ->all();

        $this->selectedLabIds = array_values(array_filter(
            $this->selectedLabIds,
            fn (string $labId): bool => in_array($labId, $availableLabIds, true)
        ));
    }

    public function selectLab(string $id): void
    {
        if (in_array($id, $this->selectedLabIds, true)) {
            $this->selectedLabIds = array_values(array_filter($this->selectedLabIds, fn (string $labId): bool => $labId !== $id));
        } else {
            $this->selectedLabIds[] = $id;
            $this->selectedLabIds = array_values(array_unique($this->selectedLabIds));
        }

        $this->labSearch = '';
    }

    public function clearZone(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedZoneIds = [];
            $this->selectedDirectorateIds = [];
            $this->selectedLabIds = [];
            return;
        }

        $this->selectedZoneIds = array_values(array_filter($this->selectedZoneIds, fn (string $zoneId): bool => $zoneId !== $id));

        $availableDirectorateIds = $this->directorates
            ->pluck('id')
            ->map(fn ($value): string => (string) $value)
            ->all();

        $this->selectedDirectorateIds = array_values(array_filter(
            $this->selectedDirectorateIds,
            fn (string $directorateId): bool => in_array($directorateId, $availableDirectorateIds, true)
        ));

        $availableLabIds = $this->labs
            ->pluck('id')
            ->map(fn ($value): string => (string) $value)
            ->all();

        $this->selectedLabIds = array_values(array_filter(
            $this->selectedLabIds,
            fn (string $labId): bool => in_array($labId, $availableLabIds, true)
        ));
    }

    public function clearDirectorate(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedDirectorateIds = [];
            $this->selectedLabIds = [];
            return;
        }

        $this->selectedDirectorateIds = array_values(array_filter($this->selectedDirectorateIds, fn (string $directorateId): bool => $directorateId !== $id));

        $availableLabIds = $this->labs
            ->pluck('id')
            ->map(fn ($value): string => (string) $value)
            ->all();

        $this->selectedLabIds = array_values(array_filter(
            $this->selectedLabIds,
            fn (string $labId): bool => in_array($labId, $availableLabIds, true)
        ));
    }

    public function clearLab(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedLabIds = [];
            return;
        }

        $this->selectedLabIds = array_values(array_filter($this->selectedLabIds, fn (string $labId): bool => $labId !== $id));
    }

    public function openAddRoleModal(): void
    {
        $this->selectedRoleIds = [];
        $this->roleSearch = '';
        $this->showRoleDropdown = false;
        $this->showAddRoleModal = true;
    }

    public function closeAddRoleModal(): void
    {
        $this->showAddRoleModal = false;
    }

    public function searchRoles(): void
    {
        $this->showRoleDropdown = true;
    }

    public function toggleRoleSelection(string $roleId): void
    {
        if (in_array($roleId, $this->selectedRoleIds, true)) {
            $this->selectedRoleIds = array_values(array_filter($this->selectedRoleIds, fn (string $v): bool => $v !== $roleId));
            return;
        }

        $this->selectedRoleIds[] = $roleId;
        $this->selectedRoleIds = array_values(array_unique($this->selectedRoleIds));
    }

    public function removeSelectedRole(string $roleId): void
    {
        $this->selectedRoleIds = array_values(array_filter($this->selectedRoleIds, fn (string $v): bool => $v !== $roleId));
    }

    public function addSelectedRoles(): void
    {
        $this->validate([
            'selectedRoleIds' => 'array|min:1',
            'selectedRoleIds.*' => 'string',
        ]);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $this->selectedRoleIds)
            ->pluck('name')
            ->toArray();

        if (!empty($roles)) {
            $this->user->assignRole($roles);
        }

        $this->showAddRoleModal = false;
        $this->message = 'User role(s) added successfully.';
        $this->messageType = 'success';
    }

    public function openDeleteRoleModal(string $roleId): void
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($roleId);

        $this->selectedRoleId = (string) $role->id;
        $this->selectedRoleName = (string) $role->name;
        $this->showDeleteRoleModal = true;
    }

    public function closeDeleteRoleModal(): void
    {
        $this->showDeleteRoleModal = false;
    }

    public function removeRole(): void
    {
        $this->validate([
            'selectedRoleId' => 'required|string',
        ]);

        $role = Role::query()->where('guard_name', 'web')->findOrFail((string) $this->selectedRoleId);
        $this->user->removeRole($role->name);

        $this->showDeleteRoleModal = false;
        $this->selectedRoleId = null;
        $this->selectedRoleName = '';
        $this->expandedRoleRows = array_values(array_filter($this->expandedRoleRows, fn (string $id): bool => $id !== (string) $role->id));
        $this->message = 'User role deleted successfully.';
        $this->messageType = 'success';
    }

    public function openCertificationModal(?string $certificationId = null): void
    {
        $this->resetCertificationForm();

        if ($certificationId) {
            $item = DB::table('personnel_user_certifications')
                ->where('id', $certificationId)
                ->where('user_id', $this->userId)
                ->first();

            if ($item) {
                $this->editingPersonnelCertificationId = (string) $item->id;
                $this->certificationTitle = (string) ($item->title ?? '');
                $this->certificationBody = (string) ($item->certifying_body ?? '');
                $this->certificationValidFrom = $item->valid_from ? date('Y-m-d', strtotime((string) $item->valid_from)) : null;
                $this->certificationValidTo = $item->valid_to ? date('Y-m-d', strtotime((string) $item->valid_to)) : null;
                $this->certificationExistingAttachmentPath = (string) ($item->attachment_path ?? '');
            }
        }

        $this->showCertificationModal = true;
    }

    public function closeCertificationModal(): void
    {
        $this->showCertificationModal = false;
        $this->resetCertificationForm();
    }

    public function saveCertification(): void
    {
        $rules = [
            'certificationTitle' => 'required|string|max:255',
            'certificationBody' => 'required|string|max:255',
            'certificationValidFrom' => 'required|date',
            'certificationValidTo' => 'nullable|date|after_or_equal:certificationValidFrom',
            'certificationAttachment' => $this->editingPersonnelCertificationId ? 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx' : 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];

        $this->validate($rules);

        $certificationId = $this->editingPersonnelCertificationId ?? (string) Str::uuid();
        $attachmentPath = $this->certificationExistingAttachmentPath;

        if ($this->certificationAttachment) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $this->certificationAttachment->store('personnel/certifications', 'public');
        }

        DB::table('personnel_user_certifications')->updateOrInsert(
            ['id' => $certificationId],
            [
                'user_id' => $this->userId,
                'title' => trim($this->certificationTitle),
                'certifying_body' => trim($this->certificationBody),
                'valid_from' => $this->certificationValidFrom,
                'valid_to' => $this->certificationValidTo,
                'attachment_path' => $attachmentPath,
                'created_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->showCertificationModal = false;
        $this->resetCertificationForm();
        $this->message = $this->editingPersonnelCertificationId ? 'Certification updated successfully.' : 'Certification uploaded successfully.';
        $this->messageType = 'success';
    }

    public function openDeleteCertificationModal(string $certificationId): void
    {
        $this->editingPersonnelCertificationId = $certificationId;
        $this->showDeleteCertificationModal = true;
    }

    public function closeDeleteCertificationModal(): void
    {
        $this->showDeleteCertificationModal = false;
    }

    public function deleteCertification(): void
    {
        $this->validate([
            'editingPersonnelCertificationId' => 'required|string',
        ]);

        $item = DB::table('personnel_user_certifications')
            ->where('id', $this->editingPersonnelCertificationId)
            ->where('user_id', $this->userId)
            ->first();

        if ($item && !empty($item->attachment_path)) {
            Storage::disk('public')->delete((string) $item->attachment_path);
        }

        DB::table('personnel_user_certifications')
            ->where('id', $this->editingPersonnelCertificationId)
            ->where('user_id', $this->userId)
            ->delete();

        $this->showDeleteCertificationModal = false;
        $this->editingPersonnelCertificationId = null;
        $this->message = 'Certification deleted successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function getRolePermissionModules(string $roleId): Collection
    {
        $role = $this->allUserRoles->firstWhere('id', $roleId);

        if (!$role) {
            return collect();
        }

        $modules = [];

        foreach ($role->permissions as $permission) {
            $permissionName = (string) ($permission->name ?? '');
            $parsed = $this->parsePermissionName($permissionName);
            $moduleKey = $parsed['module_key'];
            $resourceKey = $parsed['resource_key'];
            $actionKey = $parsed['action_key'];

            if (!isset($modules[$moduleKey])) {
                $modules[$moduleKey] = [
                    'module_key' => $moduleKey,
                    'module_label' => $parsed['module_label'],
                    'has_access' => false,
                    'actions' => [],
                    'resources' => [],
                    'all_permissions' => [],
                ];
            }

            if (!isset($modules[$moduleKey]['resources'][$resourceKey])) {
                $modules[$moduleKey]['resources'][$resourceKey] = [
                    'resource_key' => $resourceKey,
                    'resource_label' => $parsed['resource_label'],
                    'actions' => [],
                    'permission_names' => [],
                ];
            }

            $modules[$moduleKey]['actions'][$actionKey] = [
                'key' => $actionKey,
                'label' => $parsed['action_label'],
            ];
            $modules[$moduleKey]['resources'][$resourceKey]['actions'][$actionKey] = true;
            $modules[$moduleKey]['resources'][$resourceKey]['permission_names'][$actionKey] = $permissionName;
            $modules[$moduleKey]['all_permissions'][] = $permissionName;

            if (in_array($actionKey, ['access', 'view', 'index', 'list', 'read'], true)) {
                $modules[$moduleKey]['has_access'] = true;
            }
        }

        return collect($modules)
            ->sortBy(fn (array $module): string => sprintf('%03d_%s', $this->permissionModulePriority((string) $module['module_key']), strtolower((string) $module['module_label'])))
            ->map(function (array $module): array {
                $actions = collect($module['actions'])
                    ->sortBy(fn (array $action): int => $this->permissionActionPriority((string) $action['key']))
                    ->values()
                    ->all();

                $resources = collect($module['resources'])
                    ->sortBy('resource_label', SORT_NATURAL | SORT_FLAG_CASE)
                    ->map(function (array $resource): array {
                        ksort($resource['actions']);
                        return $resource;
                    })
                    ->values()
                    ->all();

                $module['actions'] = $actions;
                $module['resources'] = $resources;

                if (!$module['has_access']) {
                    $module['has_access'] = !empty($module['resources']);
                }

                return $module;
            })
            ->values();
    }

    public function getUserProperty(): User
    {
        return User::query()->findOrFail($this->userId);
    }

    public function getDesignationsProperty()
    {
        return ModulePreConfigs::query()->where('type', 'Designation')->where('module', 'Personnel-Management')->orderBy('name')->get(['id', 'name']);
    }

    public function getEducationLevelsProperty()
    {
        return ModulePreConfigs::query()->where('type', 'Educational Levels')->where('module', 'Personnel-Management')->orderBy('name')->get(['id', 'name']);
    }

    public function getPositionsProperty()
    {
        return ModulePreConfigs::query()->where('type', 'Job Description')->orderBy('name')->get(['id', 'name']);
    }

    public function getDepartmentsProperty()
    {
        $companyId = getUserCompany();

        $query = InventoryDepartment::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $departments = (clone $query)
            ->where('module', 'organizational')
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($departments->isNotEmpty()) {
            return $departments;
        }

        $departments = (clone $query)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($departments->isNotEmpty()) {
            return $departments;
        }

        return InventoryDepartment::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getDirectoratesProperty()
    {
        $query = Directorate::query()->where('active', 1)->orderBy('name');

        if (!empty($this->selectedZoneIds)) {
            $query->whereIn('zone_id', $this->selectedZoneIds);
        }

        return $query->get(['id', 'name', 'zone_id']);
    }

    public function getLabsProperty()
    {
        $query = Lab::query()->where('active', 1)->orderBy('name');

        if (!empty($this->selectedDirectorateIds)) {
            $query->whereIn('directorate_id', $this->selectedDirectorateIds);
        }

        if (!empty($this->selectedZoneIds)) {
            $query->whereIn('zone_id', $this->selectedZoneIds);
        }

        return $query->get(['id', 'name', 'directorate_id', 'zone_id']);
    }

    public function getAllRolesProperty()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('company_id', getUserCompany())
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getAssignedSpatieGroupsProperty(): Collection
    {
        return $this->allUserRoles
            ->filter(fn ($role): bool => $this->isGroupRole((string) $role->name))
            ->values();
    }

    public function getRolesPageProperty(): Collection
    {
        $search = strtolower(trim($this->rolesSearch));

        return $this->allUserRoles
            ->when($search !== '', function (Collection $roles) use ($search): Collection {
                return $roles->filter(function ($role) use ($search): bool {
                    $name = strtolower((string) $role->name);
                    $description = strtolower((string) ($role->description ?? ''));
                    $type = $this->isGroupRole((string) $role->name) ? 'group' : 'role';

                    return str_contains($name, $search)
                        || str_contains($description, $search)
                        || str_contains($type, $search);
                })->values();
            })
            ->forPage($this->getPage('rolesPage'), $this->rolesPerPage)
            ->values();
    }

    public function getRolesTotalProperty(): int
    {
        $search = strtolower(trim($this->rolesSearch));

        return $this->allUserRoles
            ->when($search !== '', function (Collection $roles) use ($search): Collection {
                return $roles->filter(function ($role) use ($search): bool {
                    $name = strtolower((string) $role->name);
                    $description = strtolower((string) ($role->description ?? ''));
                    $type = $this->isGroupRole((string) $role->name) ? 'group' : 'role';

                    return str_contains($name, $search)
                        || str_contains($description, $search)
                        || str_contains($type, $search);
                })->values();
            })
            ->count();
    }

    public function getWorkHistoryPageProperty(): Collection
    {
        $search = strtolower(trim($this->workHistorySearch));
        $all = collect($this->user->work_history());

        return $all
            ->when($search !== '', fn (Collection $history): Collection => $history->filter(fn ($row): bool => str_contains(strtolower((string) ($row->department_name ?? '')), $search) || str_contains(strtolower((string) ($row->position ?? '')), $search))->values())
            ->forPage($this->getPage('workPage'), $this->workHistoryPerPage)
            ->values();
    }

    public function getWorkHistoryTotalProperty(): int
    {
        $search = strtolower(trim($this->workHistorySearch));
        $all = collect($this->user->work_history());

        return $all
            ->when($search !== '', fn (Collection $history): Collection => $history->filter(fn ($row): bool => str_contains(strtolower((string) ($row->department_name ?? '')), $search) || str_contains(strtolower((string) ($row->position ?? '')), $search))->values())
            ->count();
    }

    public function getPersonnelCertificationsProperty(): Collection
    {
        if (!Schema::hasTable('personnel_user_certifications')) {
            return collect();
        }

        return collect(
            DB::table('personnel_user_certifications')
                ->where('user_id', $this->userId)
                ->orderByDesc('valid_to')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function getPositionSkillsMatrixIdsProperty(): Collection
    {
        $positionId = trim((string) ($this->user->position ?? ''));
        if ($positionId === '') {
            return collect();
        }

        $ids = DB::table('skills_matrix_role')
            ->whereRaw('job_description_id::text = ?', [$positionId])
            ->pluck('skills_matrix_id');

        if ($ids->isEmpty()) {
            $positionName = DB::table('module_pre_configs')
                ->whereRaw('id::text = ?', [$positionId])
                ->value('name');

            if (is_string($positionName) && $positionName !== '') {
                $ids = DB::table('skills_matrix_role_requirments')
                    ->whereRaw('LOWER(role_name) = ?', [strtolower($positionName)])
                    ->pluck('skills_matrix_id');
            }
        }

        return collect($ids)
            ->filter(fn ($id): bool => $id !== null && $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();
    }

    public function getCapabilityRowsProperty(): Collection
    {
        if (!Schema::hasTable('skills_matrix_detail') || !Schema::hasTable('skills_matrix_detail_role')) {
            return collect();
        }

        $matrixIds = $this->positionSkillsMatrixIds;
        if ($matrixIds->isEmpty()) {
            return collect();
        }

        $positionId = trim((string) ($this->user->position ?? ''));

        $rows = DB::table('skills_matrix_detail as smd')
            ->leftJoin('skillsmatrices as sm', DB::raw('sm.id::text'), '=', DB::raw('smd.skill_matrix_id::text'))
            ->leftJoin('skills_matrix_detail_role as smdr', function ($join) use ($positionId): void {
                $join->on(DB::raw('smdr.matrix_detail_id::text'), '=', DB::raw('smd.id::text'));
                if ($positionId !== '') {
                    $join->whereRaw('smdr.role_id::text = ?', [$positionId]);
                }
            })
            ->leftJoin('module_pre_configs as area', DB::raw('area.id::text'), '=', DB::raw('smd.competency_area_id::text'))
            ->leftJoin('module_pre_configs as ctype', DB::raw('ctype.id::text'), '=', DB::raw('smd.competency_type_id::text'))
            ->leftJoin('module_pre_configs as cdesc', DB::raw('cdesc.id::text'), '=', DB::raw('smd.competency_description_id::text'))
            ->leftJoin('module_pre_configs as prof', DB::raw('prof.id::text'), '=', DB::raw('smdr.proficiency_id::text'))
            ->whereIn(DB::raw('smd.skill_matrix_id::text'), $matrixIds->all())
            ->whereNull('smd.deleted_at')
            ->orderBy('smd.skill_matrix_id')
            ->orderBy('area.name')
            ->orderBy('ctype.name')
            ->selectRaw('smd.id as detail_id, smd.skill_matrix_id, sm.name as matrix_name, area.name as competency_area, ctype.name as competency_type, cdesc.name as competency_description, prof.description as proficiency_name, prof.code as proficiency_code, prof.color as proficiency_color')
            ->get();

        return collect($rows)->map(function ($row) {
            $row->matrix_label = $row->matrix_name ?: ('Matrix #' . (string) $row->skill_matrix_id);
            $row->proficiency_label = $row->proficiency_name ?: 'Not rated';
            return $row;
        });
    }

    public function getZonesProperty()
    {
        if (!Schema::hasTable('zones')) {
            return collect();
        }

        return Zone::query()
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->orderBy('key')
            ->get(['id', 'key', 'value']);
    }

    public function getLicenseCountProperty(): array
    {
        $counts = [];

        foreach (User::query()->where('company_id', getUserCompany())->get(['license_type']) as $user) {
            $key = (string) $user->license_type;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    public function render()
    {
        return view('livewire.personnel.personnel-detail-manager');
    }

    private function isGroupRole(string $roleName): bool
    {
        return str_ends_with(strtolower(trim($roleName)), 'group');
    }

    private function formatPermissionModule(string $permissionName): string
    {
        $module = explode('.', $permissionName)[0] ?? 'general';
        $module = str_replace('-', ' ', $module);

        return ucwords($module);
    }

    private function parsePermissionName(string $permissionName): array
    {
        $normalizedName = strtolower(trim($permissionName));
        $parts = array_values(array_filter(explode('.', $normalizedName), fn (string $part): bool => $part !== ''));
        $knownActions = ['access', 'view', 'index', 'list', 'read', 'create', 'store', 'edit', 'update', 'delete', 'destroy', 'approve', 'publish', 'import', 'export', 'print', 'download', 'upload', 'manage'];
        $knownModules = $this->knownPermissionModules();
        $actionAliases = [
            'add' => 'create',
            'bulk import' => 'import',
        ];

        foreach ($actionAliases as $from => $to) {
            if (str_ends_with($normalizedName, '.' . $from)) {
                $normalizedName = substr($normalizedName, 0, -strlen($from)) . $to;
            }
        }

        $parts = array_values(array_filter(explode('.', $normalizedName), fn (string $part): bool => $part !== ''));

        $modulePart = $this->resolvePermissionModuleKey($normalizedName, $parts);
        $resourcePart = 'general';
        $actionPart = 'access';

        if (str_contains($normalizedName, '.')) {
            $moduleIndex = array_search($modulePart, $parts, true);

            if ($modulePart === 'module' && isset($parts[1]) && !in_array($parts[1], $knownActions, true)) {
                $modulePart = $parts[1];
                $resourcePart = 'module';
                $actionPart = 'access';
            } else {
                if (!is_int($moduleIndex)) {
                    $moduleIndex = 0;
                }

                $segmentsAfterModule = array_slice($parts, $moduleIndex + 1);
                if (!empty($segmentsAfterModule)) {
                    $lastSegment = $segmentsAfterModule[count($segmentsAfterModule) - 1];
                    if (in_array($lastSegment, $knownActions, true)) {
                        $actionPart = $lastSegment;
                        $resourceSegments = array_slice($segmentsAfterModule, 0, -1);
                        $resourcePart = implode('_', $resourceSegments ?: ['general']);
                    } else {
                        $resourcePart = implode('_', $segmentsAfterModule);
                    }
                }
            }
        } else {
            $tokens = preg_split('/\s+/', $normalizedName) ?: [];
            $first = $tokens[0] ?? '';
            $last = $tokens[count($tokens) - 1] ?? '';

            if (in_array($first, $knownActions, true) && isset($tokens[1])) {
                $actionPart = $first;
                $resourcePart = implode('_', array_slice($tokens, 1));
                if (in_array($tokens[1], $knownModules, true)) {
                    $modulePart = $tokens[1];
                }
            } elseif (in_array($last, $knownActions, true) && count($tokens) > 1) {
                $actionPart = $last;
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
            'module_key' => $modulePart,
            'module_label' => $this->formatPermissionLabel($modulePart),
            'resource_key' => $resourcePart,
            'resource_label' => $this->formatPermissionLabel($resourcePart),
            'action_key' => $actionPart,
            'action_label' => $this->formatPermissionLabel($actionPart),
        ];
    }

    private function formatPermissionLabel(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $value));
    }

    private function permissionActionPriority(string $action): int
    {
        return match ($action) {
            'access' => 1,
            'view', 'index', 'list', 'read' => 2,
            'create', 'store' => 3,
            'edit', 'update' => 4,
            'delete', 'destroy' => 5,
            'approve', 'publish' => 6,
            'import', 'export', 'download', 'upload', 'print' => 7,
            'manage' => 8,
            default => 50,
        };
    }

    private function permissionModulePriority(string $module): int
    {
        return match ($module) {
            'personnel' => 1,
            'inventory' => 2,
            'laboratory' => 3,
            'equipment' => 4,
            'dms' => 5,
            'crm' => 6,
            'tickets' => 7,
            'system' => 8,
            'settings' => 9,
            'ai' => 10,
            'ai_analytics' => 11,
            'audit' => 12,
            'calendar' => 13,
            'risk' => 14,
            'matrix' => 15,
            'general' => 99,
            default => 50,
        };
    }

    private function resolvePermissionModuleKey(string $normalizedName, array $parts): string
    {
        $knownModules = $this->knownPermissionModules();

        foreach ($knownModules as $knownModule) {
            if (str_starts_with($normalizedName, $knownModule . '.')) {
                return $knownModule;
            }

            if (str_starts_with($normalizedName, $knownModule . ' ')) {
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

    private function knownPermissionModules(): array
    {
        return ['personnel', 'inventory', 'laboratory', 'equipment', 'dms', 'crm', 'tickets', 'system', 'settings', 'ai', 'ai_analytics', 'audit', 'calendar', 'risk', 'matrix', 'module'];
    }

    public function getAllUserRolesProperty(): Collection
    {
        $rolesTable = (new Role())->getTable();

        return $this->user
            ->roles()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->with('permissions:id,name')
            ->get(["{$rolesTable}.id", "{$rolesTable}.name", "{$rolesTable}.description"]);
    }

    private function resetCertificationForm(): void
    {
        $this->editingPersonnelCertificationId = null;
        $this->certificationTitle = '';
        $this->certificationBody = '';
        $this->certificationValidFrom = null;
        $this->certificationValidTo = null;
        $this->certificationAttachment = null;
        $this->certificationExistingAttachmentPath = null;
    }
}
