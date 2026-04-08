<?php

namespace App\Livewire\Personnel;

use App\InventoryDepartment;
use App\ModulePreConfigs;
use App\User;
use Livewire\Component;
use Livewire\WithPagination;

class PersonnelTableManager extends Component
{
    use WithPagination;

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

    /** @var array<int, array{id:int,name:string}> */
    public array $departments = [];
    /** @var array<int, array{id:int,name:string}> */
    public array $designations = [];
    /** @var array<string,string> */
    public array $licenses = [];

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
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
}

