<?php

namespace App\Livewire\Equipment\Assets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Assets\AssetType;

class AssetTypeManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;
    
    // Form properties
    public $showModal = false;
    public $editId = null;
    public $asset_code = '';
    public $description = '';
    public $is_active = true;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'asset_code' => 'required|string|max:50|unique:asset_types,asset_code',
        'description' => 'required|string|max:255',
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
        
        $type = AssetType::findOrFail($id);
        $this->editId = $id;
        $this->asset_code = $type->asset_code;
        $this->description = $type->descripton; // Note DB typo
        $this->is_active = (bool)$type->is_active;
        
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
        $this->asset_code = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function save()
    {
        // Adjust unique rule for update
        $rules = $this->rules;
        if ($this->editId) {
            $rules['asset_code'] = 'required|string|max:50|unique:asset_types,asset_code,' . $this->editId;
        }

        $this->validate($rules);

        if ($this->editId) {
            $type = AssetType::findOrFail($this->editId);
            $type->update([
                'asset_code' => $this->asset_code,
                'descripton' => $this->description,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Type updated successfully.']);
        } else {
            AssetType::create([
                'asset_code' => $this->asset_code,
                'descripton' => $this->description,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Type created successfully.']);
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        try {
            AssetType::findOrFail($id)->delete();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Asset Type deleted successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error deleting Asset Type: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = AssetType::query()->withCount(['equipments as active_equipments_count' => function ($query) {
            $query->where('active', 1);
        }]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('asset_code', 'like', '%' . $this->search . '%')
                  ->orWhere('descripton', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.equipment.assets.asset-type-manager', [
            'types' => $query->orderBy('created_at', 'desc')->paginate($this->perPage)
        ]);
    }
}
