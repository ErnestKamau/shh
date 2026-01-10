<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\CompanyProduct;

class SampleProductManager extends Component
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
    public $unit = 0; // Keeping unit logic, defaulting to 0 as per controller

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
        $product = CompanyProduct::findOrFail($id);
        $this->name = $product->name;
        $this->active = (bool) $product->active;
        $this->unit = $product->crm_company_unit_id;
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
            $product = CompanyProduct::findOrFail($this->editingId);
            $product->update([
                'name' => $this->name,
                'active' => $this->active ? 1 : 0,
                'crm_company_unit_id' => $this->unit,
            ]);
            session()->flash('success', 'Sample Product updated successfully.');
        } else {
            CompanyProduct::create([
                'name' => $this->name,
                'active' => $this->active ? 1 : 0,
                'crm_company_unit_id' => $this->unit,
            ]);
            session()->flash('success', 'Sample Product created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $product = CompanyProduct::find($id);
        if ($product) {
            $product->delete();
            session()->flash('success', 'Sample Product deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->active = true;
        $this->unit = 0;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = CompanyProduct::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === 'active' ? 1 : 0);
        }

        $products = $query->orderBy('active', 'DESC')
                          ->orderBy('name', 'ASC')
                          ->paginate($this->perPage);

        return view('livewire.samples.sample-product-manager', [
            'products' => $products,
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }
}
