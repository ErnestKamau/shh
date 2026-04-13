<?php

namespace App\Livewire\CRM\Customer;

use App\Models\CRM\CRMCompanyUnit;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\Attributes\On;

class UnitForm extends BaseCrmComponent
{
    public $unitId = null;
    public $customerId;
    public $name = '';
    public $active = true; // Default to active (boolean)

    public function mount($customerId, $unitId = null)
    {
        $this->initialize();
        $this->customerId = $customerId;

        if ($unitId) {
            $this->unitId = $unitId;
            $unit = CRMCompanyUnit::find($unitId);
            if ($unit) {
                $this->name = $unit->name;
                $this->active = (bool) $unit->active;
            }
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        if ($this->unitId) {
            $this->checkPermission('CRM.components.Company-Units.Edit');
            $unit = CRMCompanyUnit::find($this->unitId);
        } else {
            $this->checkPermission('CRM.components.Company-Units.Add');
            $unit = new CRMCompanyUnit();
            $unit->crm_customer_id = $this->customerId;
            $unit->company_id = $this->getUserCompany();
        }

        $unit->name = $this->name;
        $unit->active = $this->active ? 1 : 0;
        $unit->save();

        $this->showSuccess($this->unitId ? 'Company Unit updated successfully.' : 'Company Unit added successfully.');
        $this->dispatch('unit-saved');
        $this->close();
    }

    public function close()
    {
        $this->dispatch('unit-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.customer.unit-form');
    }
}
