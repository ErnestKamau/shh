<?php

namespace App\Livewire\SkillsMatrix;

use App\InventoryDepartment;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\ModulePreConfigs;
use Livewire\Component;

class SkillsMatrixManager extends Component
{
    use HasMatrixFlash;
    use HasMatrixListFilters;

    public bool $showModal = false;

    public ?string $editingMatrixId = null;

    public string $name = '';

    public string $departmentId = '';

    public int $status = 1;

    public string $statusFilter = '';

    /** @var array<int, string> */
    public array $selectedRoleIds = [];

    public string $roleSearch = '';

    public bool $showRoleDropdown = false;

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function afterClearListFilters(): void
    {
        $this->statusFilter = '';
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $matrixId): void
    {
        $matrix = SkillsMatrix::findOrFail($matrixId);
        $this->editingMatrixId = $matrix->id;
        $this->name = $matrix->name;
        $this->departmentId = (string) $matrix->department_id;
        $this->status = (int) $matrix->status;
        $this->selectedRoleIds = SkillMarixRole::where('skills_matrix_id', $matrixId)->pluck('job_description_id')->map(fn ($id) => (string) $id)->toArray();
        $this->showModal = true;
    }

    public function saveMatrix(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'departmentId' => 'required',
            'selectedRoleIds' => 'required|array|min:1',
        ]);

        $matrix = $this->editingMatrixId
            ? SkillsMatrix::findOrFail($this->editingMatrixId)
            : new SkillsMatrix;

        $matrix->name = $this->name;
        $matrix->department_id = $this->departmentId;
        $matrix->status = $this->status;
        $matrix->save();

        if ($this->editingMatrixId) {
            $exist = $matrix->jobdescription['ids'] ?? [];
            $add = array_diff($this->selectedRoleIds, $exist);
            $delete = array_diff($exist, $this->selectedRoleIds);
            SkillMarixRole::where('skills_matrix_id', $matrix->id)->whereIn('job_description_id', $delete)->delete();
            foreach ($add as $roleId) {
                SkillMarixRole::create([
                    'skills_matrix_id' => $matrix->id,
                    'job_description_id' => $roleId,
                ]);
            }
        } else {
            foreach ($this->selectedRoleIds as $roleId) {
                SkillMarixRole::create([
                    'skills_matrix_id' => $matrix->id,
                    'job_description_id' => $roleId,
                ]);
            }
        }

        $this->showModal = false;
        $this->flash('Matrix saved successfully.');
    }

    protected function resetForm(): void
    {
        $this->editingMatrixId = null;
        $this->name = '';
        $this->departmentId = '';
        $this->status = 1;
        $this->selectedRoleIds = [];
    }

    public function render()
    {
        $query = SkillsMatrix::withDepartmentName()->orderBy('skillsmatrices.name');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('skillsmatrices.name', 'like', $term)
                    ->orWhere('c.name', 'like', $term);
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('skillsmatrices.status', (int) $this->statusFilter);
        }

        $matrices = $query->paginate($this->perPage);

        $departments = InventoryDepartment::where('company_id', getUserCompany())
            ->where('module', 'organizational')
            ->where('location_id', getCurrentUserLocation()->id)
            ->orderBy('name')
            ->get();

        $roles = ModulePreConfigs::where('type', 'Job Description')->orderBy('name')->get();

        return view('livewire.skills-matrix.skills-matrix-manager', compact('matrices', 'departments', 'roles'));
    }
}
