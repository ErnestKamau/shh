<?php

namespace App\Livewire\Personnel;

use App\InventoryDepartment;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\User;
use App\Zone;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class PersonnelTableManager extends Component
{
    use WithPagination;

    public bool $embedded = false;

    public string $search = '';
    public string $activeTab = 'active';
    public string $departmentFilter = '';
    public string $designationFilter = '';
    public string $licenseFilter = '';
    public string $employmentDateFrom = '';
    public string $employmentDateTo = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showAdvancedFilters = false;
    public bool $showAddPersonnelModal = false;
    public bool $showStateModal = false;
    public bool $showResetPasswordModal = false;
    public ?int $selectedPersonnelId = null;
    public string $selectedPersonnelName = '';
    public string $stateAction = 'active';
    public string $newPassword = '';
    public string $confirmPassword = '';

    /** @var array<int, array{id:int,name:string}> */
    public array $departments = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $designations = [];
    /** @var array<string,string> */
    public array $licenses = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $positions = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $educationLevels = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $zones = [];
    public bool $zonesTableAvailable = false;
    /** @var array<int, array{id:int,name:string}> */
    public array $stages = [];
    /** @var array<string,int> */
    public array $licenseCount = [];

    /** @var array<string,mixed> */
    public array $personnelForm = [
        'designation' => '',
        'first_name' => '',
        'middle_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'id_number' => '',
        'employment_date' => '',
        'educational_level' => '',
        'position' => '',
        'department' => '',
        'zone_id' => '',
        'lab_section_id' => [],
        'user_license' => '',
        'active' => true,
    ];

    public string $message = '';
    public string $messageType = 'success';
    public string $designationSearch = '';
    public string $educationLevelSearch = '';
    public string $positionSearch = '';
    public string $departmentSearchInput = '';
    public string $licenseSearch = '';
    public string $labSectionSearch = '';
    public bool $showDesignationDropdown = false;
    public bool $showEducationLevelDropdown = false;
    public bool $showPositionDropdown = false;
    public bool $showDepartmentDropdown = false;
    public bool $showLicenseDropdown = false;
    public bool $showLabSectionDropdown = false;
    /** @var array<int, array{id:int,name:string}> */
    public array $filteredDesignations = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $filteredEducationLevels = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $filteredPositions = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $filteredDepartments = [];
    /** @var array<int, array{key:string,name:string,count:int,limit:int,disabled:bool}> */
    public array $filteredLicenses = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $filteredLabSections = [];

    protected $paginationTheme = 'bootstrap';

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;
        $this->departments = InventoryDepartment::query()
            ->where('company_id', getUserCompany())
            ->where('module', 'organizational')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (int) $item->id, 'name' => (string) $item->name])
            ->toArray();

        $this->designations = ModulePreConfigs::query()
            ->where('type', 'Designation')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (int) $item->id, 'name' => (string) $item->name])
            ->toArray();

        $this->licenses = getUserLicenses();
        $this->positions = ModulePreConfigs::query()
            ->where('type', 'Job Description')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (int) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->educationLevels = ModulePreConfigs::query()
            ->where('type', 'Educational Levels')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (int) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->zonesTableAvailable = Schema::hasTable('zones');
        $this->zones = $this->zonesTableAvailable
            ? Zone::query()
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->orderBy('key')
                ->get(['id', 'key', 'value'])
                ->map(fn ($item): array => ['id' => (int) $item->id, 'key' => (string) $item->key, 'value' => (string) $item->value])
                ->toArray()
            : [];
        $this->stages = SampleAnalysisStage::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (int) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->licenseCount = $this->getUsersByLicense();
    }

    #[On('personnel-open-add-modal')]
    public function openAddPersonnelModal(): void
    {
        $this->resetPersonnelForm();
        $this->primeAddModalDropdowns();
        $this->showAddPersonnelModal = true;
    }

    public function closeAddPersonnelModal(): void
    {
        $this->showAddPersonnelModal = false;
        $this->resetAddModalSearches();
    }

    public function savePersonnel(): void
    {
        $validationRules = [
            'personnelForm.designation' => 'required|integer',
            'personnelForm.first_name' => 'required|string|max:255',
            'personnelForm.middle_name' => 'nullable|string|max:255',
            'personnelForm.last_name' => 'nullable|string|max:255',
            'personnelForm.email' => 'required|email|max:255|unique:users,email',
            'personnelForm.phone' => 'nullable|string|max:255',
            'personnelForm.id_number' => 'required|string|max:255',
            'personnelForm.employment_date' => 'nullable|date',
            'personnelForm.educational_level' => 'nullable|integer',
            'personnelForm.position' => 'required|integer',
            'personnelForm.department' => 'required|integer',
            'personnelForm.zone_id' => 'nullable|integer',
            'personnelForm.lab_section_id' => 'array',
            'personnelForm.user_license' => 'required|string|max:255',
            'personnelForm.active' => 'boolean',
        ];

        if ($this->zonesTableAvailable) {
            $validationRules['personnelForm.zone_id'] .= '|exists:zones,id';
        }

        $this->validate($validationRules);

        $personnel = new User();
        $personnel->first_name = (string) $this->personnelForm['first_name'];
        $personnel->middle_name = (string) $this->personnelForm['middle_name'];
        $personnel->last_name = (string) $this->personnelForm['last_name'];
        $personnel->name = trim($personnel->first_name . ' ' . $personnel->middle_name . ' ' . $personnel->last_name);
        $personnel->email = (string) $this->personnelForm['email'];
        $personnel->phone = (string) $this->personnelForm['phone'];
        $personnel->company_id = getUserCompany();
        $personnel->location_id = getCurrentUserLocation()->id;
        $personnel->department_id = (int) $this->personnelForm['department'];
        $personnel->designation = (int) $this->personnelForm['designation'];
        $personnel->position = (int) $this->personnelForm['position'];
        $personnel->education_level = $this->personnelForm['educational_level'] !== '' ? (int) $this->personnelForm['educational_level'] : null;
        $personnel->employment_date = $this->personnelForm['employment_date'] !== '' ? (string) $this->personnelForm['employment_date'] : null;
        $personnel->id_number = (string) $this->personnelForm['id_number'];
        $personnel->zone_id = $this->zonesTableAvailable && $this->personnelForm['zone_id'] !== ''
            ? (int) $this->personnelForm['zone_id']
            : null;
        $personnel->active = $this->personnelForm['active'] ? 1 : 0;
        $personnel->lab_section_id = implode(',', $this->personnelForm['lab_section_id'] ?? []);
        $personnel->license_type = (string) $this->personnelForm['user_license'];
        $personnel->password = bcrypt($personnel->first_name . config('app.name') . date('Y'));
        $personnel->save();

        $this->licenseCount = $this->getUsersByLicense();
        $this->message = 'Personnel added successfully.';
        $this->messageType = 'success';
        $this->showAddPersonnelModal = false;
        $this->resetPersonnelForm();
        $this->resetPage();
    }

    public function openStateModal(int $personnelId): void
    {
        $personnel = User::query()->findOrFail($personnelId);
        $this->selectedPersonnelId = $personnel->id;
        $this->selectedPersonnelName = (string) $personnel->name;
        $this->stateAction = (int) $personnel->active === 1 ? 'active' : 'deactive';
        $this->showStateModal = true;
    }

    public function closeStateModal(): void
    {
        $this->showStateModal = false;
    }

    public function savePersonnelState(): void
    {
        $this->validate([
            'selectedPersonnelId' => 'required|integer',
            'stateAction' => 'required|in:active,deactive',
        ]);

        $personnel = User::query()->findOrFail((int) $this->selectedPersonnelId);
        $personnel->active = $this->stateAction === 'active' ? 1 : 0;
        $personnel->save();

        $this->showStateModal = false;
        $this->message = 'Personnel state updated successfully.';
        $this->messageType = 'success';
    }

    public function openResetPasswordModal(int $personnelId): void
    {
        $personnel = User::query()->findOrFail($personnelId);
        $this->selectedPersonnelId = $personnel->id;
        $this->selectedPersonnelName = (string) $personnel->name;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->showResetPasswordModal = true;
    }

    public function closeResetPasswordModal(): void
    {
        $this->showResetPasswordModal = false;
    }

    public function resetPersonnelPassword(): void
    {
        $this->validate([
            'selectedPersonnelId' => 'required|integer',
            'newPassword' => 'required|string|min:6',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        $personnel = User::query()->findOrFail((int) $this->selectedPersonnelId);
        $personnel->password = bcrypt($this->newPassword);
        $personnel->save();

        $this->showResetPasswordModal = false;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->message = 'Password reset successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDesignationFilter(): void
    {
        $this->resetPage();
    }

    public function updatingLicenseFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEmploymentDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingEmploymentDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['active', 'deactive'], true) ? $tab : 'active';
        $this->resetPage();
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->departmentFilter = '';
        $this->designationFilter = '';
        $this->licenseFilter = '';
        $this->employmentDateFrom = '';
        $this->employmentDateTo = '';
        $this->resetPage();
    }

    public function getPersonnelProperty()
    {
        $query = User::query()
            ->join('inventory_departments as d', 'd.id', '=', 'users.department_id')
            ->leftJoin('module_pre_configs as de', function ($join): void {
                $join->on('de.id', '=', 'users.designation')
                    ->where('de.type', '=', 'Designation');
            })
            ->leftJoin('module_pre_configs as e', function ($join): void {
                $join->on('e.id', '=', 'users.education_level')
                    ->where('e.type', '=', 'Educational Levels');
            })
            ->leftJoin('module_pre_configs as p', function ($join): void {
                $join->on('p.id', '=', 'users.position')
                    ->where('p.type', '=', 'Job Description');
            })
            ->selectRaw('users.*, d.name as department_name, p.name as position, e.name as education, de.name as designation')
            ->where('users.company_id', getUserCompany());

        if ($this->activeTab === 'active') {
            $query->where('users.active', 1);
        } else {
            $query->where('users.active', 0);
        }

        if ($this->search !== '') {
            $searchText = '%' . $this->search . '%';
            $query->where(function ($builder) use ($searchText): void {
                $builder->where('users.first_name', 'like', $searchText)
                    ->orWhere('users.middle_name', 'like', $searchText)
                    ->orWhere('users.last_name', 'like', $searchText)
                    ->orWhere('users.name', 'like', $searchText)
                    ->orWhere('users.email', 'like', $searchText)
                    ->orWhere('d.name', 'like', $searchText)
                    ->orWhere('p.name', 'like', $searchText)
                    ->orWhere('de.name', 'like', $searchText);
            });
        }

        if ($this->departmentFilter !== '') {
            $query->where('users.department_id', (int) $this->departmentFilter);
        }

        if ($this->designationFilter !== '') {
            $query->where('users.designation', (int) $this->designationFilter);
        }

        if ($this->licenseFilter !== '') {
            $query->where('users.license_type', $this->licenseFilter);
        }

        if ($this->employmentDateFrom !== '') {
            $query->whereDate('users.employment_date', '>=', $this->employmentDateFrom);
        }

        if ($this->employmentDateTo !== '') {
            $query->whereDate('users.employment_date', '<=', $this->employmentDateTo);
        }

        return $query->orderBy('users.name')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.personnel-table-manager');
    }

    public function searchDesignations(): void
    {
        $this->showDesignationDropdown = true;
        $search = trim($this->designationSearch);
        $this->filteredDesignations = array_values(array_filter(
            $this->designations,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function selectDesignation(int $id): void
    {
        $this->personnelForm['designation'] = $id;
        $this->designationSearch = '';
        $this->showDesignationDropdown = false;
    }

    public function clearDesignation(): void
    {
        $this->personnelForm['designation'] = '';
    }

    public function searchEducationLevels(): void
    {
        $this->showEducationLevelDropdown = true;
        $search = trim($this->educationLevelSearch);
        $this->filteredEducationLevels = array_values(array_filter(
            $this->educationLevels,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function selectEducationLevel(int $id): void
    {
        $this->personnelForm['educational_level'] = $id;
        $this->educationLevelSearch = '';
        $this->showEducationLevelDropdown = false;
    }

    public function clearEducationLevel(): void
    {
        $this->personnelForm['educational_level'] = '';
    }

    public function searchPositions(): void
    {
        $this->showPositionDropdown = true;
        $search = trim($this->positionSearch);
        $this->filteredPositions = array_values(array_filter(
            $this->positions,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function selectPosition(int $id): void
    {
        $this->personnelForm['position'] = $id;
        $this->positionSearch = '';
        $this->showPositionDropdown = false;
    }

    public function clearPosition(): void
    {
        $this->personnelForm['position'] = '';
    }

    public function searchDepartmentsInput(): void
    {
        $this->showDepartmentDropdown = true;
        $search = trim($this->departmentSearchInput);
        $this->filteredDepartments = array_values(array_filter(
            $this->departments,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function selectDepartmentInput(int $id): void
    {
        $this->personnelForm['department'] = $id;
        $this->departmentSearchInput = '';
        $this->showDepartmentDropdown = false;
    }

    public function clearDepartmentInput(): void
    {
        $this->personnelForm['department'] = '';
    }

    public function searchLicenses(): void
    {
        $this->showLicenseDropdown = true;
        $search = trim($this->licenseSearch);
        $licenseRows = [];
        foreach ($this->licenses as $key => $name) {
            $count = (int) ($this->licenseCount[$key] ?? 0);
            $limit = (int) mamboSawa($key . 's');
            $licenseRows[] = [
                'key' => (string) $key,
                'name' => (string) $name,
                'count' => $count,
                'limit' => $limit,
                'disabled' => $count >= $limit,
            ];
        }

        $this->filteredLicenses = array_values(array_filter(
            $licenseRows,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function selectLicense(string $license): void
    {
        $this->personnelForm['user_license'] = $license;
        $this->licenseSearch = '';
        $this->showLicenseDropdown = false;
    }

    public function clearLicense(): void
    {
        $this->personnelForm['user_license'] = '';
    }

    public function searchLabSections(): void
    {
        $this->showLabSectionDropdown = true;
        $search = trim($this->labSectionSearch);
        $this->filteredLabSections = array_values(array_filter(
            $this->stages,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function toggleLabSectionSelection(int $stageId): void
    {
        $selected = $this->personnelForm['lab_section_id'] ?? [];
        if (in_array($stageId, $selected, true)) {
            $this->personnelForm['lab_section_id'] = array_values(array_filter($selected, fn (int $id): bool => $id !== $stageId));
            return;
        }

        $selected[] = $stageId;
        $this->personnelForm['lab_section_id'] = array_values(array_unique($selected));
    }

    public function removeLabSectionSelection(int $stageId): void
    {
        $selected = $this->personnelForm['lab_section_id'] ?? [];
        $this->personnelForm['lab_section_id'] = array_values(array_filter($selected, fn (int $id): bool => $id !== $stageId));
    }

    public function closeAddModalDropdowns(): void
    {
        $this->showDesignationDropdown = false;
        $this->showEducationLevelDropdown = false;
        $this->showPositionDropdown = false;
        $this->showDepartmentDropdown = false;
        $this->showLicenseDropdown = false;
        $this->showLabSectionDropdown = false;
    }

    private function resetPersonnelForm(): void
    {
        $this->personnelForm = [
            'designation' => '',
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'id_number' => '',
            'employment_date' => '',
            'educational_level' => '',
            'position' => '',
            'department' => '',
            'zone_id' => '',
            'lab_section_id' => [],
            'user_license' => '',
            'active' => true,
        ];
        $this->resetAddModalSearches();
    }

    /** @return array<string,int> */
    private function getUsersByLicense(): array
    {
        $counts = [];
        $users = User::query()->where('company_id', getUserCompany())->get(['license_type']);

        foreach ($users as $user) {
            $license = (string) $user->license_type;
            if (!isset($counts[$license])) {
                $counts[$license] = 0;
            }
            $counts[$license]++;
        }

        return $counts;
    }

    private function primeAddModalDropdowns(): void
    {
        $this->filteredDesignations = $this->designations;
        $this->filteredEducationLevels = $this->educationLevels;
        $this->filteredPositions = $this->positions;
        $this->filteredDepartments = $this->departments;
        $this->searchLicenses();
        $this->filteredLabSections = $this->stages;
        $this->showLicenseDropdown = false;
    }

    private function resetAddModalSearches(): void
    {
        $this->designationSearch = '';
        $this->educationLevelSearch = '';
        $this->positionSearch = '';
        $this->departmentSearchInput = '';
        $this->licenseSearch = '';
        $this->labSectionSearch = '';
        $this->closeAddModalDropdowns();
    }
}

