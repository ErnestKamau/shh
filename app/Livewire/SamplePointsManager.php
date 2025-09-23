<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CRMCompanyUnit;
use Illuminate\Support\Facades\DB;

class SamplePointsManager extends Component
{
    // Customer Data
    public $customerId;
    public $customer;
    
    // Point Management
    public $editingPoint = null;
    public $showPointModal = false;
    
    // Point Form
    public $pointForm = [
        'name' => '',
        'crm_company_unit_id' => null,
        'active' => true,
        'gps' => ''
    ];

    // Supporting Data
    public $units = [];

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'pointForm.name' => 'required|string|max:255',
        'pointForm.crm_company_unit_id' => 'required|exists:crm_company_units,id',
    ];

    protected $messages = [
        'pointForm.name.required' => 'Sample point name is required.',
        'pointForm.crm_company_unit_id.required' => 'Unit selection is required.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = \App\Models\CRM\CRMCustomer::findOrFail($customerId);
        $this->loadUnits();
    }

    public function loadUnits()
    {
        $this->units = CRMCompanyUnit::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getSamplePointsProperty()
    {
        return SamplePoint::with('unit')
            ->whereHas('unit', function($query) {
                $query->where('crm_customer_id', $this->customerId);
            })
            ->orderBy('name')
            ->get();
    }

    public function showCreatePointModal()
    {
        $this->resetPointForm();
        $this->showPointModal = true;
    }

    public function showEditPointModal($id)
    {
        $point = SamplePoint::findOrFail($id);
        
        $this->pointForm = [
            'name' => $point->name,
            'crm_company_unit_id' => $point->crm_company_unit_id,
            'active' => $point->active == 1,
            'gps' => $point->gps ?? ''
        ];
        
        $this->editingPoint = $point;
        $this->showPointModal = true;
    }

    public function savePoint()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingPoint) {
                // Update existing point
                $point = $this->editingPoint;
            } else {
                // Create new point
                $point = new SamplePoint();
            }

            $point->name = $this->pointForm['name'];
            $point->crm_company_unit_id = $this->pointForm['crm_company_unit_id'];
            $point->active = $this->pointForm['active'] ? 1 : 0;
            $point->gps = $this->pointForm['gps'];
            $point->save();

            DB::commit();
            
            $this->closePointModal();
            $this->message = $this->editingPoint ? 'Sample point updated successfully!' : 'Sample point created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deletePoint($id)
    {
        try {
            DB::beginTransaction();

            $point = SamplePoint::findOrFail($id);
            
            // Soft delete - set active to 0
            $point->active = 0;
            $point->save();

            DB::commit();
            
            $this->message = 'Sample point deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closePointModal()
    {
        $this->showPointModal = false;
        $this->resetPointForm();
    }

    public function resetPointForm()
    {
        $this->pointForm = [
            'name' => '',
            'crm_company_unit_id' => null,
            'active' => true,
            'gps' => ''
        ];
        $this->editingPoint = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.sample-points-manager');
    }
}

