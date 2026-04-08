<?php

namespace App\Livewire\Personnel;

use App\User;
use Livewire\Component;
use Livewire\WithPagination;

class PersonnelTableManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'active';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
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

        return $query->orderBy('users.name')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.personnel-table-manager');
    }
}

