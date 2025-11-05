<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Area;
use App\Models\SamplePointArea;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanySubUnit;
use App\Models\SamplePoint as GlobalSamplePoint;
use App\Models\CRM\SamplePoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AreasManager extends Component
{
    use WithPagination;

    // Customer Data
    public $customerId;
    public $customer;
    
    // Area Management
    public $editingArea = null;
    public $showAreaModal = false;
    
    // Area Form
    public $areaForm = [
        'description' => '',
        'crm_area_id' => null,
        'crm_company_sub_unit_id' => null,
        'selectedSamplePoints' => [],
        'active' => true
    ];

    // Supporting Data
    public $masterSamplePoints = [];
    public $availableSamplePoints = [];
    public $customerAreas = [];
    public $companySubUnits = [];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Clone Modal State
    public $showCloneModal = false;
    public $cloneFromSubUnitId = null;
    public $cloneToSubUnitId = null;
    public $availableAreasToClone = [];
    public $selectedAreasToClone = [];
    public $includeSamplePoints = false;

    // Delete Confirmation Modal State
    public $showDeleteConfirmModal = false;
    public $areaToDelete = null;
    public $samplePointsCount = 0;

    protected $rules = [
        'areaForm.crm_area_id' => 'required|exists:crm_areas,id',
        'areaForm.crm_company_sub_unit_id' => 'required|exists:crm_company_sub_units,id',
        'areaForm.description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'areaForm.crm_area_id.required' => 'Please select a customer area.',
        'areaForm.crm_area_id.exists' => 'Selected customer area is invalid.',
        'areaForm.crm_company_sub_unit_id.required' => 'Please select a company sub unit.',
        'areaForm.crm_company_sub_unit_id.exists' => 'Selected company sub unit is invalid.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = CRMCustomer::findOrFail($customerId);
        $this->loadMasterData();
    }

    public function loadMasterData()
    {
        $this->customerAreas = Area::orderBy('name')->get();
        $this->masterSamplePoints = GlobalSamplePoint::orderBy('name')->get(); // from crm_sample_points
        $this->companySubUnits = CRMCompanySubUnit::where('crm_customer_id', $this->customerId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAreasProperty()
    {
        $query = SamplePointArea::with(['crmArea', 'subUnit', 'samplePoints'])
            ->where('crm_customer_id', $this->customerId)
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
    }

    public function showCreateAreaModal()
    {
        $this->resetAreaForm();
        $this->showAreaModal = true;
    }

    public function showEditAreaModal($id)
    {
        $area = SamplePointArea::findOrFail($id);
        
        // Get associated sample points (get the crm_sample_point_id from sample_points table)
        $associatedSamplePoints = SamplePoint::where('sample_point_area_id', $id)
            ->where('crm_customer_id', $this->customerId)
            ->pluck('crm_sample_point_id')
            ->toArray();
        
        $this->areaForm = [
            'description' => $area->description ?? '',
            'crm_area_id' => $area->crm_area_id,
            'crm_company_sub_unit_id' => $area->crm_company_sub_unit_id,
            'selectedSamplePoints' => $associatedSamplePoints,
            'active' => $area->active
        ];
        
        $this->editingArea = $area;
        $this->showAreaModal = true;
    }

    public function saveArea()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Get the selected CRM Area and Sub Unit
            $crmArea = Area::findOrFail($this->areaForm['crm_area_id']);
            $subUnit = CRMCompanySubUnit::findOrFail($this->areaForm['crm_company_sub_unit_id']);

            if ($this->editingArea) {
                $area = $this->editingArea;
            } else {
                $area = new SamplePointArea();
            }

            // Set all fields
            $area->description = $this->areaForm['description'];
            $area->crm_customer_id = $this->customerId;
            $area->crm_area_id = $this->areaForm['crm_area_id'];
            $area->crm_company_sub_unit_id = $this->areaForm['crm_company_sub_unit_id'];
            $area->crm_company_unit_id = $subUnit->crm_company_unit_id; // Auto-filled from sub unit
            $area->active = $this->areaForm['active'];
            $area->save();

            // Handle sample points for this area only
            if (!empty($this->areaForm['selectedSamplePoints'])) {
                foreach ($this->areaForm['selectedSamplePoints'] as $masterSamplePointId) {
                    // Check if this specific sample point already exists for THIS area
                    $existingPoint = SamplePoint::where('sample_point_area_id', $area->id)
                        ->where('crm_customer_id', $this->customerId)
                        ->where('crm_sample_point_id', $masterSamplePointId)
                        ->first();
                    
                    if ($existingPoint) {
                        // Update existing point for THIS area only
                        $existingPoint->crm_area_id = $area->crm_area_id;
                        $existingPoint->crm_company_sub_unit_id = $area->crm_company_sub_unit_id;
                        $existingPoint->crm_company_unit_id = $area->crm_company_unit_id;
                        $existingPoint->active = true;
                        $existingPoint->save();
                    } else {
                        // Create new sample point for THIS area
                        $newPoint = new SamplePoint();
                        $newPoint->crm_customer_id = $this->customerId;
                        $newPoint->crm_sample_point_id = $masterSamplePointId;
                        $newPoint->crm_area_id = $area->crm_area_id;
                        $newPoint->crm_company_sub_unit_id = $area->crm_company_sub_unit_id;
                        $newPoint->crm_company_unit_id = $area->crm_company_unit_id;
                        $newPoint->sample_point_area_id = $area->id;
                        $newPoint->active = true;
                        $newPoint->save();
                    }
                }
            }

            // Remove sample points that were deselected from THIS area only
            if ($this->editingArea) {
                SamplePoint::where('sample_point_area_id', $area->id)
                    ->where('crm_customer_id', $this->customerId)
                    ->whereNotIn('crm_sample_point_id', $this->areaForm['selectedSamplePoints'] ?? [])
                    ->delete();
            }

            DB::commit();
            
            $this->closeAreaModal();
            $this->message = $this->editingArea ? 'Area updated successfully!' : 'Area created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function showDeleteConfirmation($id)
    {
        $area = SamplePointArea::findOrFail($id);
        $this->areaToDelete = $area;
        $this->samplePointsCount = $area->samplePoints()->count();
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->areaToDelete = null;
        $this->samplePointsCount = 0;
    }

    public function confirmDeleteArea()
    {
        if (!$this->areaToDelete) {
            $this->message = 'No area selected for deletion.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();

            $area = $this->areaToDelete;
            
            // Delete all sample points associated with this area first
            SamplePoint::where('sample_point_area_id', $area->id)
                ->where('crm_customer_id', $this->customerId)
                ->delete();

            // Delete the area
            $area->delete();

            DB::commit();
            
            $this->closeDeleteConfirmModal();
            $this->message = 'Area and associated sample points deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->closeDeleteConfirmModal();
            $this->message = 'Error deleting area: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeAreaModal()
    {
        $this->showAreaModal = false;
        $this->resetAreaForm();
    }

    public function resetAreaForm()
    {
        $this->areaForm = [
            'description' => '',
            'crm_area_id' => null,
            'crm_company_sub_unit_id' => null,
            'selectedSamplePoints' => [],
            'active' => true
        ];
        $this->editingArea = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Clone Modal Methods
    public function showCloneModalMethod()
    {
        $this->resetCloneState();
        $this->showCloneModal = true;
    }

    public function closeCloneModal()
    {
        $this->showCloneModal = false;
        $this->resetCloneState();
    }

    public function resetCloneState()
    {
        $this->cloneFromSubUnitId = null;
        $this->cloneToSubUnitId = null;
        $this->availableAreasToClone = [];
        $this->selectedAreasToClone = [];
        $this->includeSamplePoints = false;
    }

    public function updatedCloneFromSubUnitId($value)
    {
        if ($value) {
            $this->loadAreasForCloning();
        } else {
            $this->availableAreasToClone = [];
            $this->selectedAreasToClone = [];
        }
    }

    public function loadAreasForCloning()
    {
        if (!$this->cloneFromSubUnitId) {
            return;
        }

        $areas = SamplePointArea::with(['crmArea'])
            ->where('crm_company_sub_unit_id', $this->cloneFromSubUnitId)
            ->where('crm_customer_id', $this->customerId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'description', 'crm_area_id']);

        $this->availableAreasToClone = $areas->map(function($area) {
            return [
                'id' => $area->id,
                'description' => $area->description ?? 'N/A',
                'area_name' => $area->crmArea->name ?? 'N/A',
                'area_code' => $area->crmArea->code ?? 'N/A',
            ];
        })->toArray();
        
        // Pre-select all areas
        $this->selectedAreasToClone = $areas->pluck('id')->toArray();
    }

    public function cloneAreas()
    {
        // Validation
        $this->validate([
            'cloneFromSubUnitId' => 'required|exists:crm_company_sub_units,id',
            'cloneToSubUnitId' => 'required|exists:crm_company_sub_units,id|different:cloneFromSubUnitId',
            'selectedAreasToClone' => 'required|array|min:1',
        ], [
            'cloneFromSubUnitId.required' => 'Please select a sub unit to clone from.',
            'cloneToSubUnitId.required' => 'Please select a sub unit to clone to.',
            'cloneToSubUnitId.different' => 'Clone from and clone to sub units must be different.',
            'selectedAreasToClone.required' => 'Please select at least one area to clone.',
            'selectedAreasToClone.min' => 'Please select at least one area to clone.',
        ]);

        try {
            DB::beginTransaction();

            $clonedCount = 0;
            $targetSubUnit = CRMCompanySubUnit::findOrFail($this->cloneToSubUnitId);

            foreach ($this->selectedAreasToClone as $areaId) {
                $originalArea = SamplePointArea::findOrFail($areaId);

                // Clone the area
                $newArea = new SamplePointArea();
                $newArea->description = $originalArea->description;
                $newArea->crm_customer_id = $originalArea->crm_customer_id;
                $newArea->crm_company_sub_unit_id = $this->cloneToSubUnitId;
                $newArea->crm_area_id = $originalArea->crm_area_id;
                $newArea->crm_company_unit_id = $targetSubUnit->crm_company_unit_id;
                $newArea->active = $originalArea->active;
                $newArea->save();

                $clonedCount++;

                        // Clone sample points if requested
                        if ($this->includeSamplePoints) {
                            $samplePoints = SamplePoint::where('sample_point_area_id', $originalArea->id)
                                ->get();

                            foreach ($samplePoints as $originalPoint) {
                                $newPoint = new SamplePoint();
                                $newPoint->crm_customer_id = $originalPoint->crm_customer_id;
                                $newPoint->crm_sample_point_id = $originalPoint->crm_sample_point_id;
                                $newPoint->crm_area_id = $originalPoint->crm_area_id;
                                $newPoint->crm_company_sub_unit_id = $this->cloneToSubUnitId;
                                $newPoint->crm_company_unit_id = $targetSubUnit->crm_company_unit_id;
                                $newPoint->sample_point_area_id = $newArea->id;
                                $newPoint->active = $originalPoint->active;
                                
                                // Flag GPS as cloned
                                $newPoint->gps = $originalPoint->gps ? $originalPoint->gps . ' (Cloned)' : '(Cloned)';
                                
                                $newPoint->save();
                            }
                        }
            }

            DB::commit();

            $this->closeCloneModal();
            
            $message = "{$clonedCount} area(s) cloned successfully!";
            if ($this->includeSamplePoints) {
                $message .= " (with sample points)";
            }
            
            $this->message = $message;
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error cloning areas: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.crm.areas-manager');
    }
}
