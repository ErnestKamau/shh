<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CRM\CRMCompanyUnit;
use Illuminate\Support\Facades\DB;

class CompanyUnitsManager extends Component
{
    // Customer Data
    public $customerId;
    public $customer;
    
    // Unit Management
    public $editingUnit = null;
    public $showUnitModal = false;
    
    // Unit Form
    public $unitForm = [
        'name' => '',
        'active' => true
    ];

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'unitForm.name' => 'required|string|max:255',
    ];

    protected $messages = [
        'unitForm.name.required' => 'Unit name is required.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = \App\Models\CRM\CRMCustomer::findOrFail($customerId);
    }

    public function getUnitsProperty()
    {
        return CRMCompanyUnit::where('crm_customer_id', $this->customerId)
            ->orderBy('name')
            ->get();
    }

    public function showCreateUnitModal()
    {
        $this->resetUnitForm();
        $this->showUnitModal = true;
    }

    public function showEditUnitModal($id)
    {
        $unit = CRMCompanyUnit::findOrFail($id);
        
        $this->unitForm = [
            'name' => $unit->name,
            'active' => $unit->active == 1
        ];
        
        $this->editingUnit = $unit;
        $this->showUnitModal = true;
    }

    public function saveUnit()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingUnit) {
                // Update existing unit
                $unit = $this->editingUnit;
            } else {
                // Create new unit
                $unit = new CRMCompanyUnit();
                $unit->crm_customer_id = $this->customerId;
            }

            $unit->name = $this->unitForm['name'];
            $unit->active = $this->unitForm['active'] ? 1 : 0;
            $unit->save();

            DB::commit();
            
            $this->closeUnitModal();
            $this->message = $this->editingUnit ? 'Unit updated successfully!' : 'Unit created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteUnit($id)
    {
        try {
            DB::beginTransaction();

            $unit = CRMCompanyUnit::findOrFail($id);
            
            // Soft delete - set active to 0
            $unit->active = 0;
            $unit->save();

            // Cascade effects on sample points
            $unit->sample_points()->update(['active' => 0]);

            DB::commit();
            
            $this->message = 'Unit deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeUnitModal()
    {
        $this->showUnitModal = false;
        $this->resetUnitForm();
    }

    public function resetUnitForm()
    {
        $this->unitForm = [
            'name' => '',
            'active' => true
        ];
        $this->editingUnit = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.company-units-manager');
    }
}

