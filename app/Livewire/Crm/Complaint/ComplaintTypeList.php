<?php

namespace App\Livewire\Crm\Complaint;

use App\Models\CRM\Complaint_Type;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;

class ComplaintTypeList extends BaseCrmComponent
{
    // public $types; // Removed, passing directly in render
    public $showForm = false;
    public $editingTypeId = null;
    public $name = '';
    public $description = '';
    public $status = false;

    // Filter properties
    public $search = '';
    public $perPage = 10;
    public $typeIdToDelete = null;

    // protected $paginationTheme = 'bootstrap'; // Usually handled in Base or globally

    public function mount()
    {
        $this->initialize();
        $this->checkPermission('CRM.components.Complaint Type.View');
        // $this->loadTypes(); // Removed
    }

    public function getBreadcrumbItemsProperty()
    {
        return [
            [
                'link' => route('complaint-type-home'),
                'name' => 'Complaint Type',
                'icon' => null
            ]
        ];
    }

    public function openAddForm()
    {
        $this->resetForm();
        $this->dispatch('open-complaint-type-modal');
    }

    public function openEditForm($typeId)
    {
        $type = Complaint_Type::find($typeId);
        if ($type) {
            $this->editingTypeId = $typeId;
            $this->name = $type->name;
            $this->description = $type->description ?? '';
            $this->status = in_array($type->status, [1, '1', 'active'], true);
            $this->dispatch('open-complaint-type-modal');
        }
    }

    public function resetForm()
    {
        $this->editingTypeId = null;
        $this->name = '';
        $this->description = '';
        $this->status = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $isEditing = $this->editingTypeId ? true : false;

        if ($isEditing) {
            $this->checkPermission('CRM.components.Complaint Type.Edit');
            $type = Complaint_Type::find($this->editingTypeId);
            if (!$type) {
                $this->showError('Complaint Type not found.');
                return;
            }
        } else {
            $this->checkPermission('CRM.components.Complaint Type.Add');
            $type = new Complaint_Type();
        }

        $type->name = $this->name;
        $type->description = $this->description;
        $type->status = $this->status ? 1 : 0;
        $type->save();

        // $this->loadTypes(); // Not needed, render handles it
        $this->resetForm();
        $this->showSuccess($isEditing ? 'Complaint Type edited successfully!' : 'Complaint Type added successfully!');
        $this->dispatch('type-saved');
        $this->dispatch('close-complaint-type-modal');
    }

    public function confirmDelete($id)
    {
        $this->typeIdToDelete = $id;
        $this->dispatch('show-type-delete-modal');
    }

    public function cancelDelete()
    {
        $this->typeIdToDelete = null;
        $this->dispatch('hide-type-delete-modal');
    }

    public function delete()
    {
        $this->checkPermission('CRM.components.Complaint Type.Delete');

        $type = Complaint_Type::find($this->typeIdToDelete);
        if ($type) {
            $type->delete();
            $this->showSuccess('Complaint type deleted successfully');
            $this->dispatch('hide-type-delete-modal');
            $this->typeIdToDelete = null;
            $this->dispatch('type-deleted');
        }
    }

    #[On('type-saved')]
    #[On('type-deleted')]
    public function refreshTypes()
    {
        // Just empty is fine, Livewire will re-render
    }

    public $activeTab = 'all';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function getTypesProperty()
    {
        $query = Complaint_Type::query();

        if ($this->activeTab === 'active') {
            $query->where(function ($q) {
                $q->where('status', 1)->orWhere('status', 'active');
            });
        } elseif ($this->activeTab === 'archived') {
            $query->where(function ($q) {
                $q->where('status', 0)->orWhereIn('status', ['archive', 'archived']);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function exportToExcel()
    {
        $this->checkPermission('CRM.components.Complaint Type.View');

        $filters = [
            'activeTab' => $this->activeTab,
            'search' => $this->search,
        ];

        return (new \App\Exports\CRM\ComplaintTypesExport($filters))->download('crm_complaint_types_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.crm.complaint.complaint-type-list', [
            'types' => $this->types,
        ]);
    }
}
