<?php

namespace App\Livewire\Personnel;

use App\Directorate;
use App\Exports\ReportExporter;
use App\InventoryDepartment;
use App\Lab;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\User;
use App\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;

class PersonnelTableManager extends Component
{
    use WithFileUploads;
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
    public ?string $selectedPersonnelId = null;
    public string $selectedPersonnelName = '';
    public string $stateAction = 'active';
    public string $newPassword = '';
    public string $confirmPassword = '';

    /** @var array<int, array{id:string,name:string}> */
    public array $departments = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $designations = [];
    /** @var array<string,string> */
    public array $licenses = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $positions = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $educationLevels = [];
    /** @var array<int, array{id:string,key:string,value:string}> */
    public array $zones = [];
    public bool $zonesTableAvailable = false;
    /** @var array<int, array{id:string,name:string,zone_id:string}> */
    public array $directorates = [];
    public bool $directoratesTableAvailable = false;
    /** @var array<int, array{id:string,name:string,directorate_id:string,zone_id:string}> */
    public array $labs = [];
    public bool $labsTableAvailable = false;
    /** @var array<int, array{id:string,name:string}> */
    public array $stages = [];
    /** @var array<string,int> */
    public array $licenseCount = [];
    public int $addPersonnelStep = 1;
    public $signatureUpload = null;
    public string $signatureData = '';

    /** @var array<string,mixed> */
    public array $personnelForm = [
        'designation' => '',
        'first_name' => '',
        'middle_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'id_number' => '',
        'date_of_birth' => '',
        'employment_date' => '',
        'educational_level' => '',
        'position' => '',
        'analyst_is_gazzetted' => false,
        'date_of_gazzette' => '',
        'start_of_career' => '',
        'department' => '',
        'zone_id' => '',
        'directorate_id' => '',
        'lab_id' => '',
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
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredDesignations = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredEducationLevels = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredPositions = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredDepartments = [];
    /** @var array<int, array{key:string,name:string,count:int,limit:int,disabled:bool}> */
    public array $filteredLicenses = [];
    /** @var array<int, array{id:string,name:string}> */
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
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();

        $this->designations = ModulePreConfigs::query()
            ->where('type', 'Designation')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();

        $this->licenses = getUserLicenses();
        $this->positions = ModulePreConfigs::query()
            ->where('type', 'Job Description')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->educationLevels = ModulePreConfigs::query()
            ->where('type', 'Educational Levels')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->zonesTableAvailable = Schema::hasTable('zones');
        $this->zones = $this->zonesTableAvailable
            ? Zone::query()
                ->where('inventory_location_id', getCurrentUserLocation()->id)
                ->orderBy('key')
                ->get(['id', 'key', 'value'])
                ->map(fn ($item): array => ['id' => (string) $item->id, 'key' => (string) $item->key, 'value' => (string) $item->value])
                ->toArray()
            : [];
        $this->directoratesTableAvailable = Schema::hasTable('directorates');
        $this->directorates = $this->directoratesTableAvailable
            ? Directorate::query()
                ->where('active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'zone_id'])
                ->map(fn ($item): array => [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'zone_id' => (string) ($item->zone_id ?? ''),
                ])
                ->toArray()
            : [];
        $this->labsTableAvailable = Schema::hasTable('labs');
        $this->labs = $this->labsTableAvailable
            ? Lab::query()
                ->where('active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'directorate_id', 'zone_id'])
                ->map(fn ($item): array => [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'directorate_id' => (string) ($item->directorate_id ?? ''),
                    'zone_id' => (string) ($item->zone_id ?? ''),
                ])
                ->toArray()
            : [];
        $this->stages = SampleAnalysisStage::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->licenseCount = $this->getUsersByLicense();
    }

    #[On('personnel-open-add-modal')]
    public function openAddPersonnelModal(): void
    {
        $this->resetPersonnelForm();
        $this->primeAddModalDropdowns();
        $this->addPersonnelStep = 1;
        $this->showAddPersonnelModal = true;
    }

    public function closeAddPersonnelModal(): void
    {
        $this->showAddPersonnelModal = false;
        $this->resetAddModalSearches();
        $this->addPersonnelStep = 1;
        $this->signatureUpload = null;
        $this->signatureData = '';
    }

    public function updatedPersonnelForm($value, string $key): void
    {
        if ($key === 'analyst_is_gazzetted' && !$value) {
            $this->personnelForm['date_of_gazzette'] = '';
        }

        if ($key === 'zone_id') {
            $selectedDirectorateId = (string) ($this->personnelForm['directorate_id'] ?? '');
            if ($selectedDirectorateId !== '' && !$this->directorateBelongsToSelectedZone($selectedDirectorateId)) {
                $this->personnelForm['directorate_id'] = '';
                $this->personnelForm['lab_id'] = '';
            }

            $selectedLabId = (string) ($this->personnelForm['lab_id'] ?? '');
            if ($selectedLabId !== '' && !$this->labMatchesSelections($selectedLabId)) {
                $this->personnelForm['lab_id'] = '';
            }
        }

        if ($key === 'directorate_id') {
            $selectedLabId = (string) ($this->personnelForm['lab_id'] ?? '');
            if ($selectedLabId !== '' && !$this->labMatchesSelections($selectedLabId)) {
                $this->personnelForm['lab_id'] = '';
            }
        }
    }

    public function goToAddPersonnelStep(int $step): void
    {
        $this->addPersonnelStep = max(1, min(4, $step));
    }

    public function nextAddPersonnelStep(): void
    {
        $this->validateAddPersonnelStep($this->addPersonnelStep);
        $this->addPersonnelStep = min(4, $this->addPersonnelStep + 1);
    }

    public function previousAddPersonnelStep(): void
    {
        $this->addPersonnelStep = max(1, $this->addPersonnelStep - 1);
    }

    public function savePersonnel(): void
    {
        $validationRules = [
            'personnelForm.designation' => [
                'required',
                'string',
                Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Designation')),
            ],
            'personnelForm.first_name' => 'required|string|max:255',
            'personnelForm.middle_name' => 'nullable|string|max:255',
            'personnelForm.last_name' => 'nullable|string|max:255',
            'personnelForm.email' => 'required|email|max:255|unique:users,email',
            'personnelForm.phone' => 'nullable|string|max:255',
            'personnelForm.id_number' => 'required|string|max:255',
            'personnelForm.date_of_birth' => 'nullable|date',
            'personnelForm.employment_date' => 'nullable|date',
            'personnelForm.educational_level' => [
                'nullable',
                'string',
                Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Educational Levels')),
            ],
            'personnelForm.position' => [
                'required',
                'string',
                Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Job Description')),
            ],
            'personnelForm.analyst_is_gazzetted' => 'boolean',
            'personnelForm.date_of_gazzette' => 'nullable|date',
            'personnelForm.start_of_career' => 'nullable|date',
            'personnelForm.department' => 'required|string|exists:inventory_departments,id',
            'personnelForm.zone_id' => 'nullable|string',
            'personnelForm.directorate_id' => 'nullable|string',
            'personnelForm.lab_id' => 'nullable|string',
            'personnelForm.lab_section_id' => 'array',
            'personnelForm.user_license' => 'required|string|max:255',
            'personnelForm.active' => 'boolean',
            'signatureUpload' => 'nullable|image|max:3072',
            'signatureData' => 'nullable|string',
        ];

        if ($this->zonesTableAvailable) {
            $validationRules['personnelForm.zone_id'] .= '|exists:zones,id';
        }

        if ($this->directoratesTableAvailable) {
            $validationRules['personnelForm.directorate_id'] .= '|exists:directorates,id';
        }

        if ($this->labsTableAvailable) {
            $validationRules['personnelForm.lab_id'] .= '|exists:labs,id';
        }

        $this->validate($validationRules);

        if ($this->personnelForm['analyst_is_gazzetted'] && $this->personnelForm['date_of_gazzette'] === '') {
            $this->addError('personnelForm.date_of_gazzette', 'The date of gazzette field is required when analyst is gazzetted.');
            return;
        }

        $selectedDirectorateId = (string) ($this->personnelForm['directorate_id'] ?? '');
        if ($selectedDirectorateId !== '' && !$this->directorateBelongsToSelectedZone($selectedDirectorateId)) {
            $this->addError('personnelForm.directorate_id', 'The selected directorate does not belong to the selected organization structure.');
            return;
        }

        $selectedLabId = (string) ($this->personnelForm['lab_id'] ?? '');
        if ($selectedLabId !== '' && !$this->labMatchesSelections($selectedLabId)) {
            $this->addError('personnelForm.lab_id', 'The selected lab does not belong to the current organization selection.');
            return;
        }

        $personnel = new User();
        $personnel->first_name = (string) $this->personnelForm['first_name'];
        $personnel->middle_name = (string) $this->personnelForm['middle_name'];
        $personnel->last_name = (string) $this->personnelForm['last_name'];
        $personnel->name = trim($personnel->first_name . ' ' . $personnel->middle_name . ' ' . $personnel->last_name);
        $personnel->email = (string) $this->personnelForm['email'];
        $personnel->phone = (string) $this->personnelForm['phone'];
        $personnel->company_id = getUserCompany();
        $personnel->location_id = getCurrentUserLocation()->id;
        $personnel->department_id = (string) $this->personnelForm['department'];
        $personnel->designation = (string) $this->personnelForm['designation'];
        $personnel->position = (string) $this->personnelForm['position'];
        $personnel->education_level = $this->personnelForm['educational_level'] !== '' ? (string) $this->personnelForm['educational_level'] : null;
        $personnel->employment_date = $this->personnelForm['employment_date'] !== '' ? (string) $this->personnelForm['employment_date'] : null;
        $personnel->date_of_birth = $this->personnelForm['date_of_birth'] !== '' ? (string) $this->personnelForm['date_of_birth'] : null;
        $personnel->analyst_is_gazzetted = (bool) $this->personnelForm['analyst_is_gazzetted'];
        $personnel->date_of_gazzette = $personnel->analyst_is_gazzetted && $this->personnelForm['date_of_gazzette'] !== ''
            ? (string) $this->personnelForm['date_of_gazzette']
            : null;
        $personnel->start_of_career = $this->personnelForm['start_of_career'] !== '' ? (string) $this->personnelForm['start_of_career'] : null;
        $personnel->id_number = (string) $this->personnelForm['id_number'];
        $personnel->zone_id = $this->zonesTableAvailable && $this->personnelForm['zone_id'] !== ''
            ? (string) $this->personnelForm['zone_id']
            : null;
        $personnel->active = $this->personnelForm['active'] ? 1 : 0;
        $personnel->lab_section_id = implode(',', $this->personnelForm['lab_section_id'] ?? []);
        $personnel->license_type = (string) $this->personnelForm['user_license'];
        $personnel->password = bcrypt($personnel->first_name . config('app.name') . date('Y'));

        if ($this->signatureUpload) {
            $filename = Str::uuid()->toString() . '_' . time() . '.' . $this->signatureUpload->getClientOriginalExtension();
            $storedPath = $this->signatureUpload->storeAs('personnel-signature', $filename, 'public');
            $personnel->electronic_sig = '/storage/' . $storedPath;
        }

        if ($this->signatureData !== '' && str_starts_with($this->signatureData, 'data:image/')) {
            if (preg_match('/^data:image\/(\w+);base64,/', $this->signatureData, $matches)) {
                $extension = strtolower($matches[1]);
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                if (in_array($extension, ['png', 'jpg', 'gif', 'webp'], true)) {
                    $imageData = substr($this->signatureData, strpos($this->signatureData, ',') + 1);
                    $decoded = base64_decode($imageData, true);

                    if ($decoded !== false) {
                        $filename = Str::uuid()->toString() . '_' . time() . '.' . $extension;
                        $storagePath = 'personnel-signature/' . $filename;
                        Storage::disk('public')->put($storagePath, $decoded);
                        $personnel->electronic_sig = '/storage/' . $storagePath;
                    }
                }
            }
        }

        $personnel->save();

        if (Schema::hasTable('user_zone_relation')) {
            if ($this->personnelForm['zone_id'] !== '') {
                DB::table('user_zone_relation')->updateOrInsert(
                    ['user_id' => $personnel->id, 'zone_id' => (string) $this->personnelForm['zone_id']],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            } else {
                DB::table('user_zone_relation')->where('user_id', $personnel->id)->delete();
            }
        }

        if (Schema::hasTable('user_directorate_relation')) {
            if ($this->personnelForm['directorate_id'] !== '') {
                DB::table('user_directorate_relation')->updateOrInsert(
                    ['user_id' => $personnel->id, 'directorate_id' => (string) $this->personnelForm['directorate_id']],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            } else {
                DB::table('user_directorate_relation')->where('user_id', $personnel->id)->delete();
            }
        }

        if (Schema::hasTable('user_lab_relation')) {
            if ($this->personnelForm['lab_id'] !== '') {
                DB::table('user_lab_relation')->updateOrInsert(
                    ['user_id' => $personnel->id, 'lab_id' => (string) $this->personnelForm['lab_id']],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            } else {
                DB::table('user_lab_relation')->where('user_id', $personnel->id)->delete();
            }
        }

        $this->licenseCount = $this->getUsersByLicense();
        $this->message = 'Personnel added successfully.';
        $this->messageType = 'success';
        $this->showAddPersonnelModal = false;
        $this->resetPersonnelForm();
        $this->resetPage();
    }

    public function openStateModal(string $personnelId): void
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
            'selectedPersonnelId' => 'required|string|exists:users,id',
            'stateAction' => 'required|in:active,deactive',
        ]);

        $personnel = User::query()->findOrFail($this->selectedPersonnelId);
        $personnel->active = $this->stateAction === 'active' ? 1 : 0;
        $personnel->save();

        $this->showStateModal = false;
        $this->message = 'Personnel state updated successfully.';
        $this->messageType = 'success';
    }

    public function openResetPasswordModal(string $personnelId): void
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
            'selectedPersonnelId' => 'required|string|exists:users,id',
            'newPassword' => 'required|string|min:6',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        $personnel = User::query()->findOrFail($this->selectedPersonnelId);
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

    public function exportToExcel()
    {
        $rows = $this->buildPersonnelQuery()
            ->orderBy('users.name')
            ->get();

        $data = $rows->map(static function ($item): array {
            return [
                'Designation' => (string) ($item->designation ?? ''),
                'First Name' => (string) ($item->first_name ?? ''),
                'Middle Name' => (string) ($item->middle_name ?? ''),
                'Last Name' => (string) ($item->last_name ?? ''),
                'Department' => (string) ($item->department_name ?? ''),
                'Job Description' => (string) ($item->position ?? ''),
                'Lab Sections' => (string) ($item->labsectionname ?? ''),
                'Email' => (string) ($item->email ?? ''),
                'Employment Date' => (string) ($item->employment_date ?? ''),
                'License Type' => (string) ($item->license_type ?? ''),
                'Active' => (int) ($item->active ?? 0) === 1 ? 'Yes' : 'No',
            ];
        })->toArray();

        $headings = [
            'Designation',
            'First Name',
            'Middle Name',
            'Last Name',
            'Department',
            'Job Description',
            'Lab Sections',
            'Email',
            'Employment Date',
            'License Type',
            'Active',
        ];

        return Excel::download(
            new ReportExporter($data, $headings),
            'personnel_' . now()->format('Y_m_d_H_i_s') . '.xlsx'
        );
    }

    public function getPersonnelProperty()
    {
        return $this->buildPersonnelQuery()
            ->orderBy('users.name')
            ->paginate($this->perPage);
    }

    private function buildPersonnelQuery()
    {
        $query = User::query()
            ->join('inventory_departments as d', function ($join): void {
                $join->whereRaw('d.id::text = users.department_id');
            })
            ->leftJoin('module_pre_configs as de', function ($join): void {
                $join->whereRaw('de.id::text = users.designation')
                    ->where('de.type', '=', 'Designation');
            })
            ->leftJoin('module_pre_configs as e', function ($join): void {
                $join->whereRaw('e.id::text = users.education_level')
                    ->where('e.type', '=', 'Educational Levels');
            })
            ->leftJoin('module_pre_configs as p', function ($join): void {
                $join->whereRaw('p.id::text = users.position::text')
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
            $query->where('users.department_id', $this->departmentFilter);
        }

        if ($this->designationFilter !== '') {
            $query->where('users.designation', $this->designationFilter);
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

        return $query;
    }

    public function render()
    {
        return view('livewire.personnel.personnel-table-manager');
    }

    /** @return array<int, array{id:string,name:string,zone_id:string}> */
    public function getAvailableDirectoratesProperty(): array
    {
        $selectedZoneId = (string) ($this->personnelForm['zone_id'] ?? '');

        return array_values(array_filter(
            $this->directorates,
            static fn (array $item): bool => $selectedZoneId === '' || (string) ($item['zone_id'] ?? '') === $selectedZoneId
        ));
    }

    /** @return array<int, array{id:string,name:string,directorate_id:string,zone_id:string}> */
    public function getAvailableLabsProperty(): array
    {
        $selectedZoneId = (string) ($this->personnelForm['zone_id'] ?? '');
        $selectedDirectorateId = (string) ($this->personnelForm['directorate_id'] ?? '');

        return array_values(array_filter(
            $this->labs,
            static function (array $item) use ($selectedZoneId, $selectedDirectorateId): bool {
                $matchesZone = $selectedZoneId === '' || (string) ($item['zone_id'] ?? '') === $selectedZoneId;
                $matchesDirectorate = $selectedDirectorateId === '' || (string) ($item['directorate_id'] ?? '') === $selectedDirectorateId;

                return $matchesZone && $matchesDirectorate;
            }
        ));
    }

    public function getExperienceYearsPreviewProperty(): string
    {
        $startOfCareer = (string) ($this->personnelForm['start_of_career'] ?? '');
        if ($startOfCareer === '') {
            return '--';
        }

        try {
            $startDate = now()->parse($startOfCareer);
        } catch (\Throwable $exception) {
            return '--';
        }

        $now = now();
        if ($startDate->greaterThan($now)) {
            return '0 years';
        }

        $diff = $startDate->diff($now);
        $years = max(0, (int) $diff->y);
        $months = max(0, (int) $diff->m);

        if ($years === 0 && $months === 0) {
            return '0 months';
        }

        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' year' . ($years === 1 ? '' : 's');
        }

        if ($months > 0) {
            $parts[] = $months . ' month' . ($months === 1 ? '' : 's');
        }

        return implode(' ', $parts);
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

    public function selectDesignation(string $id): void
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

    public function selectEducationLevel(string $id): void
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

    public function selectPosition(string $id): void
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

    public function selectDepartmentInput(string $id): void
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

    public function toggleLabSectionSelection(string $stageId): void
    {
        $selected = $this->personnelForm['lab_section_id'] ?? [];
        if (in_array($stageId, $selected, true)) {
            $this->personnelForm['lab_section_id'] = array_values(array_filter($selected, fn (string $id): bool => $id !== $stageId));
            return;
        }

        $selected[] = $stageId;
        $this->personnelForm['lab_section_id'] = array_values(array_unique($selected));
    }

    public function removeLabSectionSelection(string $stageId): void
    {
        $selected = $this->personnelForm['lab_section_id'] ?? [];
        $this->personnelForm['lab_section_id'] = array_values(array_filter($selected, fn (string $id): bool => $id !== $stageId));
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
            'date_of_birth' => '',
            'employment_date' => '',
            'educational_level' => '',
            'position' => '',
            'analyst_is_gazzetted' => false,
            'date_of_gazzette' => '',
            'start_of_career' => '',
            'department' => '',
            'zone_id' => '',
            'directorate_id' => '',
            'lab_id' => '',
            'lab_section_id' => [],
            'user_license' => '',
            'active' => true,
        ];
        $this->signatureUpload = null;
        $this->signatureData = '';
        $this->addPersonnelStep = 1;
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

    private function validateAddPersonnelStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'personnelForm.first_name' => 'required|string|max:255',
                'personnelForm.email' => 'required|email|max:255|unique:users,email',
                'personnelForm.id_number' => 'required|string|max:255',
                'personnelForm.date_of_birth' => 'nullable|date',
            ]);
        }

        if ($step === 2) {
            $this->validate([
                'personnelForm.designation' => [
                    'required',
                    'string',
                    Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Designation')),
                ],
                'personnelForm.position' => [
                    'required',
                    'string',
                    Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Job Description')),
                ],
                'personnelForm.department' => 'required|string|exists:inventory_departments,id',
            ]);
        }

        if ($step === 4) {
            $this->validate([
                'personnelForm.user_license' => 'required|string|max:255',
                'signatureUpload' => 'nullable|image|max:3072',
            ]);
        }
    }

    private function directorateBelongsToSelectedZone(string $directorateId): bool
    {
        $selectedZoneId = (string) ($this->personnelForm['zone_id'] ?? '');
        if ($selectedZoneId === '') {
            return true;
        }

        foreach ($this->directorates as $directorate) {
            if ((string) $directorate['id'] === $directorateId) {
                return (string) ($directorate['zone_id'] ?? '') === $selectedZoneId;
            }
        }

        return false;
    }

    private function labMatchesSelections(string $labId): bool
    {
        $selectedZoneId = (string) ($this->personnelForm['zone_id'] ?? '');
        $selectedDirectorateId = (string) ($this->personnelForm['directorate_id'] ?? '');

        foreach ($this->labs as $lab) {
            if ((string) $lab['id'] !== $labId) {
                continue;
            }

            $matchesZone = $selectedZoneId === '' || (string) ($lab['zone_id'] ?? '') === $selectedZoneId;
            $matchesDirectorate = $selectedDirectorateId === '' || (string) ($lab['directorate_id'] ?? '') === $selectedDirectorateId;

            return $matchesZone && $matchesDirectorate;
        }

        return false;
    }
}

