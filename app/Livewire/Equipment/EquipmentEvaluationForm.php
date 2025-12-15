<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentEvaluation;
use App\Services\Equipment\EquipmentEvaluationService;
use Illuminate\Support\Facades\DB;

class EquipmentEvaluationForm extends Component
{
    use WithFileUploads;

    public $equipmentId = null;
    public $equipment = null;
    public $evaluationId = null;
    public $evaluation = null;
    public $showModal = false;

    // Form data
    public $form = [
        'evaluation_date' => '',
        'physical_condition' => '',
        'last_calibration_date' => '',
        'last_calibration_status' => '',
        'repair_cost_estimate' => '',
        'replacement_cost_estimate' => '',
        'impact_on_testing' => '',
        'recommendation' => '',
        'fault_report_reference' => '',
        'evaluation_notes' => '',
    ];

    // Auto-filled data
    public $calibrationHistory = [];
    public $maintenanceHistory = [];
    public $costComparison = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $evaluationService;

    protected function rules(): array
    {
        return [
            'form.evaluation_date' => 'required|date',
            'form.physical_condition' => 'required|in:excellent,good,fair,poor,failed',
            'form.last_calibration_date' => 'nullable|date',
            'form.last_calibration_status' => 'nullable|in:pass,fail,not_applicable',
            'form.repair_cost_estimate' => 'nullable|numeric|min:0',
            'form.replacement_cost_estimate' => 'nullable|numeric|min:0',
            'form.impact_on_testing' => 'nullable|string',
            'form.recommendation' => 'required|in:repair,dispose,continue_use',
            'form.fault_report_reference' => 'nullable|string|max:255',
            'form.evaluation_notes' => 'nullable|string',
        ];
    }

    public function boot(EquipmentEvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function mount(?int $equipmentId = null, ?int $evaluationId = null): void
    {
        if ($evaluationId) {
            $this->loadEvaluation($evaluationId);
        } elseif ($equipmentId) {
            $this->equipmentId = $equipmentId;
            $this->loadEquipment();
        }

        // Set default evaluation date
        if (!$this->form['evaluation_date']) {
            $this->form['evaluation_date'] = date('Y-m-d');
        }
    }

    public function loadEquipment(): void
    {
        if (!$this->equipmentId) {
            return;
        }

        $this->equipment = Equipment::with(['maintainance_Calibration_logs'])->find($this->equipmentId);

        if (!$this->equipment) {
            $this->message = 'Equipment not found.';
            $this->messageType = 'danger';
            return;
        }

        // Auto-fill calibration history
        $this->calibrationHistory = $this->evaluationService->getCalibrationHistory($this->equipment, 5);
        $this->maintenanceHistory = $this->evaluationService->getMaintenanceHistory($this->equipment, 5);

        // Auto-fill last calibration date
        $lastCalibration = $this->evaluationService->getLastCalibration($this->equipment);
        if ($lastCalibration) {
            $this->form['last_calibration_date'] = $lastCalibration->date->format('Y-m-d');
        }

        // Set default replacement cost from market value
        if ($this->equipment->market_value) {
            $this->form['replacement_cost_estimate'] = $this->equipment->market_value;
        }
    }

    public function loadEvaluation(int $evaluationId): void
    {
        $this->evaluation = EquipmentEvaluation::with(['equipment'])->find($evaluationId);

        if (!$this->evaluation) {
            $this->message = 'Evaluation not found.';
            $this->messageType = 'danger';
            return;
        }

        $this->evaluationId = $evaluationId;
        $this->equipmentId = $this->evaluation->equipment_id;
        $this->equipment = $this->evaluation->equipment;

        // Load form data
        $this->form = [
            'evaluation_date' => $this->evaluation->evaluation_date->format('Y-m-d'),
            'physical_condition' => $this->evaluation->physical_condition,
            'last_calibration_date' => $this->evaluation->last_calibration_date?->format('Y-m-d') ?? '',
            'last_calibration_status' => $this->evaluation->last_calibration_status,
            'repair_cost_estimate' => $this->evaluation->repair_cost_estimate,
            'replacement_cost_estimate' => $this->evaluation->replacement_cost_estimate,
            'impact_on_testing' => $this->evaluation->impact_on_testing,
            'recommendation' => $this->evaluation->recommendation,
            'fault_report_reference' => $this->evaluation->fault_report_reference,
            'evaluation_notes' => $this->evaluation->evaluation_notes,
        ];

        // Load histories
        $this->calibrationHistory = $this->evaluationService->getCalibrationHistory($this->equipment, 5);
        $this->maintenanceHistory = $this->evaluationService->getMaintenanceHistory($this->equipment, 5);
    }

    public function openModal(?int $equipmentId = null): void
    {
        $this->resetForm();
        if ($equipmentId) {
            $this->equipmentId = $equipmentId;
            $this->loadEquipment();
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->form = [
            'evaluation_date' => date('Y-m-d'),
            'physical_condition' => '',
            'last_calibration_date' => '',
            'last_calibration_status' => '',
            'repair_cost_estimate' => '',
            'replacement_cost_estimate' => '',
            'impact_on_testing' => '',
            'recommendation' => '',
            'fault_report_reference' => '',
            'evaluation_notes' => '',
        ];
        $this->equipmentId = null;
        $this->evaluationId = null;
        $this->equipment = null;
        $this->evaluation = null;
        $this->calibrationHistory = [];
        $this->maintenanceHistory = [];
        $this->costComparison = [];
        $this->message = '';
        $this->messageType = 'success';
    }

    public function updatedFormRepairCostEstimate(): void
    {
        $this->calculateCostComparison();
    }

    public function updatedFormReplacementCostEstimate(): void
    {
        $this->calculateCostComparison();
    }

    protected function calculateCostComparison(): void
    {
        if ($this->form['repair_cost_estimate'] && $this->form['replacement_cost_estimate']) {
            $this->costComparison = $this->evaluationService->calculateRepairVsReplacementCost(
                $this->equipment ?? new Equipment(),
                (float) $this->form['repair_cost_estimate'],
                (float) $this->form['replacement_cost_estimate']
            );
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = $this->form;
            $data['equipment_id'] = $this->equipmentId;
            $data['evaluated_by'] = auth()->id();
            $data['company_id'] = getUserCompany();

            if ($this->evaluationId && $this->evaluation) {
                // Update existing evaluation
                $this->evaluation->update($data);
                $this->message = 'Evaluation updated successfully!';
            } else {
                // Create new evaluation
                $this->evaluation = $this->evaluationService->createEvaluation($this->equipment, $data);
                $this->evaluationId = $this->evaluation->id;
                $this->message = 'Evaluation created successfully!';
            }

            $this->messageType = 'success';
            DB::commit();

            // Emit event to refresh parent component
            $this->dispatch('evaluation-saved', ['evaluation_id' => $this->evaluation->id]);

            // Close modal after short delay
            $this->showModal = false;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error saving evaluation: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function render()
    {
        return view('livewire.equipment.equipment-evaluation-form');
    }
}

