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
    public ?string $editingDepartmentId = null;
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

    public function openEditModal(string $departmentId): void
    {
        $department = InventoryDepartment::query()->findOrFail($departmentId);

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

        if ($this->editingDepartmentId !== null) {
            $department = InventoryDepartment::query()->findOrFail($this->editingDepartmentId);
        } else {
            $department = new InventoryDepartment();
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

    public function openDeleteModal(string $departmentId): void
    {
        $department = InventoryDepartment::query()->findOrFail($departmentId);

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
            'editingDepartmentId' => 'required|string',
        ]);

        $department = InventoryDepartment::query()->find($this->editingDepartmentId);

        if (!$department) {
            $this->showDeleteModal = false;
            $this->editingDepartmentId = null;
            $this->message = 'This department was already deleted or could not be found.';
            $this->messageType = 'danger';
            $this->resetPage();

            return;
        }

        $department->delete();
        $this->showDeleteModal = false;
        $this->editingDepartmentId = null;
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
        $query = InventoryDepartment::query()->orderBy('name');

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
