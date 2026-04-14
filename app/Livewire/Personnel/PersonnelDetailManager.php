<?php

namespace App\Livewire\Personnel;

use App\InventoryDepartment;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class PersonnelDetailManager extends Component
{
    public int $userId;
    public string $activeTab = 'roles';
    public string $designationSearch = '';
    public string $educationSearch = '';
    public string $positionSearch = '';
    public string $departmentSearch = '';
    public string $licenseSearch = '';
    public string $labSectionSearch = '';
    public bool $showDesignationDropdown = false;
    public bool $showEducationDropdown = false;
    public bool $showPositionDropdown = false;
    public bool $showDepartmentDropdown = false;
    public bool $showLicenseDropdown = false;
    public bool $showLabSectionDropdown = false;
    public ?int $selectedDesignationId = null;
    public ?int $selectedEducationId = null;
    public ?int $selectedPositionId = null;
    public ?int $selectedDepartmentId = null;
    public ?string $selectedLicenseKey = null;
    /** @var array<int, int> */
    public array $selectedLabSectionIds = [];
    public bool $showAddRoleModal = false;
    public bool $showDeleteRoleModal = false;
    public ?int $selectedRoleId = null;
    public string $selectedRoleName = '';
    /** @var array<int, int> */
    public array $selectedRoleIds = [];
    public string $roleSearch = '';
    public bool $showRoleDropdown = false;
    public string $message = '';
    public string $messageType = 'success';

    public function mount(int $userId): void
    {
        $this->userId = $userId;
        $user = $this->user;
        $this->selectedDesignationId = $user->designation ? (int) $user->designation : null;
        $this->selectedEducationId = $user->education_level ? (int) $user->education_level : null;
        $this->selectedPositionId = $user->position ? (int) $user->position : null;
        $this->selectedDepartmentId = $user->department_id ? (int) $user->department_id : null;
        $this->selectedLicenseKey = $user->license_type ? (string) $user->license_type : null;
        $this->selectedLabSectionIds = array_values(array_filter(array_map('intval', explode(',', (string) $user->lab_section_id))));
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['roles', 'details', 'work_history'], true) ? $tab : 'roles';
    }

    public function closeSelectDropdowns(): void
    {
        $this->showDesignationDropdown = false;
        $this->showEducationDropdown = false;
        $this->showPositionDropdown = false;
        $this->showDepartmentDropdown = false;
        $this->showLicenseDropdown = false;
        $this->showLabSectionDropdown = false;
        $this->showRoleDropdown = false;
    }

    public function searchDesignation(): void { $this->showDesignationDropdown = true; }
    public function searchEducation(): void { $this->showEducationDropdown = true; }
    public function searchPosition(): void { $this->showPositionDropdown = true; }
    public function searchDepartment(): void { $this->showDepartmentDropdown = true; }
    public function searchLicense(): void { $this->showLicenseDropdown = true; }
    public function searchLabSection(): void { $this->showLabSectionDropdown = true; }
    public function selectDesignation(int $id): void { $this->selectedDesignationId = $id; $this->showDesignationDropdown = false; $this->designationSearch = ''; }
    public function selectEducation(int $id): void { $this->selectedEducationId = $id; $this->showEducationDropdown = false; $this->educationSearch = ''; }
    public function selectPosition(int $id): void { $this->selectedPositionId = $id; $this->showPositionDropdown = false; $this->positionSearch = ''; }
    public function selectDepartment(int $id): void { $this->selectedDepartmentId = $id; $this->showDepartmentDropdown = false; $this->departmentSearch = ''; }
    public function selectLicense(string $key): void { $this->selectedLicenseKey = $key; $this->showLicenseDropdown = false; $this->licenseSearch = ''; }
    public function clearDesignation(): void { $this->selectedDesignationId = null; }
    public function clearEducation(): void { $this->selectedEducationId = null; }
    public function clearPosition(): void { $this->selectedPositionId = null; }
    public function clearDepartment(): void { $this->selectedDepartmentId = null; }
    public function clearLicense(): void { $this->selectedLicenseKey = null; }
    public function toggleLabSection(int $id): void
    {
        if (in_array($id, $this->selectedLabSectionIds, true)) {
            $this->selectedLabSectionIds = array_values(array_filter($this->selectedLabSectionIds, fn (int $v): bool => $v !== $id));
            return;
        }
        $this->selectedLabSectionIds[] = $id;
        $this->selectedLabSectionIds = array_values(array_unique($this->selectedLabSectionIds));
    }
    public function removeLabSection(int $id): void
    {
        $this->selectedLabSectionIds = array_values(array_filter($this->selectedLabSectionIds, fn (int $v): bool => $v !== $id));
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

    public function toggleRoleSelection(int $roleId): void
    {
        if (in_array($roleId, $this->selectedRoleIds, true)) {
            $this->selectedRoleIds = array_values(array_filter($this->selectedRoleIds, fn (int $v): bool => $v !== $roleId));
            return;
        }
        $this->selectedRoleIds[] = $roleId;
        $this->selectedRoleIds = array_values(array_unique($this->selectedRoleIds));
    }

    public function removeSelectedRole(int $roleId): void
    {
        $this->selectedRoleIds = array_values(array_filter($this->selectedRoleIds, fn (int $v): bool => $v !== $roleId));
    }

    public function addSelectedRoles(): void
    {
        $this->validate([
            'selectedRoleIds' => 'array|min:1',
            'selectedRoleIds.*' => 'integer',
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

    public function openDeleteRoleModal(int $roleId): void
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($roleId);
        $this->selectedRoleId = $role->id;
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
            'selectedRoleId' => 'required|integer',
        ]);

        $role = Role::query()->where('guard_name', 'web')->findOrFail((int) $this->selectedRoleId);
        $this->user->removeRole($role->name);

        $this->showDeleteRoleModal = false;
        $this->selectedRoleId = null;
        $this->selectedRoleName = '';
        $this->message = 'User role deleted successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function getUserProperty()
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
        return InventoryDepartment::query()->where('company_id', getUserCompany())->where('module', 'organizational')->orderBy('name')->get(['id', 'name']);
    }

    public function getStagesProperty()
    {
        return SampleAnalysisStage::query()->where('active', 1)->orderBy('name')->get(['id', 'name']);
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
}
