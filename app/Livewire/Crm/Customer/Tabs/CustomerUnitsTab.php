<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Models\CRM\CRMCompanySection;
use App\Models\CRM\CRMCompanyUnit;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\WithPagination;

class CustomerUnitsTab extends BaseCrmComponent
{
    use WithPagination;

    public $customer;
    public $search = '';
    public $perPage = 10;

    // Form properties
    public $unit_id;
    public $name;

    public $active = true;

    public $showForm = false;
    public $editingUnit = null;

    protected $rules = [
        'name' => 'required|string|max:255',

        'active' => 'boolean',
    ];

    public $isEditingLabel = false;
    public $labelColumn = '';
    public $customLabel = '';

    public function editLabel($column)
    {
        $this->labelColumn = $column;
        $this->customLabel = $this->customer->$column;
        $this->isEditingLabel = true;
    }

    public function cancelEditLabel()
    {
        $this->isEditingLabel = false;
        $this->labelColumn = '';
        $this->customLabel = '';
    }

    public function saveLabel()
    {
        $this->validate([
            'customLabel' => 'required|string|max:255',
        ]);

        if ($this->labelColumn) {
            $column = $this->labelColumn;
            $this->customer->$column = $this->customLabel;
            $this->customer->save();
            $this->dispatch('customer-updated');
            $this->showSuccess('Label updated successfully');
        }

        $this->cancelEditLabel();
    }

    protected $paginationTheme = 'bootstrap';

    public function mount($customer)
    {
        $this->customer = $customer;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }



    public function getUnitsProperty()
    {
        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $this->customer->id)
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function openUnitForm(?string $unitId = null): void
    {
        $this->resetValidation();
        
        if ($unitId) {
            $this->editingUnit = CRMCompanyUnit::find($unitId);

            if (! $this->editingUnit) {
                $this->showError('Company unit not found.');

                return;
            }

            $this->unit_id = $this->editingUnit->id;
            $this->name = $this->editingUnit->name;

            $this->active = $this->editingUnit->active == 1;
        } else {
            $this->editingUnit = null;
            $this->unit_id = null;
            $this->name = '';

            $this->active = true;
        }
        
        $this->showForm = true;
        $this->dispatch('show-unit-modal');
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->editingUnit = null;
        $this->dispatch('hide-unit-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->unit_id) {
            $unit = CRMCompanyUnit::find($this->unit_id);
            $message = 'Company Unit updated successfully.';
        } else {
            $unit = new CRMCompanyUnit();
            $unit->crm_customer_id = $this->customer->id;
            $unit->company_id = $this->getUserCompany(); 
            $message = 'Company Unit added successfully.';
        }

        $unit->name = $this->name;

        $unit->active = $this->active ? 1 : 0;
        $unit->save();

        $this->showSuccess($message);
        $this->resetPage();
        $this->closeForm();
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'units', $this->search))
            ->download('customer_units_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-units-tab', [
            'units' => $this->units,
        ]);
    }
}
