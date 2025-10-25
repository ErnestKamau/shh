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

            // Create or update customer sample points (from sample_points table) to link to this area
            if (!empty($this->areaForm['selectedSamplePoints'])) {
                foreach ($this->areaForm['selectedSamplePoints'] as $masterSamplePointId) {
                    // Get the master sample point to copy name and description
                    $masterSamplePoint = GlobalSamplePoint::find($masterSamplePointId);
                    
                    if ($masterSamplePoint) {
                        // Create or update customer-specific sample point
                        SamplePoint::updateOrCreate(
                            [
                                'crm_customer_id' => $this->customerId,
                                'crm_sample_point_id' => $masterSamplePointId,
                            ],
                            [
                                'name' => $masterSamplePoint->name,
                                'description' => $masterSamplePoint->name,
                                'crm_area_id' => $area->crm_area_id,
                                'crm_company_sub_unit_id' => $area->crm_company_sub_unit_id,
                                'crm_company_unit_id' => $area->crm_company_unit_id,
                                'sample_point_area_id' => $area->id,
                                'active' => true,
                            ]
                        );
                    }
                }
            }

            // Remove link from deselected sample points
            if ($this->editingArea) {
                SamplePoint::where('sample_point_area_id', $area->id)
                    ->where('crm_customer_id', $this->customerId)
                    ->whereNotIn('crm_sample_point_id', $this->areaForm['selectedSamplePoints'] ?? [])
                    ->update(['sample_point_area_id' => null]);
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

    public function deleteArea($id)
    {
        try {
            DB::beginTransaction();

            $area = SamplePointArea::findOrFail($id);
            
            // Check if area has sample points
            if ($area->samplePoints()->count() > 0) {
                $this->message = 'Cannot delete area that has sample points. Please reassign or delete sample points first.';
                $this->messageType = 'error';
                return;
            }

            // Soft delete
            $area->delete();

            DB::commit();
            
            $this->message = 'Area deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
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

    public function render()
    {
        return view('livewire.crm.areas-manager');
    }
}
