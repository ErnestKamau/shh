<?php

namespace App\Livewire\Procedures;

use Livewire\Component;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\Equipments\Equipment;
use App\ReportingUnit;
use App\User;
use Livewire\WithPagination;

class ProcedureWorksheetEditor extends Component
{
    use WithPagination;

    public $worksheetId;
    public $search = '';
    public $perPage = 25;
    
    // Modal properties
    public $showModal = false;
    public $editingStepId = null;
    public $showDeleteModal = false;
    public $stepIdToDelete = null;

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

    public function mount($procedureWorksheet)
    {
        $this->worksheetId = $procedureWorksheet->id;
    }

    public function render()
    {
        $worksheet = ProcedureWorksheet::findOrFail($this->worksheetId);
        
        $query = $worksheet->steps();

        if ($this->search) {
            $query->where('step', 'like', '%' . $this->search . '%');
        }

        $steps = $query->orderBy('order', 'asc')->paginate($this->perPage);

        // Fetch data for dropdowns
        $equipments = [];
        if ($this->showEquipmentDropdown) {
            $equipments = Equipment::where('name', 'like', '%' . $this->equipmentSearch . '%')
                ->orWhere('equipment_number', 'like', '%' . $this->equipmentSearch . '%')
                ->limit(10)->get();
        }

        $analysts = [];
        if ($this->showAnalystDropdown) {
            $analysts = User::where('name', 'like', '%' . $this->analystSearch . '%')
                ->where('active', 1)
                ->limit(10)->get();
        }

        $measurands = [];
        if ($this->showMeasurandDropdown) {
            $measurands = ReportingUnit::where('name', 'like', '%' . $this->measurandSearch . '%')
                ->limit(10)->get();
        }

        $selectedEquipment = $this->default_equipment_id ? Equipment::find($this->default_equipment_id) : null;
        $selectedAnalyst = $this->default_analyst_id ? User::find($this->default_analyst_id) : null;
        $selectedMeasurands = ReportingUnit::whereIn('id', $this->default_measurand_ids)->get();

        return view('livewire.procedures.procedure-worksheet-editor', [
            'worksheet' => $worksheet,
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
        $step = ProcedureWorksheetStep::findOrFail($id);
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

        ProcedureWorksheetStep::updateOrCreate(
            ['id' => $this->editingStepId],
            [
                'procedure_worksheet_id' => $this->worksheetId,
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

    public function confirmDelete($id)
    {
        $this->stepIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteStep()
    {
        if ($this->stepIdToDelete) {
            $step = ProcedureWorksheetStep::find($this->stepIdToDelete);
            if ($step) {
                $step->delete();
                session()->flash('message', 'Step deleted successfully.');
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
        $step = ProcedureWorksheetStep::find($id);
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

    public function updateStepOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            if ($step = ProcedureWorksheetStep::find($id)) {
                $step->order = $index + 1;
                $step->save();
            }
        }
    }
}
