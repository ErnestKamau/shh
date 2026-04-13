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
use Illuminate\Support\Facades\Auth;
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

    // Individual Clone Modal State
    public $showIndividualCloneModal = false;
    public $areaToClone = null;
    public $cloneToSubUnitIdIndividual = null;
    public $includeSamplePointsIndividual = false;

    // Create New Area Modal State
    public $showCreateAreaModal = false;
    public $newAreaForm = [
        'name' => '',
        'code' => '',
    ];

    // Create New Sample Point Modal State
    public $showCreateSamplePointModal = false;
    public $newSamplePointForm = [
        'name' => '',
        'code' => '',
    ];

    // Dropdown States
    public $customerAreasDropdownOpen = false;
    public $customerAreasSearch = '';
    public $companySubUnitDropdownOpen = false;
    public $companySubUnitSearch = '';
    public $samplePointsDropdownOpen = false;
    public $samplePointsSearch = '';

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

    public function getCustomerAreasFormattedProperty()
    {
        if (is_array($this->customerAreas) && empty($this->customerAreas)) {
            $this->loadMasterData();
        }
        if (is_array($this->customerAreas)) {
            return collect($this->customerAreas)->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'code' => $a->code])->values()->toArray();
        }
        return $this->customerAreas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'code' => $a->code])->values()->toArray();
    }

    public function getMasterSamplePointsFormattedProperty()
    {
        if (is_array($this->masterSamplePoints) && empty($this->masterSamplePoints)) {
            $this->loadMasterData();
        }
        if (is_array($this->masterSamplePoints)) {
            return collect($this->masterSamplePoints)->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->code])->values()->toArray();
        }
        return $this->masterSamplePoints->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'code' => $p->code])->values()->toArray();
    }

    // Public methods for JavaScript access (if needed)
    public function customerAreasFormatted()
    {
        return $this->customerAreasFormatted;
    }

    public function masterSamplePointsFormatted()
    {
        return $this->masterSamplePointsFormatted;
    }

    public function showCreateAreaModalInitiator()
    {
        $this->loadMasterData(); // Refresh data when opening modal
        $this->resetAreaForm();
        // Reset dropdown states
        $this->customerAreasDropdownOpen = false;
        $this->companySubUnitDropdownOpen = false;
        $this->samplePointsDropdownOpen = false;
        $this->customerAreasSearch = '';
        $this->companySubUnitSearch = '';
        $this->samplePointsSearch = '';
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
        // Reset dropdown states
        $this->customerAreasDropdownOpen = false;
        $this->companySubUnitDropdownOpen = false;
        $this->samplePointsDropdownOpen = false;
        $this->customerAreasSearch = '';
        $this->companySubUnitSearch = '';
        $this->samplePointsSearch = '';
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

    // Individual Clone Methods
    public function showIndividualCloneModalInitiator($areaId)
    {
        $this->areaToClone = SamplePointArea::with(['crmArea', 'subUnit'])->findOrFail($areaId);
        $this->cloneToSubUnitIdIndividual = null;
        $this->includeSamplePointsIndividual = false;
        $this->showIndividualCloneModal = true;
    }

    public function closeIndividualCloneModal()
    {
        $this->showIndividualCloneModal = false;
        $this->areaToClone = null;
        $this->cloneToSubUnitIdIndividual = null;
        $this->includeSamplePointsIndividual = false;
    }

    public function cloneIndividualArea()
    {
        if (!$this->areaToClone) {
            $this->message = 'No area selected for cloning.';
            $this->messageType = 'error';
            return;
        }

        $this->validate([
            'cloneToSubUnitIdIndividual' => 'required|exists:crm_company_sub_units,id|different:' . $this->areaToClone->crm_company_sub_unit_id,
        ], [
            'cloneToSubUnitIdIndividual.required' => 'Please select a sub unit to clone to.',
            'cloneToSubUnitIdIndividual.exists' => 'Selected sub unit is invalid.',
            'cloneToSubUnitIdIndividual.different' => 'Clone to sub unit must be different from the current sub unit.',
        ]);

        try {
            DB::beginTransaction();

            $targetSubUnit = CRMCompanySubUnit::findOrFail($this->cloneToSubUnitIdIndividual);
            $originalArea = $this->areaToClone;

            // Clone the area
            $newArea = new SamplePointArea();
            $newArea->description = $originalArea->description;
            $newArea->crm_customer_id = $originalArea->crm_customer_id;
            $newArea->crm_company_sub_unit_id = $this->cloneToSubUnitIdIndividual;
            $newArea->crm_area_id = $originalArea->crm_area_id;
            $newArea->crm_company_unit_id = $targetSubUnit->crm_company_unit_id;
            $newArea->active = $originalArea->active;
            $newArea->save();

            // Clone sample points if requested
            if ($this->includeSamplePointsIndividual) {
                $samplePoints = SamplePoint::where('sample_point_area_id', $originalArea->id)
                    ->where('crm_customer_id', $this->customerId)
                    ->get();

                foreach ($samplePoints as $originalPoint) {
                    $newPoint = new SamplePoint();
                    $newPoint->crm_customer_id = $originalPoint->crm_customer_id;
                    $newPoint->crm_sample_point_id = $originalPoint->crm_sample_point_id;
                    $newPoint->crm_area_id = $originalPoint->crm_area_id;
                    $newPoint->crm_company_sub_unit_id = $this->cloneToSubUnitIdIndividual;
                    $newPoint->crm_company_unit_id = $targetSubUnit->crm_company_unit_id;
                    $newPoint->sample_point_area_id = $newArea->id;
                    $newPoint->active = $originalPoint->active;
                    
                    // Flag GPS as cloned
                    $newPoint->gps = $originalPoint->gps ? $originalPoint->gps . ' (Cloned)' : '(Cloned)';
                    
                    $newPoint->save();
                }
            }

            DB::commit();

            $this->closeIndividualCloneModal();
            
            $message = "Area cloned successfully!";
            if ($this->includeSamplePointsIndividual) {
                $message .= " (with sample points)";
            }
            
            $this->message = $message;
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error cloning area: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Create New Area Methods
    public function openCreateAreaModal($prefillName = '')
    {
        $this->newAreaForm = [
            'name' => $prefillName,
            'code' => '',
        ];
        $this->showCreateAreaModal = true;
    }

    public function closeCreateAreaModal()
    {
        $this->showCreateAreaModal = false;
        $this->newAreaForm = [
            'name' => '',
            'code' => '',
        ];
    }

    public function createNewArea()
    {
        $this->validate([
            'newAreaForm.name' => 'required|string|max:255',
            'newAreaForm.code' => 'required|string|max:255|unique:crm_areas,code',
        ], [
            'newAreaForm.name.required' => 'Area name is required.',
            'newAreaForm.code.required' => 'Area code is required.',
            'newAreaForm.code.unique' => 'This area code already exists.',
        ]);

        try {
            DB::beginTransaction();

            $area = new Area();
            $area->name = $this->newAreaForm['name'];
            $area->code = $this->newAreaForm['code'];
            $area->created_by = Auth::id();
            $area->save();

            // Refresh customer areas list
            $this->loadMasterData();

            DB::commit();

            // Auto-select the newly created area
            $this->areaForm['crm_area_id'] = $area->id;
            
            $this->closeCreateAreaModal();
            $this->message = 'Area created successfully and selected!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error creating area: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Create New Sample Point Methods
    public function openCreateSamplePointModal($prefillName = '')
    {
        $this->newSamplePointForm = [
            'name' => $prefillName,
            'code' => '',
        ];
        $this->showCreateSamplePointModal = true;
    }

    public function closeCreateSamplePointModal()
    {
        $this->showCreateSamplePointModal = false;
        $this->newSamplePointForm = [
            'name' => '',
            'code' => '',
        ];
    }

    public function createNewSamplePoint()
    {
        $this->validate([
            'newSamplePointForm.name' => 'required|string|max:255',
            'newSamplePointForm.code' => 'required|string|max:255|unique:crm_sample_points,code',
        ], [
            'newSamplePointForm.name.required' => 'Sample point name is required.',
            'newSamplePointForm.code.required' => 'Sample point code is required.',
            'newSamplePointForm.code.unique' => 'This sample point code already exists.',
        ]);

        try {
            DB::beginTransaction();

            $samplePoint = new GlobalSamplePoint();
            $samplePoint->name = $this->newSamplePointForm['name'];
            $samplePoint->code = $this->newSamplePointForm['code'];
            $samplePoint->created_by = Auth::id();
            $samplePoint->save();

            // Refresh master sample points list
            $this->loadMasterData();

            DB::commit();

            // Auto-select the newly created sample point
            if (!in_array($samplePoint->id, $this->areaForm['selectedSamplePoints'])) {
                $this->areaForm['selectedSamplePoints'][] = $samplePoint->id;
            }
            
            $this->closeCreateSamplePointModal();
            $this->message = 'Sample point created successfully and selected!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error creating sample point: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Dropdown Methods
    public function toggleCustomerAreasDropdown()
    {
        $this->customerAreasDropdownOpen = !$this->customerAreasDropdownOpen;
        if ($this->customerAreasDropdownOpen) {
            $this->loadMasterData();
        }
    }

    public function toggleCompanySubUnitDropdown()
    {
        $this->companySubUnitDropdownOpen = !$this->companySubUnitDropdownOpen;
    }

    public function toggleSamplePointsDropdown()
    {
        $this->samplePointsDropdownOpen = !$this->samplePointsDropdownOpen;
        if ($this->samplePointsDropdownOpen) {
            $this->loadMasterData();
        }
    }

    public function selectCustomerArea($areaId)
    {
        $this->areaForm['crm_area_id'] = $areaId;
        $this->customerAreasDropdownOpen = false;
        $this->customerAreasSearch = '';
    }

    public function selectCompanySubUnit($subUnitId)
    {
        $this->areaForm['crm_company_sub_unit_id'] = $subUnitId;
        $this->companySubUnitDropdownOpen = false;
        $this->companySubUnitSearch = '';
    }

    public function toggleSamplePoint($pointId)
    {
        $selected = $this->areaForm['selectedSamplePoints'] ?? [];
        $index = array_search($pointId, $selected);
        
        if ($index !== false) {
            unset($selected[$index]);
            $this->areaForm['selectedSamplePoints'] = array_values($selected);
        } else {
            $this->areaForm['selectedSamplePoints'][] = $pointId;
        }
    }

    public function isSamplePointSelected($pointId): bool
    {
        return in_array($pointId, $this->areaForm['selectedSamplePoints'] ?? []);
    }

    public function clearCustomerAreaSelection()
    {
        $this->areaForm['crm_area_id'] = null;
        $this->customerAreasSearch = '';
    }

    public function clearCompanySubUnitSelection()
    {
        $this->areaForm['crm_company_sub_unit_id'] = null;
        $this->companySubUnitSearch = '';
    }

    public function getFilteredCustomerAreasProperty()
    {
        $areas = $this->customerAreasFormatted;
        
        if (empty($this->customerAreasSearch)) {
            return array_slice($areas, 0, 50);
        }
        
        $search = strtolower($this->customerAreasSearch);
        return array_values(array_filter($areas, function($area) use ($search) {
            return str_contains(strtolower($area['name']), $search) || 
                   str_contains(strtolower($area['code']), $search);
        }));
    }

    public function getFilteredCompanySubUnitsProperty()
    {
        if (empty($this->companySubUnits) || (is_array($this->companySubUnits) && count($this->companySubUnits) === 0)) {
            $this->loadMasterData();
        }
        $subUnits = is_array($this->companySubUnits) 
            ? collect($this->companySubUnits)->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code])->values()->toArray()
            : $this->companySubUnits->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code])->values()->toArray();
        
        if (empty($this->companySubUnitSearch)) {
            return array_slice($subUnits, 0, 50);
        }
        
        $search = strtolower($this->companySubUnitSearch);
        return array_values(array_filter($subUnits, function($subUnit) use ($search) {
            return str_contains(strtolower($subUnit['name']), $search) || 
                   str_contains(strtolower($subUnit['code']), $search);
        }));
    }

    public function getFilteredSamplePointsProperty()
    {
        $points = $this->masterSamplePointsFormatted;
        
        if (empty($this->samplePointsSearch)) {
            return $points;
        }
        
        $search = strtolower($this->samplePointsSearch);
        return array_values(array_filter($points, function($point) use ($search) {
            return str_contains(strtolower($point['name']), $search) || 
                   str_contains(strtolower($point['code']), $search);
        }));
    }

    public function getSelectedCustomerAreaLabelProperty(): string
    {
        if (empty($this->areaForm['crm_area_id'])) {
            return '';
        }
        
        $areas = $this->customerAreasFormatted;
        $area = collect($areas)->firstWhere('id', $this->areaForm['crm_area_id']);
        
        return $area ? "{$area['name']} ({$area['code']})" : '';
    }

    public function getSelectedCompanySubUnitLabelProperty(): string
    {
        if (empty($this->areaForm['crm_company_sub_unit_id'])) {
            return '';
        }
        
        if (is_array($this->companySubUnits) && empty($this->companySubUnits)) {
            $this->loadMasterData();
        }
        
        $subUnit = is_array($this->companySubUnits)
            ? collect($this->companySubUnits)->firstWhere('id', $this->areaForm['crm_company_sub_unit_id'])
            : $this->companySubUnits->firstWhere('id', $this->areaForm['crm_company_sub_unit_id']);
        
        if (!$subUnit) {
            return '';
        }
        
        $name = is_array($subUnit) ? $subUnit['name'] : $subUnit->name;
        $code = is_array($subUnit) ? $subUnit['code'] : $subUnit->code;
        
        return "{$name} ({$code})";
    }

    public function getSelectedSamplePointsNamesProperty(): string
    {
        $selected = $this->areaForm['selectedSamplePoints'] ?? [];
        if (empty($selected)) {
            return '';
        }
        
        $points = $this->masterSamplePointsFormatted;
        $names = [];
        
        foreach ($selected as $id) {
            $point = collect($points)->firstWhere('id', $id);
            if ($point) {
                $names[] = $point['name'];
            }
        }
        
        if (count($names) > 3) {
            return implode(', ', array_slice($names, 0, 3)) . ' +' . (count($names) - 3) . ' more';
        }
        
        return implode(', ', $names);
    }

    public function getShowCreateAreaOptionProperty(): bool
    {
        return !empty($this->customerAreasSearch) && 
               count($this->filteredCustomerAreas) === 0;
    }

    public function getShowCreateSamplePointOptionProperty(): bool
    {
        return !empty($this->samplePointsSearch) && 
               count($this->filteredSamplePoints) === 0;
    }

    public function updatedCustomerAreasSearch()
    {
        // Trigger re-render when search changes
    }

    public function updatedCompanySubUnitSearch()
    {
        // Trigger re-render when search changes
    }

    public function updatedSamplePointsSearch()
    {
        // Trigger re-render when search changes
    }

    public function render()
    {
        return view('livewire.crm.areas-manager');
    }
}
