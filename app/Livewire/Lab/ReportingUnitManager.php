<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithPagination;
use App\ReportingUnit;

class ReportingUnitManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Modal state
    public $showModal = false;
    public $editingId = null;
    
    // Form fields
    public $name = '';
    public $active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'active' => 'boolean',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function showCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function showEditModal($id)
    {
        $this->resetForm();
        $this->editingId = $id;
        $unit = ReportingUnit::findOrFail($id);
        $this->name = $unit->name;
        $this->active = (bool) $unit->active;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $unit = ReportingUnit::findOrFail($this->editingId);
            $unit->update([
                'name' => $this->name,
                'active' => $this->active ? 1 : 0,
            ]);
            session()->flash('success', 'Reporting Unit updated successfully.');
        } else {
            ReportingUnit::create([
                'name' => $this->name,
                'active' => $this->active ? 1 : 0,
            ]);
            session()->flash('success', 'Reporting Unit created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $unit = ReportingUnit::find($id);
        if ($unit) {
            $unit->delete();
            session()->flash('success', 'Reporting Unit deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->active = true;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = ReportingUnit::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === 'active' ? 1 : 0);
        }

        $units = $query->orderBy('name', 'ASC')
                       ->paginate($this->perPage);

        return view('livewire.lab.reporting-unit-manager', [
            'units' => $units,
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }
}
