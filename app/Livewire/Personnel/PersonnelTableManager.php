<?php

namespace App\Livewire\Personnel;

use App\Directorate;
use App\Exports\ReportExporter;
use App\InventoryDepartment;
use App\Lab;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\User;
use App\UserDirectorateRelation;
use App\UserLabRelation;
use App\UserZoneRelation;
use App\Zone;
use App\Mail\PersonnelWelcomeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
    public string $activeTab = 'all';
    public string $zoneFilter = '';
    public string $directorateFilter = '';
    public string $labFilter = '';
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
        'gazzette_no' => '',
        'start_of_career' => '',
        'department' => '',
        'lab_ids' => [],
        'lab_section_id' => [],
        'active' => true,
        'is_technical' => false,
    ];

    public string $message = '';
    public string $messageType = 'success';
    public string $designationSearch = '';
    public string $educationLevelSearch = '';
    public string $positionSearch = '';
    public string $departmentSearchInput = '';
    public string $labSectionSearch = '';
    public bool $showDesignationDropdown = false;
    public bool $showEducationLevelDropdown = false;
    public bool $showPositionDropdown = false;
    public bool $showDepartmentDropdown = false;
    public bool $showLabSectionDropdown = false;
    public string $labSearch = '';
    public bool $showLabDropdown = false;
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredLabs = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredDesignations = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredEducationLevels = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredPositions = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredDepartments = [];
    /** @var array<int, array{id:string,name:string}> */
    public array $filteredLabSections = [];

    protected $paginationTheme = 'bootstrap';

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;
        $this->reloadDepartments();

        $this->designations = ModulePreConfigs::query()
            ->where('type', 'Designation')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();

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
    }

    #[On('personnel-open-add-modal')]
    public function openAddPersonnelModal(): void
    {
        $this->resetPersonnelForm();
        $this->primeAddModalDropdowns();
        $this->addPersonnelStep = 1;
        $this->showAddPersonnelModal = true;
    }

    #[On('personnel-departments-updated')]
    public function handleDepartmentsUpdated(): void
    {
        $this->reloadDepartments();
        $this->filteredDepartments = $this->departments;
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
            $this->personnelForm['gazzette_no'] = '';
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
            'personnelForm.gazzette_no' => 'nullable|string|max:255',
            'personnelForm.start_of_career' => 'nullable|date',
            'personnelForm.department' => 'required|string|exists:inventory_departments,id',
            'personnelForm.lab_section_id' => 'array',
            'personnelForm.active' => 'boolean',
            'personnelForm.is_technical' => 'boolean',
            'signatureUpload' => 'nullable|image|max:3072',
            'signatureData' => 'nullable|string',
        ];

        $this->validate($validationRules);

        if ($this->personnelForm['analyst_is_gazzetted'] && $this->personnelForm['date_of_gazzette'] === '') {
            $this->addError('personnelForm.date_of_gazzette', 'The date of gazzette field is required when analyst is gazzetted.');
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
        $personnel->gazzette_no = $personnel->analyst_is_gazzetted && trim((string) $this->personnelForm['gazzette_no']) !== ''
            ? trim((string) $this->personnelForm['gazzette_no'])
            : null;
        $personnel->start_of_career = $this->personnelForm['start_of_career'] !== '' ? (string) $this->personnelForm['start_of_career'] : null;
        $personnel->id_number = (string) $this->personnelForm['id_number'];
        $personnel->zone_id = null;
        $personnel->active = $this->personnelForm['active'] ? 1 : 0;
        $personnel->is_technical = ($this->personnelForm['is_technical'] ?? false) ? 1 : 0;
        $personnel->lab_section_id = implode(',', $this->personnelForm['lab_section_id'] ?? []);
        $plainPassword = $personnel->first_name . config('app.name') . date('Y');
        $personnel->password              = bcrypt($plainPassword);
        $personnel->password_changed_at   = null; // force change on first login

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

        // Sync labs; derive zone and directorate relationships from the assigned labs
        $selectedLabIds = array_values(array_unique(array_filter((array) ($this->personnelForm['lab_ids'] ?? []))));

        UserLabRelation::where('user_id', $personnel->id)->delete();
        foreach ($selectedLabIds as $labId) {
            UserLabRelation::create([
                'user_id' => $personnel->id,
                'lab_id'  => (string) $labId,
            ]);
        }

        if (!empty($selectedLabIds) && $this->labsTableAvailable) {
            $assignedLabs   = collect($this->labs)->whereIn('id', $selectedLabIds);
            $derivedZoneIds = $assignedLabs->pluck('zone_id')->filter()->unique()->values()->all();
            $derivedDirIds  = $assignedLabs->pluck('directorate_id')->filter()->unique()->values()->all();

            UserZoneRelation::where('user_id', $personnel->id)->delete();
            foreach ($derivedZoneIds as $zoneId) {
                UserZoneRelation::create(['user_id' => $personnel->id, 'zone_id' => $zoneId]);
            }
            UserDirectorateRelation::where('user_id', $personnel->id)->delete();
            foreach ($derivedDirIds as $dirId) {
                UserDirectorateRelation::create(['user_id' => $personnel->id, 'directorate_id' => $dirId]);
            }
        } else {
            UserZoneRelation::where('user_id', $personnel->id)->delete();
            UserDirectorateRelation::where('user_id', $personnel->id)->delete();
        }

        $this->message = 'Personnel added successfully.';
        $this->messageType = 'success';
        $this->showAddPersonnelModal = false;
        $this->resetPersonnelForm();
        $this->resetPage();

        try {
            Mail::to($personnel->email)->send(
                new PersonnelWelcomeMail(
                    (string) $personnel->name,
                    (string) $personnel->email,
                    $plainPassword
                )
            );
        } catch (\Throwable $e) {
            Log::error('PersonnelWelcomeMail failed: ' . $e->getMessage());
        }
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
        $personnel->password             = bcrypt($this->newPassword);
        $personnel->password_changed_at  = null; // force change on next login after admin reset
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

    public function updatingZoneFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDirectorateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingLabFilter(): void
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
        $this->activeTab = in_array($tab, ['all', 'active', 'deactive', 'dormant'], true) ? $tab : 'all';
        $this->resetPage();
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->zoneFilter = '';
        $this->directorateFilter = '';
        $this->labFilter = '';
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
            ->leftJoin('inventory_departments as d', function ($join): void {
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
            ->selectRaw('users.*, d.name as department_name, p.name as position, e.name as education, de.name as designation');

        if ($this->activeTab === 'active') {
            $query->where('users.active', 1);
        } elseif ($this->activeTab === 'deactive') {
            $query->where('users.active', 0);
        } elseif ($this->activeTab === 'dormant') {
            $ninetyDaysAgo = now()->subDays(90);
            
            $activeUserIds = \App\Models\Audit::where('created_at', '>=', $ninetyDaysAgo)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $query->whereNotIn('users.id', $activeUserIds)
                ->where('users.created_at', '<', $ninetyDaysAgo);
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

        if ($this->zoneFilter !== '') {
            $zoneId = $this->zoneFilter;

            $query->where(function ($builder) use ($zoneId): void {
                $builder->where('users.zone_id', $zoneId);

                if (Schema::hasTable('user_zone_relation')) {
                    $builder->orWhereExists(function ($subQuery) use ($zoneId): void {
                        $subQuery->select(DB::raw(1))
                            ->from('user_zone_relation as uzr')
                            ->whereColumn('uzr.user_id', 'users.id')
                            ->where('uzr.zone_id', $zoneId);
                    });
                }
            });
        }

        if ($this->directorateFilter !== '' && Schema::hasTable('user_directorate_relation')) {
            $directorateId = $this->directorateFilter;
            $query->whereExists(function ($subQuery) use ($directorateId): void {
                $subQuery->select(DB::raw(1))
                    ->from('user_directorate_relation as udr')
                    ->whereColumn('udr.user_id', 'users.id')
                    ->where('udr.directorate_id', $directorateId);
            });
        }

        if ($this->labFilter !== '' && Schema::hasTable('user_lab_relation')) {
            $labId = $this->labFilter;
            $query->whereExists(function ($subQuery) use ($labId): void {
                $subQuery->select(DB::raw(1))
                    ->from('user_lab_relation as ulr')
                    ->whereColumn('ulr.user_id', 'users.id')
                    ->where('ulr.lab_id', $labId);
            });
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

    public function selectDesignation($id): void
    {
        $this->personnelForm['designation'] = (string) $id;
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

    public function selectEducationLevel($id): void
    {
        $this->personnelForm['educational_level'] = (string) $id;
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

    public function selectPosition($id): void
    {
        $this->personnelForm['position'] = (string) $id;
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

    public function selectDepartmentInput($id): void
    {
        $this->personnelForm['department'] = (string) $id;
        $this->departmentSearchInput = '';
        $this->showDepartmentDropdown = false;
    }

    public function clearDepartmentInput(): void
    {
        $this->personnelForm['department'] = '';
    }

    public function searchLabs(): void
    {
        $this->showLabDropdown = true;
        $search = trim($this->labSearch);
        $this->filteredLabs = array_values(array_filter(
            $this->labs,
            fn (array $item): bool => $search === '' || stripos($item['name'], $search) !== false
        ));
    }

    public function selectLab(string $labId): void
    {
        $selected = $this->personnelForm['lab_ids'] ?? [];
        if (in_array($labId, $selected, true)) {
            return;
        }
        $selected[] = $labId;
        $this->personnelForm['lab_ids'] = array_values(array_unique($selected));
    }

    public function removeLabSelection(string $labId): void
    {
        $selected = $this->personnelForm['lab_ids'] ?? [];
        $this->personnelForm['lab_ids'] = array_values(
            array_filter($selected, fn (string $id): bool => $id !== $labId)
        );
    }

    public function selectAllLabs(): void
    {
        $this->personnelForm['lab_ids'] = array_column($this->labs, 'id');
        $this->labSearch = '';
        $this->showLabDropdown = false;
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
        $this->showLabSectionDropdown = false;
        $this->showLabDropdown = false;
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
            'gazzette_no' => '',
            'start_of_career' => '',
            'department' => '',
            'lab_ids' => [],
            'lab_section_id' => [],
            'active' => true,
            'is_technical' => false,
        ];
        $this->signatureUpload = null;
        $this->signatureData = '';
        $this->addPersonnelStep = 1;
        $this->resetAddModalSearches();
    }

    private function primeAddModalDropdowns(): void
    {
        $this->reloadDepartments();
        $this->filteredDesignations = $this->designations;
        $this->filteredEducationLevels = $this->educationLevels;
        $this->filteredPositions = $this->positions;
        $this->filteredDepartments = $this->departments;
        $this->filteredLabSections = $this->stages;
        $this->filteredLabs = $this->labs;
        $this->showLabDropdown = false;
    }

    private function reloadDepartments(): void
    {
        $companyId = getUserCompany();

        $query = InventoryDepartment::query()
            ->where('module', 'organizational')
            ->where(function ($builder) use ($companyId): void {
                if ($companyId) {
                    $builder->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                } else {
                    $builder->whereNull('company_id');
                }
            })
            ->orderBy('name');

        $departmentRows = $query->get(['id', 'name']);

        // Fallback: if organizational list is empty, use any company departments.
        if ($departmentRows->isEmpty() && $companyId) {
            $departmentRows = InventoryDepartment::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $this->departments = $departmentRows
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->values()
            ->toArray();
    }

    private function resetAddModalSearches(): void
    {
        $this->designationSearch = '';
        $this->educationLevelSearch = '';
        $this->positionSearch = '';
        $this->departmentSearchInput = '';
        $this->labSectionSearch = '';
        $this->labSearch = '';
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
                'signatureUpload' => 'nullable|image|max:3072',
            ]);
        }
    }
}

