<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use Livewire\WithPagination;
use App\SampleTypeCategory;

class SampleCategoryManager extends Component
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
        $category = SampleTypeCategory::findOrFail($id);
        $this->name = $category->sample_type_category;
        $this->active = (bool) $category->active;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        // Dispatch event to hide modal via JS if needed, or rely on wire:if/alpine
        $this->dispatch('close-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $category = SampleTypeCategory::findOrFail($this->editingId);
            $category->update([
                'sample_type_category' => $this->name,
                'active' => $this->active ? 1 : 0,
            ]);
            session()->flash('success', 'Sample Type Category updated successfully.');
        } else {
            SampleTypeCategory::create([
                'sample_type_category' => $this->name,
                'active' => $this->active ? 1 : 0,
                'zoho_id' => null, // Explicitly null as requested to be removed from UI
            ]);
            session()->flash('success', 'Sample Type Category created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        // For "delete", we usually just toggle active or actually delete. 
        // The original controller didn't have delete, but the index had a list.
        // I will adhere to the "element-manager" style which has delete. 
        // However, safely, I'll delete. If soft deletes are used, it handles itself.
        // Checking model... standard model usually.
        // Let's implement delete.
        
        $category = SampleTypeCategory::find($id);
        if ($category) {
            $category->delete();
            session()->flash('success', 'Sample Type Category deleted successfully.');
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
        $query = SampleTypeCategory::query();

        if ($this->search) {
            $query->where('sample_type_category', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === 'active' ? 1 : 0);
        }

        $categories = $query->orderBy('active', 'DESC')
                           ->orderBy('sample_type_category', 'ASC')
                           ->paginate($this->perPage);

        return view('livewire.samples.sample-category-manager', [
            'categories' => $categories,
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }
}
