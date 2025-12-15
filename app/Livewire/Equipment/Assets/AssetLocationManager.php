<?php

namespace App\Livewire\Equipment\Assets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Assets\AssetLocation;

class AssetLocationManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;
    
    // Form properties
    public $showModal = false;
    public $editId = null;
    public $location_code = '';
    public $name = '';
    public $is_active = true;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'location_code' => 'required|string|max:50|unique:asset_locations,location_code',
        'name' => 'required|string|max:255',
        'is_active' => 'boolean',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $this->resetForm();
        
        $location = AssetLocation::findOrFail($id);
        $this->editId = $id;
        $this->location_code = $location->location_code;
        $this->name = $location->name;
        $this->is_active = (bool)$location->is_active;
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->location_code = '';
        $this->name = '';
        $this->is_active = true;
    }

    public function save()
    {
        // Adjust unique rule for update
        $rules = $this->rules;
        if ($this->editId) {
            $rules['location_code'] = 'required|string|max:50|unique:asset_locations,location_code,' . $this->editId;
        }

        $this->validate($rules);

        if ($this->editId) {
            $location = AssetLocation::findOrFail($this->editId);
            $location->update([
                'location_code' => $this->location_code,
                'name' => $this->name,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Location updated successfully.']);
        } else {
            AssetLocation::create([
                'location_code' => $this->location_code,
                'name' => $this->name,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Location created successfully.']);
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        try {
            AssetLocation::findOrFail($id)->delete();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Location deleted successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error deleting Asset Location: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = AssetLocation::query();

        if ($this->search) {
            $query->where('location_code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.equipment.assets.asset-location-manager', [
            'locations' => $query->orderBy('created_at', 'desc')->paginate($this->perPage)
        ]);
    }
}
