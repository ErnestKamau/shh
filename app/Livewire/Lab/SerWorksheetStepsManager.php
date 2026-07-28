<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use App\Models\SerWorksheetStep;
use App\Models\Equipments\Equipment;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\ReportingUnit;
use App\User;
use Livewire\WithPagination;

class SerWorksheetStepsManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    
    // Modal properties
    public $showModal = false;
    public $editingStepId = null;
    
    // Form properties
    public $step = '';
    public $is_active = true;
    public $default_equipment_id = null;
    public $default_analyst_id = null;
    public $default_measurand_ids = [];
    
    // Search properties for dropdowns
    public $equipmentSearch = '';
    public $analystSearch = '';
    public $measurandSearch = '';
    
    // Dropdown visibility
    public $showEquipmentDropdown = false;
    public $showAnalystDropdown = false;
    public $showMeasurandDropdown = false;

    protected $rules = [
        'step' => 'required|string|max:255',
        'default_equipment_id' => 'nullable|exists:equipment,id',
        'default_analyst_id' => 'nullable|exists:users,id',
        'default_measurand_ids' => 'nullable|array',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = SerWorksheetStep::query();

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['step'], (string) $this->search);
        }

        $steps = $query->latest()->paginate($this->perPage);

        // Fetch data for dropdowns
        $equipments = [];
        if ($this->showEquipmentDropdown) {
            $equipmentsQuery = Equipment::query();
            $this->applyCaseInsensitiveSearch($equipmentsQuery, ['name', 'equipment_number'], (string) $this->equipmentSearch);
            $equipments = $equipmentsQuery->limit(10)->get();
        }

        $analysts = [];
        if ($this->showAnalystDropdown) {
            $analystsQuery = User::query()->where('active', 1);
            $this->applyCaseInsensitiveSearch($analystsQuery, ['name'], (string) $this->analystSearch);
            $analysts = $analystsQuery->limit(10)->get();
        }

        $measurands = [];
        if ($this->showMeasurandDropdown) {
            $measurandsQuery = ReportingUnit::query();
            $this->applyCaseInsensitiveSearch($measurandsQuery, ['name'], (string) $this->measurandSearch);
            $measurands = $measurandsQuery->limit(10)->get();
        }

        $selectedEquipment = $this->default_equipment_id ? Equipment::find($this->default_equipment_id) : null;
        $selectedAnalyst = $this->default_analyst_id ? User::find($this->default_analyst_id) : null;
        $selectedMeasurands = ReportingUnit::whereIn('id', $this->default_measurand_ids)->get();

        return view('livewire.lab.ser-worksheet-steps-manager', [
            'steps' => $steps,
            'equipments' => $equipments,
            'analysts' => $analysts,
            'measurands' => $measurands,
            'selectedEquipment' => $selectedEquipment,
            'selectedAnalyst' => $selectedAnalyst,
            'selectedMeasurands' => $selectedMeasurands,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $step = SerWorksheetStep::findOrFail($id);
        $this->editingStepId = $id;
        $this->step = $step->step;
        $this->is_active = $step->is_active;
        $this->default_equipment_id = $step->default_equipment_id;
        $this->default_analyst_id = $step->default_analyst_id;
        $this->default_measurand_ids = $step->default_measurand_ids ?? [];
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        SerWorksheetStep::updateOrCreate(
            ['id' => $this->editingStepId],
            [
                'step' => $this->step,
                'is_active' => $this->is_active,
                'default_equipment_id' => $this->default_equipment_id,
                'default_analyst_id' => $this->default_analyst_id,
                'default_measurand_ids' => $this->default_measurand_ids,
            ]
        );

        $this->showModal = false;
        $this->resetForm();
        session()->flash('message', 'Step saved successfully.');
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $this->confirmDelete($id);
    }

    public $showDeleteModal = false;
    public $stepIdToDelete = null;

    public function confirmDelete($id)
    {
        $this->stepIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteStep()
    {
        if ($this->stepIdToDelete) {
            $step = SerWorksheetStep::find($this->stepIdToDelete);
            if ($step) {
                // User requested to set active to 0 instead of hard delete
                $step->is_active = false;
                $step->save();
                session()->flash('message', 'Step deactivated successfully.');
            }
        }
        $this->showDeleteModal = false;
        $this->stepIdToDelete = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->stepIdToDelete = null;
    }

    public function toggleActive($id)
    {
        $step = SerWorksheetStep::find($id);
        $step->is_active = !$step->is_active;
        $step->save();
    }

    public function resetForm()
    {
        $this->editingStepId = null;
        $this->step = '';
        $this->is_active = true;
        $this->default_equipment_id = null;
        $this->default_analyst_id = null;
        $this->default_measurand_ids = [];
        $this->equipmentSearch = '';
        $this->analystSearch = '';
        $this->measurandSearch = '';
    }

    // Selection Methods
    public function selectEquipment($id)
    {
        $this->default_equipment_id = $id;
        $this->showEquipmentDropdown = false;
    }

    public function selectAnalyst($id)
    {
        $this->default_analyst_id = $id;
        $this->showAnalystDropdown = false;
    }

    public function toggleMeasurand($id)
    {
        if (in_array($id, $this->default_measurand_ids)) {
            $this->default_measurand_ids = array_diff($this->default_measurand_ids, [$id]);
        } else {
            $this->default_measurand_ids[] = $id;
        }
    }
    
    public function removeMeasurand($id)
    {
        $this->default_measurand_ids = array_diff($this->default_measurand_ids, [$id]);
    }
}
