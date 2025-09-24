<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
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
        'name' => '',
        'description' => '',
        'unit_id' => null,
        'area_id' => null,
        'active' => true
    ];

    // Supporting Data
    public $units = [];
    public $areas = [];

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
        'samplePointForm.name' => 'required|string|max:255',
        'samplePointForm.unit_id' => 'required|exists:crm_company_units,id',
        'samplePointForm.area_id' => 'nullable|exists:sample_point_area,id',
    ];

    protected $messages = [
        'samplePointForm.name.required' => 'Sample point name is required.',
        'samplePointForm.unit_id.required' => 'Company unit selection is required.',
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
        $this->loadAreas();
    }

    public function loadUnits()
    {
        $this->units = CRMCompanyUnit::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function loadAreas()
    {
        $this->areas = \App\Models\SamplePointArea::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getSamplePointsProperty()
    {
        $query = SamplePoint::join('crm_company_units', 'sample_points.crm_company_unit_id', '=', 'crm_company_units.id')
            ->leftJoin('sample_point_area', 'sample_points.sample_point_area_id', '=', 'sample_point_area.id')
            ->where('crm_company_units.crm_customer_id', $this->customerId)
            ->where('crm_company_units.active', 1)
            ->select('sample_points.*', 'crm_company_units.name as unit_name', 'sample_point_area.name as area_name')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('sample_points.name', 'like', '%' . $this->search . '%')
                      ->orWhere('sample_points.description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('sample_points.active', $this->statusFilter);
            })
            ->orderBy('sample_points.name');

        return $query->paginate($this->perPage);
    }

    public function showCreateSamplePointModal()
    {
        $this->resetSamplePointForm();
        $this->editingSamplePoint = null;
        $this->showSamplePointModal = true;
    }

    public function showEditSamplePointModal($samplePointId)
    {
        $samplePoint = SamplePoint::findOrFail($samplePointId);
        
        $this->samplePointForm = [
            'name' => $samplePoint->name,
            'description' => $samplePoint->description ?? '',
            'unit_id' => $samplePoint->crm_company_unit_id,
            'area_id' => $samplePoint->sample_point_area_id,
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
            'name' => $this->samplePointForm['name'],
            'description' => $this->samplePointForm['description'],
            'crm_company_unit_id' => $this->samplePointForm['unit_id'],
            'sample_point_area_id' => $this->samplePointForm['area_id'],
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
            'name' => '',
            'description' => '',
            'unit_id' => null,
            'area_id' => null,
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