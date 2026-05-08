<?php

namespace App\Livewire\Samples;

use Livewire\Component;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\Schema;

class SamplePointsManager extends Component
{
    public $customerId;
    public $customer;

    public $editingSamplePoint = null;
    public $showSamplePointModal = false;

    public $samplePointForm = [
        'unit_id' => null,
        'name' => '',
        'code' => '',
        'description' => '',
        'active' => true
    ];

    public $units = [];
    public $unitSearch = '';
    public $showUnitDropdown = false;
    public $search = '';
    public $statusFilter = '';
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'samplePointForm.unit_id' => 'required|exists:crm_company_units,id',
        'samplePointForm.name' => 'required|string|max:255',
        'samplePointForm.code' => 'nullable|string|max:50',
        'samplePointForm.description' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'samplePointForm.unit_id.required' => 'Company unit selection is required.',
        'samplePointForm.name.required' => 'Sample point name is required.',
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
        $hasName = Schema::hasColumn('sample_points', 'name');
        $hasCode = Schema::hasColumn('sample_points', 'code');
        $hasDescription = Schema::hasColumn('sample_points', 'description');
        $hasGps = Schema::hasColumn('sample_points', 'gps');

        $query = SamplePoint::with('unit')
            ->where('crm_customer_id', $this->customerId)
            ->when($this->search, function ($query) use ($hasName, $hasCode, $hasDescription, $hasGps) {
                $query->where(function ($subQuery) use ($hasName, $hasCode, $hasDescription, $hasGps) {
                    if ($hasName) {
                        $subQuery->orWhere('name', 'like', '%' . $this->search . '%');
                    }
                    if ($hasCode) {
                        $subQuery->orWhere('code', 'like', '%' . $this->search . '%');
                    }
                    if ($hasDescription) {
                        $subQuery->orWhere('description', 'like', '%' . $this->search . '%');
                    }
                    if ($hasGps) {
                        $subQuery->orWhere('gps', 'like', '%' . $this->search . '%');
                    }
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            });

        if ($hasName) {
            $query->orderBy('name');
        } elseif (Schema::hasColumn('sample_points', 'created_at')) {
            $query->orderByDesc('created_at');
        } else {
            $query->orderByDesc('id');
        }

        return $query->get();
    }

    public function showCreateSamplePointModal()
    {
        $this->loadInitialData();
        $this->resetSamplePointForm();
        $this->editingSamplePoint = null;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
        $this->showSamplePointModal = true;
    }

    public function showEditSamplePointModal($samplePointId)
    {
        $samplePoint = SamplePoint::findOrFail($samplePointId);
        
        $this->samplePointForm = [
            'unit_id' => $samplePoint->crm_company_unit_id,
            'name' => $samplePoint->name ?? $samplePoint->display_name,
            'code' => $samplePoint->code ?? '',
            'description' => $samplePoint->description ?? ($samplePoint->gps ?? ''),
            'active' => $samplePoint->active == 1
        ];
        
        $this->editingSamplePoint = $samplePoint;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
        $this->showSamplePointModal = true;
    }

    public function closeSamplePointModal()
    {
        $this->showSamplePointModal = false;
        $this->resetSamplePointForm();
        $this->editingSamplePoint = null;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
    }

    public function getSelectedUnitProperty()
    {
        $selectedId = (string) ($this->samplePointForm['unit_id'] ?? '');
        if ($selectedId === '') {
            return null;
        }

        $selected = collect($this->units)->first(function ($unit) use ($selectedId) {
            return (string) $unit->id === $selectedId;
        });

        if ($selected) {
            return $selected;
        }

        return CRMCompanyUnit::query()->find($selectedId);
    }

    public function getFilteredUnitsProperty()
    {
        $search = trim(strtolower($this->unitSearch));

        return collect($this->units)
            ->filter(function ($unit) use ($search) {
                if ((string) $unit->id === (string) ($this->samplePointForm['unit_id'] ?? '')) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower($unit->name), $search);
            })
            ->values();
    }

    public function selectUnit($unitId)
    {
        $selectedId = (string) $unitId;
        $this->samplePointForm['unit_id'] = $selectedId;

        $selected = collect($this->units)->first(function ($unit) use ($selectedId) {
            return (string) $unit->id === $selectedId;
        });

        $this->unitSearch = $selected ? (string) $selected->name : '';
        $this->showUnitDropdown = false;
    }

    public function clearUnit()
    {
        $this->samplePointForm['unit_id'] = null;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
    }

    public function saveSamplePoint()
    {
        $this->validate();

        try {
            $data = [
                'crm_company_unit_id' => $this->samplePointForm['unit_id'],
                'crm_customer_id' => $this->customerId,
                'active' => $this->samplePointForm['active'] ? 1 : 0,
                'crm_company_sub_unit_id' => null,
                'sample_point_area_id' => null,
                'crm_area_id' => null,
                'crm_sample_point_id' => null,
            ];

            // Legacy `sample_points` schema may not include these columns.
            if (Schema::hasColumn('sample_points', 'name')) {
                $data['name'] = $this->samplePointForm['name'];
            }
            if (Schema::hasColumn('sample_points', 'code')) {
                $data['code'] = $this->samplePointForm['code'];
            }
            if (Schema::hasColumn('sample_points', 'description')) {
                $data['description'] = $this->samplePointForm['description'];
            }
            if (Schema::hasColumn('sample_points', 'gps')) {
                $data['gps'] = $this->samplePointForm['description'] ?: $this->samplePointForm['name'];
            }

            if ($this->editingSamplePoint) {
                $this->editingSamplePoint->update($data);
                $this->message = 'Sample point updated successfully!';
            } else {
                SamplePoint::create($data);
                $this->message = 'Sample point created successfully!';
            }
            
            $this->closeSamplePointModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
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
    }

    public function resetSamplePointForm()
    {
        $this->samplePointForm = [
            'unit_id' => null,
            'name' => '',
            'code' => '',
            'description' => '',
            'active' => true
        ];
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
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