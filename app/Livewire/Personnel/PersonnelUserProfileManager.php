<?php

namespace App\Livewire\Personnel;

use App\InventoryDepartment;
use App\Lab;
use App\Models\Auth\Role;
use App\ModulePreConfigs;
use App\SampleAnalysisStage;
use App\Services\Personnel\PersonnelProfileService;
use App\User;
use App\UserLabRelation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class PersonnelUserProfileManager extends Component
{
    use WithFileUploads;

    public string $activeTab = 'details';
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
    public string $detailsKraPin = '';
    public string $detailsNssf = '';
    public string $detailsNhif = '';

    public $detailsSignatureUpload = null;
    public string $detailsSignatureData = '';
    public $photoUpload = null;

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

    public bool $updatePassword = false;
    public string $password = '';
    public string $passwordConfirmation = '';

    public string $message = '';
    public string $messageType = 'success';

    public function mount(): void
    {
        $this->loadFromUser($this->user);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['details', 'security'], true) ? $tab : 'details';
        if ($this->activeTab === 'details') {
            $this->detailsStep = 1;
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
        if (! $value) {
            $this->detailsDateOfGazzette = null;
            $this->detailsGazzetteNo = '';
        }
    }

    public function updatedUpdatePassword(bool $value): void
    {
        if (! $value) {
            $this->password = '';
            $this->passwordConfirmation = '';
        }
    }

    public function closeSelectDropdowns(): void
    {
        $this->showDesignationDropdown = false;
        $this->showEducationDropdown = false;
        $this->showPositionDropdown = false;
        $this->showDepartmentDropdown = false;
        $this->showLabDropdown = false;
        $this->showLabSectionDropdown = false;
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

    public function searchLabSection(): void
    {
        $this->showLabSectionDropdown = true;
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

    public function selectLab(string $id): void
    {
        if (in_array($id, $this->selectedLabIds, true)) {
            $this->selectedLabIds = array_values(array_filter(
                $this->selectedLabIds,
                fn (string $labId): bool => $labId !== $id
            ));
        } else {
            $this->selectedLabIds[] = $id;
            $this->selectedLabIds = array_values(array_unique($this->selectedLabIds));
        }

        $this->labSearch = '';
    }

    public function selectLabSection(string $id): void
    {
        if (in_array($id, $this->selectedLabSectionIds, true)) {
            $this->selectedLabSectionIds = array_values(array_filter(
                $this->selectedLabSectionIds,
                fn (string $sectionId): bool => $sectionId !== $id
            ));
        } else {
            $this->selectedLabSectionIds[] = $id;
            $this->selectedLabSectionIds = array_values(array_unique($this->selectedLabSectionIds));
        }

        $this->labSectionSearch = '';
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

    public function clearLab(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedLabIds = [];

            return;
        }

        $this->selectedLabIds = array_values(array_filter(
            $this->selectedLabIds,
            fn (string $labId): bool => $labId !== $id
        ));
    }

    public function clearLabSection(?string $id = null): void
    {
        if ($id === null) {
            $this->selectedLabSectionIds = [];

            return;
        }

        $this->selectedLabSectionIds = array_values(array_filter(
            $this->selectedLabSectionIds,
            fn (string $sectionId): bool => $sectionId !== $id
        ));
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function saveProfile(PersonnelProfileService $profileService): void
    {
        $this->validateDetailsStep(1);
        $this->validateDetailsStep(2);
        $this->validateDetailsStep(3);

        $rules = [
            'detailsEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id, 'id')],
            'detailsSignatureUpload' => 'nullable|image|max:3072',
            'detailsSignatureData' => 'nullable|string',
            'photoUpload' => 'nullable|image|max:3072',
        ];

        if ($this->updatePassword) {
            $rules['password'] = 'required|string|min:8|same:passwordConfirmation';
            $rules['passwordConfirmation'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $profileService->update(
            $this->user,
            [
                'first_name' => $this->detailsFirstName,
                'middle_name' => $this->detailsMiddleName,
                'last_name' => $this->detailsLastName,
                'email' => $this->detailsEmail,
                'phone' => $this->detailsPhone,
                'id_number' => $this->detailsIdNumber,
                'date_of_birth' => $this->detailsDateOfBirth,
                'employment_date' => $this->detailsEmploymentDate,
                'designation' => $this->selectedDesignationId,
                'education_level' => $this->selectedEducationId,
                'position' => $this->selectedPositionId,
                'department_id' => $this->selectedDepartmentId,
                'analyst_is_gazzetted' => $this->detailsAnalystIsGazzetted,
                'date_of_gazzette' => $this->detailsDateOfGazzette,
                'gazzette_no' => $this->detailsGazzetteNo,
                'start_of_career' => $this->detailsStartOfCareer,
                'lab_section_ids' => $this->selectedLabSectionIds,
                'lab_ids' => $this->selectedLabIds,
                'kra_pin' => $this->detailsKraPin,
                'nssf' => $this->detailsNssf,
                'nhif' => $this->detailsNhif,
            ],
            $this->detailsSignatureUpload,
            $this->detailsSignatureData,
            $this->photoUpload,
            $this->updatePassword ? $this->password : null,
        );

        $this->detailsSignatureUpload = null;
        $this->detailsSignatureData = '';
        $this->photoUpload = null;
        $this->updatePassword = false;
        $this->password = '';
        $this->passwordConfirmation = '';

        unset($this->user);
        $this->loadFromUser($this->user);

        $this->message = 'Profile saved. Your personnel record is up to date.';
        $this->messageType = 'success';
        $this->dispatch('notify', type: 'success', message: $this->message);
    }

    public function getUserProperty(): User
    {
        return User::query()->findOrFail(Auth::id());
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

        $departments = (clone $query)->orderBy('name')->get(['id', 'name']);

        if ($departments->isNotEmpty()) {
            return $departments;
        }

        return InventoryDepartment::query()->orderBy('name')->get(['id', 'name']);
    }

    public function getLabsProperty()
    {
        return Lab::query()->where('active', 1)->orderBy('name')->get(['id', 'name']);
    }

    public function getLabSectionsProperty()
    {
        return SampleAnalysisStage::query()
            ->where('active', 1)
            ->where('is_sample_stage', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    public function render()
    {
        return view('livewire.personnel.personnel-user-profile-manager');
    }

    private function loadFromUser(User $user): void
    {
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

        $this->detailsFirstName = (string) ($user->first_name ?? '');
        $this->detailsMiddleName = (string) ($user->middle_name ?? '');
        $this->detailsLastName = (string) ($user->last_name ?? '');
        $this->detailsEmail = (string) ($user->email ?? '');
        $this->detailsPhone = (string) ($user->phone ?? '');
        $this->detailsIdNumber = (string) ($user->id_number ?? '');
        $this->detailsDateOfBirth = ! empty($user->date_of_birth) ? date('Y-m-d', strtotime((string) $user->date_of_birth)) : null;
        $this->detailsEmploymentDate = ! empty($user->employment_date) ? date('Y-m-d', strtotime((string) $user->employment_date)) : null;
        $this->detailsAnalystIsGazzetted = (bool) ($user->analyst_is_gazzetted ?? false);
        $this->detailsDateOfGazzette = ! empty($user->date_of_gazzette) ? date('Y-m-d', strtotime((string) $user->date_of_gazzette)) : null;
        $this->detailsGazzetteNo = (string) ($user->gazzette_no ?? '');
        $this->detailsStartOfCareer = ! empty($user->start_of_career) ? date('Y-m-d', strtotime((string) $user->start_of_career)) : null;
        $this->detailsKraPin = (string) ($user->kra_pin ?? '');
        $this->detailsNssf = (string) ($user->nssf ?? '');
        $this->detailsNhif = (string) ($user->nhif ?? '');
    }

    private function validateDetailsStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'detailsFirstName' => 'required|string|max:255',
                'detailsMiddleName' => 'nullable|string|max:255',
                'detailsLastName' => 'nullable|string|max:255',
                'detailsEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id, 'id')],
                'detailsPhone' => 'nullable|string|max:255',
                'detailsIdNumber' => 'required|string|max:255',
                'detailsDateOfBirth' => 'nullable|date',
                'detailsKraPin' => 'nullable|string|max:255',
                'detailsNssf' => 'nullable|string|max:255',
                'detailsNhif' => 'nullable|string|max:255',
            ]);

            return;
        }

        if ($step === 2) {
            $this->validate([
                'detailsEmploymentDate' => 'nullable|date',
                'selectedDesignationId' => [
                    'required',
                    'string',
                    Rule::exists('module_pre_configs', 'id')->where(
                        fn ($query) => $query->where('type', 'Job Description')
                    ),
                ],
                'selectedEducationId' => [
                    'nullable',
                    'string',
                    Rule::exists('module_pre_configs', 'id')->where(
                        fn ($query) => $query->where('type', 'Educational Levels')
                    ),
                ],
                'selectedPositionId' => [
                    'nullable',
                    'string',
                    Rule::exists('spatie_roles', 'id'),
                ],
                'selectedDepartmentId' => 'nullable|string|exists:inventory_departments,id',
                'selectedLabIds' => 'array',
                'selectedLabIds.*' => 'string|exists:labs,id',
                'selectedLabSectionIds' => 'array',
                'selectedLabSectionIds.*' => 'string|exists:sample_analysis_stages,id',
            ]);

            return;
        }

        $this->validate([
            'detailsAnalystIsGazzetted' => 'boolean',
            'detailsDateOfGazzette' => 'nullable|required_if:detailsAnalystIsGazzetted,true|date',
            'detailsGazzetteNo' => 'nullable|string|max:255',
            'detailsStartOfCareer' => 'nullable|date',
        ]);
    }
}
