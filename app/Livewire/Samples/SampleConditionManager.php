<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use Livewire\WithPagination;
use App\SampleCondition;
use App\SampleType;

class SampleConditionManager extends Component
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
    public $sampleTypeId = '';
    public $sampleTypeFilter = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'active' => 'boolean',
        'sampleTypeId' => 'required|exists:sample_types,id',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSampleTypeFilter()
    {
        $this->resetPage();
    }

    public function showCreateModal()
    {
        $this->resetForm();
        if ($this->sampleTypeFilter !== '') {
            $this->sampleTypeId = $this->sampleTypeFilter;
        }
        $this->showModal = true;
    }

    public function showEditModal($id)
    {
        $this->resetForm();
        $this->editingId = $id;
        $condition = SampleCondition::findOrFail($id);
        $this->name = $condition->name;
        $this->active = (bool) $condition->active;
        $this->sampleTypeId = (string) $condition->sample_type_id;
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
            $condition = SampleCondition::findOrFail($this->editingId);
            $condition->update([
                'name' => $this->name,
                'active' => $this->active ? 1 : 0,
                'sample_type_id' => $this->sampleTypeId,
            ]);
            session()->flash('success', 'Sample Condition updated successfully.');
        } else {
            SampleCondition::create([
                'name' => $this->name,
                'active' => $this->active ? 1 : 0,
                'sample_type_id' => $this->sampleTypeId,
            ]);
            session()->flash('success', 'Sample Condition created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $condition = SampleCondition::find($id);
        if ($condition) {
            $condition->delete();
            session()->flash('success', 'Sample Condition deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->active = true;
        $this->sampleTypeId = '';
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = SampleCondition::query()->with('sample_type');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === 'active' ? 1 : 0);
        }

        if ($this->sampleTypeFilter !== '') {
            $query->where('sample_type_id', $this->sampleTypeFilter);
        }

        $conditions = $query->orderBy('active', 'DESC')
                           ->orderBy('name', 'ASC')
                           ->paginate($this->perPage);

        $sampleTypes = SampleType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.samples.sample-condition-manager', [
            'conditions' => $conditions,
            'sampleTypes' => $sampleTypes,
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }
}
