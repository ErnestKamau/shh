<?php

namespace App\Livewire\Personnel;

use App\Http\Controllers\PersonnelWorkHistoryController;
use App\InventoryDepartment;
use App\Lab;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\User;
use App\UserLabRelation;
use App\Models\Personnel\PersonelCertification;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillsMatrixDetail;
use App\Models\SkillsMatrix\SkillsMatrixRoleRequirment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Auth\Role;
use App\Services\Personnel\PersonnelSignatureService;
use Spatie\Permission\PermissionRegistrar;

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
    public string $labSearch = '';
    public string $labSectionSearch = '';
    public bool $showDesignationDropdown = false;
    public bool $showEducationDropdown = false;
    public bool $showPositionDropdown = false;
    public bool $showDepartmentDropdown = false;
    public bool $showLabDropdown = false;
    public bool $showLabSectionDropdown = false;
    public ?string $selectedDesignationId = null;
    public ?string $selectedEducationId = null;
    public ?string $selectedPositionId = null;
    public ?string $selectedDepartmentId = null;
    /** @var array<int, string> */
    public array $selectedLabIds = [];
    /** @var array<int, string> */
    public array $selectedLabSectionIds = [];

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

    public int $detailsStep = 1;
    public string $detailsFirstName = '';
    public string $detailsMiddleName = '';
    public string $detailsLastName = '';
    public string $detailsEmail = '';
    public string $detailsPhone = '';
    public string $detailsIdNumber = '';
    public ?string $detailsDateOfBirth = null;
    public ?string $detailsEmploymentDate = null;
    public bool $detailsAnalystIsGazzetted = false;
    public ?string $detailsDateOfGazzette = null;
    public string $detailsGazzetteNo = '';
    public ?string $detailsStartOfCareer = null;
    public $detailsSignatureUpload;
    public string $detailsSignatureData = '';

    public function mount(string $userId): void
    {
        $this->userId = $userId;

        $user = $this->user;
        $this->selectedDesignationId = $user->designation ? (string) $user->designation : null;
        $this->selectedEducationId = $user->education_level ? (string) $user->education_level : null;
        $this->selectedPositionId = $user->position ? (string) $user->position : null;
        $this->selectedDepartmentId = $user->department_id ? (string) $user->department_id : null;
        $this->selectedLabIds = UserLabRelation::where('user_id', $user->id)
            ->pluck('lab_id')
            ->map(fn ($id): string => (string) $id)
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values()
            ->all();
        $this->selectedLabSectionIds = collect(explode(',', (string) ($user->lab_section_id ?? '')))
            ->map(fn ($id): string => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values()
            ->all();

        $this->detailsFirstName = $this->plainTextNamePart($user->first_name);
        $this->detailsMiddleName = $this->plainTextNamePart($user->middle_name);
        $this->detailsLastName = $this->plainTextNamePart($user->last_name);

        if ($this->detailsFirstName === '' && $this->detailsMiddleName === '' && $this->detailsLastName === '') {
            $displayName = $this->plainTextNamePart($user->name);
            if ($displayName !== '') {
                $parts = preg_split('/\s+/', $displayName) ?: [];
                $this->detailsFirstName = (string) ($parts[0] ?? '');
                $this->detailsLastName = count($parts) > 1 ? (string) array_pop($parts) : '';
                $this->detailsMiddleName = count($parts) > 1 ? trim(implode(' ', array_slice($parts, 1))) : '';
            }
        }

        $this->detailsEmail = (string) ($user->email ?? '');
        $this->detailsPhone = (string) ($user->phone ?? '');
        $this->detailsIdNumber = (string) ($user->id_number ?? '');
        $this->detailsDateOfBirth = !empty($user->date_of_birth) ? date('Y-m-d', strtotime((string) $user->date_of_birth)) : null;
        $this->detailsEmploymentDate = !empty($user->employment_date) ? date('Y-m-d', strtotime((string) $user->employment_date)) : null;
        $this->detailsAnalystIsGazzetted = (bool) ($user->analyst_is_gazzetted ?? false);
        $this->detailsDateOfGazzette = !empty($user->date_of_gazzette) ? date('Y-m-d', strtotime((string) $user->date_of_gazzette)) : null;
        $this->detailsGazzetteNo = (string) ($user->gazzette_no ?? '');
        $this->detailsStartOfCareer = !empty($user->start_of_career) ? date('Y-m-d', strtotime((string) $user->start_of_career)) : null;
    }

    private function plainTextNamePart(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '' || str_starts_with($value, 'eyJ')) {
            return '';
        }

        return $value;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['roles', 'details', 'work_history', 'certifications', 'capability_matrix', 'attachments'], true) ? $tab : 'roles';
        $this->resetPage('rolesPage');
        $this->resetPage('workPage');

        if ($this->activeTab === 'details') {
            $this->detailsStep = 1;
        }

        if ($this->activeTab !== 'roles') {
            $this->expandedRoleRows = [];
        }
    }

    public function setDetailsStep(int $step): void
    {
        $this->detailsStep = max(1, min(3, $step));
    }

    public function nextDetailsStep(): void
    {
        $this->validateDetailsStep($this->detailsStep);
        $this->detailsStep = min(3, $this->detailsStep + 1);
    }

    public function previousDetailsStep(): void
    {
        $this->detailsStep = max(1, $this->detailsStep - 1);
    }

    public function updatedDetailsAnalystIsGazzetted(bool $value): void
    {
        if (!$value) {
            $this->detailsDateOfGazzette = null;
            $this->detailsGazzetteNo = '';
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

    public function clearLab(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedLabIds = [];
            return;
        }

        $this->selectedLabIds = array_values(array_filter($this->selectedLabIds, fn (string $labId): bool => $labId !== $id));
    }

    public function selectLabSection(string $id): void
    {
        if (in_array($id, $this->selectedLabSectionIds, true)) {
            $this->selectedLabSectionIds = array_values(
                array_filter($this->selectedLabSectionIds, fn (string $sectionId): bool => $sectionId !== $id)
            );
        } else {
            $this->selectedLabSectionIds[] = $id;
            $this->selectedLabSectionIds = array_values(array_unique($this->selectedLabSectionIds));
        }

        $this->labSectionSearch = '';
    }

    public function clearLabSection(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedLabSectionIds = [];
            return;
        }

        $this->selectedLabSectionIds = array_values(
            array_filter($this->selectedLabSectionIds, fn (string $sectionId): bool => $sectionId !== $id)
        );
    }

    public function searchLabSection(): void
    {
        $this->showLabSectionDropdown = true;
    }

    public function saveUserDetails(): void
    {
        $this->validateDetailsStep(1);
        $this->validateDetailsStep(2);
        $this->validateDetailsStep(3);

        $this->validate([
            'detailsEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId, 'id')],
            'detailsSignatureUpload' => 'nullable|image|max:3072',
            'detailsSignatureData' => 'nullable|string',
        ]);

        $user = $this->user;
        $originalDepartment = (string) ($user->department_id ?? '');
        $originalPosition = (string) ($user->position ?? '');

        $user->first_name = trim($this->detailsFirstName);
        $user->middle_name = trim($this->detailsMiddleName);
        $user->last_name = trim($this->detailsLastName);
        $user->name = trim(implode(' ', array_filter([
            trim($this->detailsFirstName),
            trim($this->detailsMiddleName),
            trim($this->detailsLastName),
        ], fn (string $part): bool => $part !== '')));
        $user->email = trim($this->detailsEmail);
        $user->phone = trim($this->detailsPhone);
        $user->id_number = trim($this->detailsIdNumber) !== '' ? trim($this->detailsIdNumber) : null;
        $user->date_of_birth = $this->detailsDateOfBirth ?: null;
        $user->employment_date = $this->detailsEmploymentDate ?: null;
        $user->designation = $this->selectedDesignationId ?: null;
        $user->education_level = $this->selectedEducationId ?: null;
        $user->position = $this->selectedPositionId ?: null;
        $user->department_id = $this->selectedDepartmentId ?: null;
        $user->analyst_is_gazzetted = $this->detailsAnalystIsGazzetted;
        $user->date_of_gazzette = $this->detailsAnalystIsGazzetted ? ($this->detailsDateOfGazzette ?: null) : null;
        $user->gazzette_no = $this->detailsAnalystIsGazzetted ? (trim($this->detailsGazzetteNo) !== '' ? trim($this->detailsGazzetteNo) : null) : null;
        $user->start_of_career = $this->detailsStartOfCareer ?: null;

        app(PersonnelSignatureService::class)->applyToUser(
            $user,
            $this->detailsSignatureUpload,
            $this->detailsSignatureData,
        );

        $selectedLabIds = array_values(array_unique(array_filter($this->selectedLabIds, fn ($id): bool => (string) $id !== '')));
        $selectedLabSectionIds = array_values(array_unique(array_filter($this->selectedLabSectionIds, fn ($id): bool => (string) $id !== '')));

        $user->lab_section_id = implode(',', $selectedLabSectionIds);

        $user->save();

        UserLabRelation::where('user_id', $user->id)->delete();
        foreach ($selectedLabIds as $labId) {
            UserLabRelation::create([
                'user_id' => $user->id,
                'lab_id' => (string) $labId,
            ]);
        }

        if ($originalDepartment !== (string) ($user->department_id ?? '') || $originalPosition !== (string) ($user->position ?? '')) {
            (new PersonnelWorkHistoryController())->updateWorkHistory($user->id, $user->department_id, $user->position);
        }

        if ($this->selectedPositionId) {
            $selectedRole = Role::query()
                ->where('guard_name', 'web')
                ->find($this->selectedPositionId);

            if ($selectedRole && !$user->hasRole($selectedRole)) {
                $user->assignRole($selectedRole);
            }
        }

        $this->detailsSignatureUpload = null;
        $this->detailsSignatureData = '';
        $this->message = 'User details saved.';
        $this->messageType = 'success';
    }

    private function validateDetailsStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'detailsFirstName' => 'required|string|max:255',
                'detailsMiddleName' => 'nullable|string|max:255',
                'detailsLastName' => 'nullable|string|max:255',
                'detailsEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId, 'id')],
                'detailsPhone' => 'nullable|string|max:255',
                'detailsIdNumber' => 'nullable|string|max:255',
                'detailsDateOfBirth' => 'nullable|date',
            ]);
            return;
        }

        if ($step === 2) {
            $this->validate([
                'detailsEmploymentDate' => 'nullable|date',
                'selectedDesignationId' => ['required', 'string', Rule::exists('module_pre_configs', 'id')->where(fn ($q) => $q->where('type', 'Job Description'))],
                'selectedEducationId' => ['nullable', 'string', Rule::exists('module_pre_configs', 'id')->where(fn ($q) => $q->where('type', 'Educational Levels'))],
                'selectedPositionId' => ['required', 'string', Rule::exists('spatie_roles', 'id')],
                'selectedDepartmentId' => 'required|string|exists:inventory_departments,id',
            ]);
            return;
        }

        if ($step === 3) {
            $this->validate([
                'selectedLabIds' => 'array',
                'selectedLabIds.*' => 'string|exists:labs,id',
                'selectedLabSectionIds' => 'array',
                'selectedLabSectionIds.*' => 'string|exists:sample_analysis_stages,id',
                'detailsAnalystIsGazzetted' => 'boolean',
                'detailsDateOfGazzette' => 'nullable|date',
                'detailsGazzetteNo' => 'nullable|string|max:255',
                'detailsStartOfCareer' => 'nullable|date',
            ]);

            if ($this->detailsAnalystIsGazzetted && !$this->detailsDateOfGazzette) {
                $this->addError('detailsDateOfGazzette', 'The date of gazzette field is required when analyst is gazzetted.');
            }

            return;
        }
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
            $this->flushPermissionCacheForUser();
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
        $this->flushPermissionCacheForUser();

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
            $item = PersonelCertification::where('id', $certificationId)
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

        PersonelCertification::updateOrCreate(
            ['id' => $certificationId],
            [
                'user_id'        => $this->userId,
                'title'          => trim($this->certificationTitle),
                'certifying_body' => trim($this->certificationBody),
                'valid_from'     => $this->certificationValidFrom,
                'valid_to'       => $this->certificationValidTo,
                'attachment_path' => $attachmentPath,
                'created_by'     => auth()->id(),
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

        $item = PersonelCertification::where('id', $this->editingPersonnelCertificationId)
            ->where('user_id', $this->userId)
            ->first();

        if ($item && !empty($item->attachment_path)) {
            Storage::disk('public')->delete((string) $item->attachment_path);
        }

        PersonelCertification::where('id', $this->editingPersonnelCertificationId)
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
        return ModulePreConfigs::query()
            ->where('type', 'Job Description')
            ->whereIn('module', ['Personnel-Management', 'Skills-Matrix'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getEducationLevelsProperty()
    {
        return ModulePreConfigs::query()->where('type', 'Educational Levels')->orderBy('name')->get(['id', 'name']);
    }

    public function getPositionsProperty()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);
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

    public function getLabsProperty()
    {
        return Lab::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getLabSectionsProperty()
    {
        $query = SampleAnalysisStage::query()
            ->where('active', 1)
            ->where('is_sample_stage', 0);

        if ($this->selectedLabIds !== []) {
            $query->where(function ($builder) {
                $builder->whereIn('lab_id', $this->selectedLabIds)
                    ->orWhereNull('lab_id');
            });
        }

        return $query->orderBy('name')->get(['id', 'name', 'code', 'lab_id']);
    }

    public function getAllRolesProperty()
    {
        return Role::query()
            ->where('guard_name', 'web')
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
        return PersonelCertification::where('user_id', $this->userId)
            ->orderByDesc('valid_to')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPositionSkillsMatrixIdsProperty(): Collection
    {
        $positionId = trim((string) ($this->user->position ?? ''));
        if ($positionId === '') {
            return collect();
        }

        $ids = SkillMarixRole::query()
            ->whereRaw('job_description_id::text = ?', [$positionId])
            ->pluck('skills_matrix_id');

        if ($ids->isEmpty()) {
            $positionName = ModulePreConfigs::query()
                ->whereRaw('id::text = ?', [$positionId])
                ->value('name');

            if (is_string($positionName) && $positionName !== '') {
                $ids = SkillsMatrixRoleRequirment::query()
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
        $matrixIds = $this->positionSkillsMatrixIds;
        if ($matrixIds->isEmpty()) {
            return collect();
        }

        $positionId = trim((string) ($this->user->position ?? ''));

        $rows = SkillsMatrixDetail::query()
            ->from('skills_matrix_detail as smd')
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

    private function flushPermissionCacheForUser(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->user->unsetRelation('roles');
        $this->user->load('roles.permissions');
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
