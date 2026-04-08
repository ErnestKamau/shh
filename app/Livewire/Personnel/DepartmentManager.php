<?php

namespace App\Livewire\Personnel;

use App\InventoryDepartment;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showDepartmentModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingDepartmentId = null;
    public string $departmentName = '';
    public bool $departmentActive = true;
    public string $message = '';
    public string $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingDepartmentId = null;
        $this->departmentName = '';
        $this->departmentActive = true;
        $this->showDepartmentModal = true;
    }

    public function openEditModal(int $departmentId): void
    {
        $department = InventoryDepartment::query()
            ->where('company_id', getUserCompany())
            ->where('module', 'organizational')
            ->where('location_id', getCurrentUserLocation()->id)
            ->findOrFail($departmentId);

        $this->editingDepartmentId = $department->id;
        $this->departmentName = (string) $department->name;
        $this->departmentActive = (int) $department->active === 1;
        $this->showDepartmentModal = true;
    }

    public function closeDepartmentModal(): void
    {
        $this->showDepartmentModal = false;
    }

    public function saveDepartment(): void
    {
        $this->validate([
            'departmentName' => 'required|string|max:255',
            'departmentActive' => 'boolean',
        ]);

        if ($this->editingDepartmentId) {
            $department = InventoryDepartment::query()->findOrFail($this->editingDepartmentId);
        } else {
            $department = new InventoryDepartment();
            $department->company_id = getUserCompany();
            $department->location_id = getCurrentUserLocation()->id;
            $department->module = 'organizational';
        }

        $department->name = $this->departmentName;
        $department->active = $this->departmentActive ? 1 : 0;
        $department->save();

        $this->showDepartmentModal = false;
        $this->message = $this->editingDepartmentId ? 'Department updated successfully.' : 'Department added successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function openDeleteModal(int $departmentId): void
    {
        $department = InventoryDepartment::query()
            ->where('company_id', getUserCompany())
            ->where('module', 'organizational')
            ->where('location_id', getCurrentUserLocation()->id)
            ->findOrFail($departmentId);

        $this->editingDepartmentId = $department->id;
        $this->departmentName = (string) $department->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteDepartment(): void
    {
        $this->validate([
            'editingDepartmentId' => 'required|integer',
        ]);

        $department = InventoryDepartment::query()
            ->where('company_id', getUserCompany())
            ->where('module', 'organizational')
            ->where('location_id', getCurrentUserLocation()->id)
            ->findOrFail((int) $this->editingDepartmentId);

        $department->delete();
        $this->showDeleteModal = false;
        $this->message = 'Department deleted successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function getDepartmentsProperty()
    {
        $query = InventoryDepartment::query()
            ->where('company_id', getUserCompany())
            ->where('module', 'organizational')
            ->where('location_id', getCurrentUserLocation()->id)
            ->orderBy('name');

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.department-manager');
    }
}
