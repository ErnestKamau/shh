<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\CRMCompanySubUnit;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanySubUnitsManager extends Component
{
    use WithPagination;

    // Customer Data
    public $customerId;
    public $customer;
    
    // Sub Unit Management
    public $editingSubUnit = null;
    public $showSubUnitModal = false;
    
    // Sub Unit Form
    public $subUnitForm = [
        'name' => '',
        'code' => '',
        'crm_company_unit_id' => null,
        'active' => true
    ];

    // Supporting Data
    public $companyUnits = [];

    // Search and Filter
    public $search = '';
    public $unitFilter = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Clone Modal State
    public $showCloneModal = false;
    public $cloneFromUnitId = null;
    public $cloneToUnitId = null;
    public $availableSubUnits = [];
    public $selectedSubUnitsToClone = [];
    public $includeAreas = false;
    public $includeSamplePoints = false;

    protected $rules = [
        'subUnitForm.name' => 'required|string|max:255',
        'subUnitForm.code' => 'required|string|max:50',
        'subUnitForm.crm_company_unit_id' => 'required|exists:crm_company_units,id',
    ];

    protected $messages = [
        'subUnitForm.name.required' => 'Sub unit name is required.',
        'subUnitForm.code.required' => 'Sub unit code is required.',
        'subUnitForm.crm_company_unit_id.required' => 'Company unit selection is required.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = CRMCustomer::findOrFail($customerId);
        $this->loadCompanyUnits();
    }

    public function loadCompanyUnits()
    {
        $this->companyUnits = CRMCompanyUnit::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getSubUnitsProperty()
    {
        $query = CRMCompanySubUnit::with(['companyUnit'])
            ->where('crm_customer_id', $this->customerId)
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->unitFilter, function ($query) {
                $query->where('crm_company_unit_id', $this->unitFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            })
            ->orderBy('name');

        return $query->paginate($this->perPage);
    }

    public function showCreateSubUnitModal()
    {
        $this->resetSubUnitForm();
        $this->showSubUnitModal = true;
    }

    public function showEditSubUnitModal($id)
    {
        $subUnit = CRMCompanySubUnit::findOrFail($id);
        
        $this->subUnitForm = [
            'name' => $subUnit->name,
            'code' => $subUnit->code,
            'crm_company_unit_id' => $subUnit->crm_company_unit_id,
            'active' => $subUnit->active
        ];
        
        $this->editingSubUnit = $subUnit;
        $this->showSubUnitModal = true;
    }

    public function saveSubUnit()
    {
        // Update validation rules for code uniqueness
        $this->rules['subUnitForm.code'] = [
            'required',
            'string',
            'max:50',
            Rule::unique('crm_company_sub_units', 'code')->where(function ($query) {
                return $query->where('crm_customer_id', $this->customerId);
            })->ignore($this->editingSubUnit ? $this->editingSubUnit->id : null)
        ];

        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingSubUnit) {
                // Update existing sub unit
                $subUnit = $this->editingSubUnit;
            } else {
                // Create new sub unit
                $subUnit = new CRMCompanySubUnit();
                $subUnit->crm_customer_id = $this->customerId;
            }

            $subUnit->name = $this->subUnitForm['name'];
            $subUnit->code = $this->subUnitForm['code'];
            $subUnit->crm_company_unit_id = $this->subUnitForm['crm_company_unit_id'];
            $subUnit->active = $this->subUnitForm['active'];
            $subUnit->save();

            DB::commit();
            
            $this->closeSubUnitModal();
            $this->message = $this->editingSubUnit ? 'Sub unit updated successfully!' : 'Sub unit created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteSubUnit($id)
    {
        try {
            DB::beginTransaction();

            $subUnit = CRMCompanySubUnit::findOrFail($id);
            
            // Check if sub unit has sample points
            if ($subUnit->samplePoints()->count() > 0) {
                $this->message = 'Cannot delete sub unit that has sample points. Please reassign or delete sample points first.';
                $this->messageType = 'error';
                return;
            }

            // Soft delete
            $subUnit->delete();

            DB::commit();
            
            $this->message = 'Sub unit deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeSubUnitModal()
    {
        $this->showSubUnitModal = false;
        $this->resetSubUnitForm();
    }

    public function resetSubUnitForm()
    {
        $this->subUnitForm = [
            'name' => '',
            'code' => '',
            'crm_company_unit_id' => null,
            'active' => true
        ];
        $this->editingSubUnit = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->unitFilter = '';
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
        $this->cloneFromUnitId = null;
        $this->cloneToUnitId = null;
        $this->availableSubUnits = [];
        $this->selectedSubUnitsToClone = [];
        $this->includeAreas = false;
        $this->includeSamplePoints = false;
    }

    public function updatedCloneFromUnitId($value)
    {
        if ($value) {
            $this->loadSubUnitsForCloning();
        } else {
            $this->availableSubUnits = [];
            $this->selectedSubUnitsToClone = [];
        }
    }

    public function loadSubUnitsForCloning()
    {
        if (!$this->cloneFromUnitId) {
            return;
        }

        $subUnits = CRMCompanySubUnit::where('crm_company_unit_id', $this->cloneFromUnitId)
            ->where('crm_customer_id', $this->customerId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $this->availableSubUnits = $subUnits->toArray();
        
        // Pre-select all sub units
        $this->selectedSubUnitsToClone = $subUnits->pluck('id')->toArray();
    }

    public function cloneSubUnits()
    {
        // Validation
        $this->validate([
            'cloneFromUnitId' => 'required|exists:crm_company_units,id',
            'cloneToUnitId' => 'required|exists:crm_company_units,id|different:cloneFromUnitId',
            'selectedSubUnitsToClone' => 'required|array|min:1',
        ], [
            'cloneFromUnitId.required' => 'Please select a company unit to clone from.',
            'cloneToUnitId.required' => 'Please select a company unit to clone to.',
            'cloneToUnitId.different' => 'Clone from and clone to units must be different.',
            'selectedSubUnitsToClone.required' => 'Please select at least one sub unit to clone.',
            'selectedSubUnitsToClone.min' => 'Please select at least one sub unit to clone.',
        ]);

        // Validate that include sample points requires include areas
        if ($this->includeSamplePoints && !$this->includeAreas) {
            $this->message = 'To include sample points, you must also include areas.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();

            $clonedCount = 0;
            $targetUnit = CRMCompanyUnit::findOrFail($this->cloneToUnitId);

            foreach ($this->selectedSubUnitsToClone as $subUnitId) {
                $originalSubUnit = CRMCompanySubUnit::findOrFail($subUnitId);

                // Clone the sub unit
                $newSubUnit = new CRMCompanySubUnit();
                $newSubUnit->name = $originalSubUnit->name;
                $newSubUnit->code = $originalSubUnit->code;
                $newSubUnit->crm_company_unit_id = $this->cloneToUnitId;
                $newSubUnit->crm_customer_id = $targetUnit->crm_customer_id;
                $newSubUnit->active = $originalSubUnit->active;
                $newSubUnit->save();

                $clonedCount++;

                // Clone areas if requested
                if ($this->includeAreas) {
                    $areas = \App\Models\SamplePointArea::where('crm_company_sub_unit_id', $originalSubUnit->id)
                        ->get();

                    foreach ($areas as $originalArea) {
                        $newArea = new \App\Models\SamplePointArea();
                        $newArea->description = $originalArea->description;
                        $newArea->crm_customer_id = $originalArea->crm_customer_id;
                        $newArea->crm_company_sub_unit_id = $newSubUnit->id;
                        $newArea->crm_area_id = $originalArea->crm_area_id;
                        $newArea->crm_company_unit_id = $this->cloneToUnitId;
                        $newArea->active = $originalArea->active;
                        $newArea->save();

                        // Clone sample points if requested
                        if ($this->includeSamplePoints) {
                            $samplePoints = \App\Models\CRM\SamplePoint::where('sample_point_area_id', $originalArea->id)
                                ->get();

                            foreach ($samplePoints as $originalPoint) {
                                $newPoint = new \App\Models\CRM\SamplePoint();
                                $newPoint->crm_company_unit_id = $this->cloneToUnitId;
                                $newPoint->sample_point_area_id = $newArea->id;
                                $newPoint->crm_area_id = $originalPoint->crm_area_id;
                                $newPoint->crm_sample_point_id = $originalPoint->crm_sample_point_id;
                                $newPoint->crm_company_sub_unit_id = $newSubUnit->id;
                                $newPoint->crm_customer_id = $originalPoint->crm_customer_id;
                                $newPoint->active = $originalPoint->active;
                                
                                // Flag GPS as cloned
                                $newPoint->gps = $originalPoint->gps ? $originalPoint->gps . ' (Cloned)' : '(Cloned)';
                                
                                $newPoint->save();
                            }
                        }
                    }
                }
            }

            DB::commit();

            $this->closeCloneModal();
            
            $message = "{$clonedCount} sub unit(s) cloned successfully!";
            if ($this->includeAreas) {
                $message .= " (with areas)";
            }
            if ($this->includeSamplePoints) {
                $message .= " (and sample points)";
            }
            
            $this->message = $message;
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error cloning sub units: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.c-r-m.company-sub-units-manager');
    }
}
