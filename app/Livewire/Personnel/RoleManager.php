<?php

namespace App\Livewire\Personnel;

use App\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RoleManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showRoleModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public string $roleDescription = '';
    public int $roleLevel = 1;
    public bool $roleActive = true;
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
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleLevel = 1;
        $this->roleActive = true;
        $this->showRoleModal = true;
    }

    public function openEditModal(int $roleId): void
    {
        $role = Role::query()->where('company_id', getUserCompany())->findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->roleName = (string) $role->name;
        $this->roleDescription = (string) $role->description;
        $this->roleLevel = (int) $role->level;
        $this->roleActive = (int) $role->active === 1;
        $this->showRoleModal = true;
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:255',
            'roleDescription' => 'required|string|max:1000',
            'roleLevel' => 'required|integer|min:1',
            'roleActive' => 'boolean',
        ]);

        if ($this->editingRoleId) {
            $role = Role::query()->where('company_id', getUserCompany())->findOrFail($this->editingRoleId);
        } else {
            $role = new Role();
            $role->company_id = getUserCompany();
        }

        $role->name = $this->roleName;
        $role->description = $this->roleDescription;
        $role->level = $this->roleLevel;
        $role->active = $this->roleActive ? 1 : 0;
        $role->save();

        $this->showRoleModal = false;
        $this->message = $this->editingRoleId ? 'Role updated successfully.' : 'Role added successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function openDeleteModal(int $roleId): void
    {
        $role = Role::query()->where('company_id', getUserCompany())->findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->roleName = (string) $role->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteRole(): void
    {
        $this->validate([
            'editingRoleId' => 'required|integer',
        ]);

        $role = Role::query()->where('company_id', getUserCompany())->findOrFail((int) $this->editingRoleId);
        $role->delete();
        $this->showDeleteModal = false;
        $this->message = 'Role deleted successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function getRolesProperty()
    {
        $query = Role::query()
            ->where('company_id', getUserCompany())
            ->orderBy('name');

        if ($this->search !== '') {
            $query->where(function ($builder): void {
                $searchText = '%' . $this->search . '%';
                $builder->where('name', 'like', $searchText)
                    ->orWhere('description', 'like', $searchText)
                    ->orWhere('level', 'like', $searchText);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.role-manager');
    }
}
