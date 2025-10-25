<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCompanySubUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\SamplePoint as GlobalSamplePoint;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SamplePointsManager extends Component
{
    use WithPagination;

    // Customer Data
    public $customerId;
    public $customer;
    
    // Sample Point Management
    public $editingSamplePoint = null;
    public $showSamplePointModal = false;
    
    // Sample Point Form
    public $samplePointForm = [
        'unit_id' => null,
        'sub_unit_id' => null,
        'area_id' => null,
        'crm_area_id' => null,
        'crm_sample_point_id' => null,
        'active' => true
    ];

    // Supporting Data
    public $units = [];
    public $subUnits = [];
    public $areas = [];
    public $masterSamplePoints = [];
    public $globalAreas = [];

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
        'samplePointForm.unit_id' => 'required|exists:crm_company_units,id',
        'samplePointForm.sub_unit_id' => 'nullable|exists:crm_company_sub_units,id',
        'samplePointForm.area_id' => 'nullable|exists:sample_point_area,id',
        'samplePointForm.crm_area_id' => 'nullable|exists:crm_areas,id',
        'samplePointForm.crm_sample_point_id' => 'required|exists:crm_sample_points,id',
    ];

    protected $messages = [
        'samplePointForm.unit_id.required' => 'Company unit selection is required.',
        'samplePointForm.crm_sample_point_id.required' => 'Master sample point selection is required.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->loadCustomerData();
        $this->loadInitialData();
    }

    public function loadCustomerData()
    {
        $this->customer = CRMCustomer::findOrFail($this->customerId);
        $this->loadUnits();
    }

    public function loadInitialData()
    {
        $this->loadUnits();
        $this->loadSubUnits();
        $this->loadAreas();
        $this->loadMasterData();
    }

    public function loadUnits()
    {
        $this->units = CRMCompanyUnit::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function loadSubUnits()
    {
        $this->subUnits = CRMCompanySubUnit::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function loadAreas()
    {
        $this->areas = \App\Models\SamplePointArea::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('description')
            ->get();
    }

    public function loadMasterData()
    {
        $this->masterSamplePoints = GlobalSamplePoint::orderBy('name')->get();
        $this->globalAreas = Area::orderBy('name')->get();
    }

    public function getSamplePointsProperty()
    {
        $query = SamplePoint::with(['crmSamplePoint', 'area.crmArea', 'area.subUnit', 'area.companyUnit', 'unit'])
            ->whereHas('unit', function($q) {
                $q->where('crm_customer_id', $this->customerId)
                  ->where('active', 1);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('crmSamplePoint', function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            })
            ->orderBy('sample_point_area_id')
            ->get();

        // Group by sample_point_area_id
        return $query->groupBy('sample_point_area_id');
    }

    public function showCreateSamplePointModal()
    {
        $this->loadInitialData();
        $this->resetSamplePointForm();
        $this->editingSamplePoint = null;
        $this->showSamplePointModal = true;
    }

    public function showEditSamplePointModal($samplePointId)
    {
        $samplePoint = SamplePoint::findOrFail($samplePointId);
        
        $this->samplePointForm = [
            'unit_id' => $samplePoint->crm_company_unit_id,
            'sub_unit_id' => $samplePoint->crm_company_sub_unit_id,
            'area_id' => $samplePoint->sample_point_area_id,
            'crm_area_id' => $samplePoint->crm_area_id,
            'crm_sample_point_id' => $samplePoint->crm_sample_point_id,
            'active' => $samplePoint->active == 1
        ];
        
        $this->editingSamplePoint = $samplePoint;
        $this->showSamplePointModal = true;
    }

    public function closeSamplePointModal()
    {
        $this->showSamplePointModal = false;
        $this->resetSamplePointForm();
        $this->editingSamplePoint = null;
    }

    public function saveSamplePoint()
    {
        $this->validate();

        try {
            DB::beginTransaction();

        $data = [
            'crm_company_unit_id' => $this->samplePointForm['unit_id'],
            'crm_company_sub_unit_id' => $this->samplePointForm['sub_unit_id'],
            'sample_point_area_id' => $this->samplePointForm['area_id'],
            'crm_area_id' => $this->samplePointForm['crm_area_id'],
            'crm_sample_point_id' => $this->samplePointForm['crm_sample_point_id'],
            'crm_customer_id' => $this->customerId,
            'active' => $this->samplePointForm['active'] ? 1 : 0,
        ];

            if ($this->editingSamplePoint) {
                $this->editingSamplePoint->update($data);
                $this->message = 'Sample point updated successfully!';
            } else {
                SamplePoint::create($data);
                $this->message = 'Sample point created successfully!';
            }

            DB::commit();
            
            $this->closeSamplePointModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteSamplePoint($samplePointId)
    {
        try {
            $samplePoint = SamplePoint::findOrFail($samplePointId);
            $samplePoint->delete();
            
            $this->message = 'Sample point deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function resetSamplePointForm()
    {
        $this->samplePointForm = [
            'unit_id' => null,
            'sub_unit_id' => null,
            'area_id' => null,
            'crm_area_id' => null,
            'crm_sample_point_id' => null,
            'active' => true
        ];
        $this->editingSamplePoint = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.samples.sample-points-manager', [
            'samplePoints' => $this->samplePoints
        ]);
    }
}