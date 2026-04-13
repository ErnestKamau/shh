<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\SamplePoint;
use App\Models\CRM\CRMCompanyUnit;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\Attributes\On;

class SamplePointForm extends BaseCrmComponent
{
    public $pointId = null;
    public $customerId;
    public $name = '';
    public $unitId = '';
    public $active = true;
    public $longitude = '';
    public $latitude = '';

    public $units = [];

    public function mount($customerId, $pointId = null)
    {
        $this->initialize();
        $this->customerId = $customerId;

        // Load units for this customer
        $this->units = CRMCompanyUnit::where('crm_customer_id', $customerId)->orderBy('name')->get();

        if ($pointId) {
            $this->pointId = $pointId;
            $point = SamplePoint::find($pointId);
            if ($point) {
                $this->name = $point->name;
                $this->unitId = $point->crm_company_unit_id;
                $this->active = (bool) $point->active;

                // Parse GPS
                if ($point->gps) {
                    $gpsParts = explode(',', $point->gps);
                    if (count($gpsParts) >= 2) {
                        $this->longitude = $gpsParts[0];
                        $this->latitude = $gpsParts[1];
                    }
                }
            }
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'unitId' => 'required|exists:crm_company_units,id',
            'active' => 'boolean',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
        ];
    }

    public function save()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('SamplePointForm: Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        if ($this->pointId) {
            $this->checkPermission('CRM.components.Sample-Points.Edit');
            $point = SamplePoint::find($this->pointId);
        } else {
            $this->checkPermission('CRM.components.Sample-Points.Add');
            $point = new SamplePoint();
        }

        $point->name = $this->name;
        $point->crm_company_unit_id = $this->unitId;
        $point->active = $this->active ? 1 : 0;

        if ($this->longitude || $this->latitude) {
            $point->gps = $this->longitude . ',' . $this->latitude;
        }

        $point->save();

        $this->showSuccess($this->pointId ? 'Sample Point updated successfully.' : 'Sample Point added successfully.');
        $this->dispatch('sample-point-saved');
        $this->close();
    }

    public function close()
    {
        $this->dispatch('sample-point-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.customer.sample-point-form');
    }
}
