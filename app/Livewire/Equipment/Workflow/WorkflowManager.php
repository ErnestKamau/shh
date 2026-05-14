<?php

namespace App\Livewire\Equipment\Workflow;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflow;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;

class WorkflowManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    
    // Filters
    public $typeFilter = '';
    public $locationFilter = '';
    public $typeFilterName = '';
    public $locationFilterName = '';
    public $typeFilterSearch = '';
    public $locationFilterSearch = '';
    
    // Expanded workflows
    public $expandedWorkflows = [];
    
    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
        // Update typeFilterName when filter changes
        if ($this->typeFilter) {
            $assetType = AssetType::find($this->typeFilter);
            $this->typeFilterName = $assetType ? $assetType->name : '';
        } else {
            $this->typeFilterName = '';
        }
    }

    public function updatedLocationFilter()
    {
        $this->resetPage();
        // Update locationFilterName when filter changes
        if ($this->locationFilter) {
            $location = AssetLocation::find($this->locationFilter);
            $this->locationFilterName = $location ? $location->name : '';
        } else {
            $this->locationFilterName = '';
        }
    }

    public function deleteWorkflow($id)
    {
        try {
            $workflow = EquipmentDisposalApprovalWorkflow::findOrFail($id);
            $workflow->delete();
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Workflow deleted successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error deleting workflow: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $workflow = EquipmentDisposalApprovalWorkflow::findOrFail($id);
            $workflow->update(['is_active' => !$workflow->is_active]);
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Workflow status updated successfully.']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error updating status: ' . $e->getMessage()]);
        }
    }

    public function toggleExpand($id)
    {
        if (isset($this->expandedWorkflows[$id])) {
            unset($this->expandedWorkflows[$id]);
        } else {
            $this->expandedWorkflows[$id] = true;
        }
    }

    public function render()
    {
        $query = EquipmentDisposalApprovalWorkflow::with(['equipmentType', 'location', 'steps'])
            ->where('company_id', getUserCompany());

        if ($this->search) {
            $query->where(function($q) {
                $q->where('workflow_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('equipment_type_id', $this->typeFilter);
        }

        if ($this->locationFilter) {
            $query->where('location_id', $this->locationFilter);
        }

        $workflows = $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        
        $assetTypes = AssetType::all();
        $locations = AssetLocation::all();

        return view('livewire.equipment.workflow.workflow-manager', [
            'workflows' => $workflows,
            'assetTypes' => $assetTypes,
            'locations' => $locations,
        ]);
    }
}
