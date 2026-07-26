<?php

namespace App\Livewire\Personnel;

use App\Exports\ReportExporter;
use App\InventoryDepartment;
use App\Lab;
use App\Models\Auth\Role;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\User;
use App\UserLabRelation;
use App\Mail\PersonnelWelcomeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Services\Personnel\PersonnelSignatureService;
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
    public string $labFilter = '';
    public string $employmentDateFrom = '';
    public string $employmentDateTo = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showAdvancedFilters = false;
    public bool $showAddPersonnelModal = false;
    public bool $showStateModal = false;
    public bool $showBulkDeactivateModal = false;
    public bool $showResetPasswordModal = false;
    /** @var array<int, string> */
    public array $selectedPersonnel = [];
    public bool $selectAll = false;
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
    /** @var array<int, array{id:string,name:string}> */
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

        // Designation options come from Job Description configs (sidebar: Job Designation).
        // Prefer description (full title) when present; name can be a short code.
        $this->designations = ModulePreConfigs::query()
            ->where('type', 'Job Description')
            ->whereIn('module', ['Personnel-Management', 'Skills-Matrix'])
            ->orderBy('name')
            ->get(['id', 'name', 'description'])
            ->map(fn ($item): array => [
                'id' => (string) $item->id,
                'name' => $this->displayLabel((string) ($item->description ?? ''), (string) $item->name),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->toArray();

        // Position options come from organizational Roles.
        // Prefer description (full title) when present; name can be a short code (e.g. "admin").
        $this->positions = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name', 'description'])
            ->map(fn ($item): array => [
                'id' => (string) $item->id,
                'name' => $this->displayLabel((string) ($item->description ?? ''), (string) $item->name),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->toArray();
        $this->educationLevels = ModulePreConfigs::query()
            ->where('type', 'Educational Levels')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (string) $item->id, 'name' => (string) $item->name])
            ->toArray();
        $this->labsTableAvailable = Schema::hasTable('labs');
        $this->labs = $this->labsTableAvailable
            ? Lab::query()
                ->where('active', 1)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($item): array => [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                ])
                ->toArray()
            : [];
        $this->stages = SampleAnalysisStage::query()
            ->where('active', 1)
            ->where('is_sample_stage', 0)
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

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'personnelForm.first_name' => 'first name',
            'personnelForm.middle_name' => 'middle name',
            'personnelForm.last_name' => 'last name',
            'personnelForm.email' => 'email',
            'personnelForm.phone' => 'phone',
            'personnelForm.id_number' => 'ID number',
            'personnelForm.date_of_birth' => 'date of birth',
            'personnelForm.employment_date' => 'employment date',
            'personnelForm.educational_level' => 'educational level',
            'personnelForm.designation' => 'designation/job description',
            'personnelForm.position' => 'position/role',
            'personnelForm.department' => 'department',
            'personnelForm.date_of_gazzette' => 'date of gazzette',
            'personnelForm.gazzette_no' => 'gazzette number',
            'personnelForm.start_of_career' => 'start of career',
            'personnelForm.lab_section_id' => 'lab section',
            'signatureUpload' => 'signature upload',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'personnelForm.email.unique' => 'This email is already registered to another user.',
        ];
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
                Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Job Description')),
            ],
            'personnelForm.first_name' => 'required|string|max:255',
            'personnelForm.middle_name' => 'nullable|string|max:255',
            'personnelForm.last_name' => 'nullable|string|max:255',
            'personnelForm.email' => 'required|email|max:255|unique:users,email',
            'personnelForm.phone' => 'nullable|string|max:255',
            'personnelForm.id_number' => 'nullable|string|max:255',
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
                Rule::exists('spatie_roles', 'id'),
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
        $personnel->name = trim(implode(' ', array_filter([
            trim((string) $this->personnelForm['first_name']),
            trim((string) $this->personnelForm['middle_name']),
            trim((string) $this->personnelForm['last_name']),
        ], fn (string $part): bool => $part !== '')));
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
        $personnel->id_number = trim((string) $this->personnelForm['id_number']) !== ''
            ? trim((string) $this->personnelForm['id_number'])
            : null;
        $personnel->zone_id = null;
        $personnel->active = $this->personnelForm['active'] ? 1 : 0;
        $personnel->is_technical = ($this->personnelForm['is_technical'] ?? false) ? 1 : 0;
        $personnel->lab_section_id = implode(',', $this->personnelForm['lab_section_id'] ?? []);
        $plainPassword = $personnel->first_name . config('app.name') . date('Y');
        $personnel->password              = bcrypt($plainPassword);
        $personnel->password_changed_at   = null; // force change on first login

        app(PersonnelSignatureService::class)->applyToUser(
            $personnel,
            $this->signatureUpload,
            $this->signatureData,
        );

        $personnel->save();

        $selectedRole = Role::query()
            ->where('guard_name', 'web')
            ->find($this->personnelForm['position']);

        if ($selectedRole) {
            $personnel->assignRole($selectedRole);
        }

        $selectedLabIds = array_values(array_unique(array_filter((array) ($this->personnelForm['lab_ids'] ?? []))));

        UserLabRelation::where('user_id', $personnel->id)->delete();
        foreach ($selectedLabIds as $labId) {
            UserLabRelation::create([
                'user_id' => $personnel->id,
                'lab_id'  => (string) $labId,
            ]);
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
        if (! $this->canDeactivatePersonnel()) {
            $this->message = __('personnel.unauthorized_deactivate');
            $this->messageType = 'error';

            return;
        }

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
        if (! $this->canDeactivatePersonnel()) {
            $this->message = __('personnel.unauthorized_deactivate');
            $this->messageType = 'error';

            return;
        }

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

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedPersonnel = $this->personnel->pluck('id')->map(fn ($id): string => (string) $id)->toArray();
        } else {
            $this->selectedPersonnel = [];
        }
    }

    public function updatedSelectedPersonnel(): void
    {
        $this->selectAll = count($this->selectedPersonnel) === $this->personnel->count()
            && $this->personnel->count() > 0;
    }

    public function openBulkDeactivateModal(): void
    {
        if (! $this->canDeactivatePersonnel()) {
            $this->message = __('personnel.unauthorized_deactivate');
            $this->messageType = 'error';

            return;
        }

        if ($this->selectedPersonnel === []) {
            $this->message = __('personnel.select_personnel_first');
            $this->messageType = 'error';

            return;
        }

        $this->showBulkDeactivateModal = true;
    }

    public function closeBulkDeactivateModal(): void
    {
        $this->showBulkDeactivateModal = false;
    }

    public function bulkDeactivate(): void
    {
        if (! $this->canDeactivatePersonnel()) {
            $this->message = __('personnel.unauthorized_deactivate');
            $this->messageType = 'error';

            return;
        }

        $ids = array_values(array_filter($this->selectedPersonnel));

        if ($ids === []) {
            $this->message = __('personnel.select_personnel_first');
            $this->messageType = 'error';

            return;
        }

        $ids = array_values(array_diff($ids, [(string) auth()->id()]));

        if ($ids === []) {
            $this->message = __('personnel.cannot_deactivate_self');
            $this->messageType = 'error';
            $this->showBulkDeactivateModal = false;

            return;
        }

        $updatedCount = User::query()
            ->whereIn('id', $ids)
            ->where('active', 1)
            ->update(['active' => 0]);

        $this->selectedPersonnel = [];
        $this->selectAll = false;
        $this->showBulkDeactivateModal = false;
        $this->message = $updatedCount > 0
            ? __('personnel.bulk_deactivated_success', ['count' => $updatedCount])
            : __('personnel.bulk_deactivated_none');
        $this->messageType = $updatedCount > 0 ? 'success' : 'error';
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
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingLabFilter(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingEmploymentDateFrom(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingEmploymentDateTo(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['all', 'active', 'deactive', 'dormant'], true) ? $tab : 'all';
        $this->clearSelection();
        $this->resetPage();
    }

    private function clearSelection(): void
    {
        $this->selectedPersonnel = [];
        $this->selectAll = false;
    }

    private function canDeactivatePersonnel(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('personnel.personnel.edit') || $user->CheckDeactivatePersonnel();
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = !$this->showAdvancedFilters;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->labFilter = '';
        $this->employmentDateFrom = '';
        $this->employmentDateTo = '';
        $this->clearSelection();
        $this->resetPage();
    }

    public function exportToExcel()
    {
        $rows = $this->buildPersonnelQuery()
            ->orderBy('users.name')
            ->get();

        $this->hydratePersonnelDisplayLabels($rows);

        $data = $rows->map(static function ($item): array {
            return [
                'Designation' => (string) ($item->designation_label ?? ''),
                'First Name' => (string) ($item->first_name ?? ''),
                'Middle Name' => (string) ($item->middle_name ?? ''),
                'Last Name' => (string) ($item->last_name ?? ''),
                'Department' => (string) ($item->department_name ?? ''),
                'Position' => (string) ($item->position_label ?? ''),
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
            'Position',
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
        $paginator = $this->buildPersonnelQuery()
            ->orderBy('users.name')
            ->paginate($this->perPage);

        $this->hydratePersonnelDisplayLabels($paginator->getCollection());

        return $paginator;
    }

    private function buildPersonnelQuery()
    {
        $query = User::query()
            ->leftJoin('inventory_departments as d', function ($join): void {
                $join->whereRaw('d.id::text = users.department_id');
            })
            ->leftJoin('module_pre_configs as e', function ($join): void {
                $join->whereRaw('e.id::text = users.education_level')
                    ->where('e.type', '=', 'Educational Levels');
            })
            ->leftJoin('spatie_roles as p', function ($join): void {
                $join->whereRaw('p.id::text = users.position::text');
            })
            ->selectRaw("
                users.*,
                d.name as department_name,
                e.name as education,
                COALESCE(NULLIF(TRIM(p.description), ''), p.name) as position_label
            ")
            ->where(function ($builder): void {
                $builder->where('users.is_client', 0)->orWhereNull('users.is_client');
            })
            ->where(function ($builder): void {
                $builder->where('users.is_tablet', 0)->orWhereNull('users.is_tablet');
            })
            ->whereNull('users.crm_contact_id')
            ->whereNull('users.crmcontact_id');

        $companyId = getUserCompany();
        if ($companyId) {
            $query->where('users.company_id', $companyId);
        }

        if ($this->activeTab === 'deactive') {
            $query->where('users.active', 0);
        } elseif ($this->activeTab === 'dormant') {
            // Dormant accounts are a subset of active accounts.
            $query->where('users.active', 1);

            $ninetyDaysAgo = now()->subDays(90);
            
            $activeUserIds = \App\Models\Audit::where('created_at', '>=', $ninetyDaysAgo)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $query->whereNotIn('users.id', $activeUserIds)
                ->where('users.created_at', '<', $ninetyDaysAgo);
        } else {
            // "All" and "Active" must exclude deactivated accounts.
            $query->where('users.active', 1);
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
                    ->orWhere('p.description', 'like', $searchText);
            });
        }

        if ($this->labFilter !== '' && Schema::hasTable('user_lab_relation')) {
            $labId = $this->labFilter;
            $query->whereExists(function ($subQuery) use ($labId): void {
                $subQuery->selectRaw('1')
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
                'personnelForm.id_number' => 'nullable|string|max:255',
                'personnelForm.date_of_birth' => 'nullable|date',
            ]);
        }

        if ($step === 2) {
            $this->validate([
                'personnelForm.designation' => [
                    'required',
                    'string',
                    Rule::exists('module_pre_configs', 'id')->where(fn ($query) => $query->where('type', 'Job Description')),
                ],
                'personnelForm.position' => [
                    'required',
                    'string',
                    Rule::exists('spatie_roles', 'id'),
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

    /**
     * Prefer a longer description/title when available; fall back to the short name/code.
     */
    private function displayLabel(string $description, string $name): string
    {
        $description = trim($description);
        $name = trim($name);

        return $description !== '' ? $description : $name;
    }

    /**
     * Resolve full designation/role labels after Eloquent decrypts encrypted designation IDs.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $rows
     */
    private function hydratePersonnelDisplayLabels($rows): void
    {
        $designationIds = $rows
            ->map(fn (User $row): string => is_string($row->designation) ? $row->designation : '')
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values();

        $designationLabels = ModulePreConfigs::query()
            ->whereIn('id', $designationIds)
            ->where('type', 'Job Description')
            ->get(['id', 'name', 'description'])
            ->mapWithKeys(fn (ModulePreConfigs $item): array => [
                (string) $item->id => $this->displayLabel((string) ($item->description ?? ''), (string) $item->name),
            ]);

        foreach ($rows as $row) {
            $designationId = is_string($row->designation) ? $row->designation : '';
            $row->setAttribute(
                'designation_label',
                $designationId !== '' ? (string) ($designationLabels[$designationId] ?? '') : ''
            );

            $positionLabel = trim((string) ($row->position_label ?? ''));
            if ($positionLabel === '') {
                $positionLabel = trim((string) ($row->getAttributes()['position_label'] ?? ''));
            }
            $row->setAttribute('position_label', $positionLabel);
        }
    }
}

