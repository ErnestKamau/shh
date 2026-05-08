<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\SamplePoint;
use App\Models\CRM\CRMCompanyUnit;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;

class SamplePointForm extends BaseCrmComponent
{
    public $pointId = null;
    public $customerId;
    public $name = '';
    public $description = '';
    public $unitId = '';
    public $active = true;
    public $longitude = '';
    public $latitude = '';
    public $unitSearch = '';
    public $showUnitDropdown = false;

    public $units = [];

    public function getSelectedUnitProperty()
    {
        return collect($this->units)->firstWhere('id', (int) $this->unitId);
    }

    public function getFilteredUnitsProperty()
    {
        $search = trim(strtolower($this->unitSearch));

        return collect($this->units)
            ->when($search !== '', function ($units) use ($search) {
                return $units->filter(function ($unit) use ($search) {
                    return str_contains(strtolower($unit->name), $search);
                });
            })
            ->take(50)
            ->values();
    }

    public function selectUnit($unitId)
    {
        $this->unitId = (string) $unitId;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
    }

    public function clearUnit()
    {
        $this->unitId = '';
        $this->unitSearch = '';
    }

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
                $this->description = $point->description;
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
            'description' => 'nullable|string|max:1000',
            'unitId' => [
                'required',
                Rule::exists('crm_company_units', 'id')->where(function ($query) {
                    $query->where('crm_customer_id', $this->customerId);
                }),
            ],
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
            $this->checkPermission('crm.components.sample-points.edit');
            $point = SamplePoint::where('id', $this->pointId)
                ->where('crm_customer_id', $this->customerId)
                ->first();

            if (!$point) {
                $this->showError('Sample point not found for this customer.');
                return;
            }
        } else {
            $this->checkPermission('crm.components.sample-points.add');
            $point = new SamplePoint();
        }

        $point->name = $this->name;
        $point->description = $this->description;
        $point->crm_customer_id = $this->customerId;
        $point->crm_company_unit_id = $this->unitId;
        $point->crm_company_sub_unit_id = null;
        $point->sample_point_area_id = null;
        $point->crm_area_id = null;
        $point->crm_sample_point_id = null;
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
