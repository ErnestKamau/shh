<?php

namespace App\Livewire\Equipment\Workflow;

use Livewire\Component;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflow;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflowStep;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use App\User;
use App\Role;
use Illuminate\Support\Facades\DB;

class WorkflowForm extends Component
{
    public $workflowId;
    public $workflow_name;
    public $description;
    public $equipment_type_id;
    public $location_id;
    public $is_active = true;
    
    // Steps
    public $steps = [];

    // Reference Data
    public $users;
    public $roles;

    protected $rules = [
        'workflow_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'equipment_type_id' => 'nullable|integer|exists:asset_types,id',
        'location_id' => 'nullable|integer|exists:asset_locations,id',
        'is_active' => 'boolean',
        'steps' => 'array',
        'steps.*.step_name' => 'required|string|max:255',
        'steps.*.assignee_type' => 'required|string',
        'steps.*.assignee_id' => 'required|integer',
        'steps.*.is_required' => 'boolean',
    ];

    public function mount($id = null)
    {
        // Load Users and Roles
        $this->users = User::where('company_id', getUserCompany())->where('active', 1)->orderBy('name')->get();
        // Assuming Roles don't have company_id (or are global), if they do, add filter
        $this->roles = Role::orderBy('name')->get();

        if ($id) {
            $this->workflowId = $id;
            $workflow = EquipmentDisposalApprovalWorkflow::with('steps')->findOrFail($id);
            
            $this->workflow_name = $workflow->workflow_name;
            $this->description = $workflow->description;
            $this->equipment_type_id = $workflow->equipment_type_id;
            $this->location_id = $workflow->location_id;
            $this->is_active = $workflow->is_active;
            
            foreach ($workflow->steps as $step) {
                $this->steps[] = [
                    'id' => $step->id, // Keep ID for updates
                    'step_name' => $step->step_name,
                    'assignee_type' => $step->assignee_type,
                    'assignee_id' => $step->assignee_id,
                    'is_required' => $step->is_required,
                ];
            }
        } else {
            // Default first step
            $this->addStep();
        }
    }

    public function addStep()
    {
        $this->steps[] = [
            'id' => null,
            'step_name' => 'Approval Step ' . (count($this->steps) + 1),
            'assignee_type' => 'App\User',
            'assignee_id' => '',
            'is_required' => true,
        ];
    }

    public function removeStep($index)
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps); // Reindex
    }

    public function moveStepUp($index)
    {
        if ($index > 0) {
            $temp = $this->steps[$index];
            $this->steps[$index] = $this->steps[$index - 1];
            $this->steps[$index - 1] = $temp;
        }
    }

    public function moveStepDown($index)
    {
        if ($index < count($this->steps) - 1) {
            $temp = $this->steps[$index];
            $this->steps[$index] = $this->steps[$index + 1];
            $this->steps[$index + 1] = $temp;
        }
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'workflow_name' => $this->workflow_name,
                'description' => $this->description,
                'equipment_type_id' => $this->equipment_type_id ?: null,
                'location_id' => $this->location_id ?: null,
                'is_active' => $this->is_active,
                'company_id' => getUserCompany(),
            ];

            if ($this->workflowId) {
                $workflow = EquipmentDisposalApprovalWorkflow::findOrFail($this->workflowId);
                $workflow->update($data);
            } else {
                $data['created_by'] = auth()->id();
                $workflow = EquipmentDisposalApprovalWorkflow::create($data);
            }

            // Handle Steps
            // Get existing step IDs to know what to keep/delete
            $existingStepIds = $workflow->steps->pluck('id')->toArray();
            $processedStepIds = [];

            foreach ($this->steps as $index => $stepData) {
                $stepAttributes = [
                    'workflow_id' => $workflow->id,
                    'step_order' => $index + 1,
                    'step_name' => $stepData['step_name'],
                    'assignee_type' => $stepData['assignee_type'],
                    'assignee_id' => $stepData['assignee_id'],
                    'is_required' => $stepData['is_required'],
                ];

                if (isset($stepData['id']) && $stepData['id']) {
                    EquipmentDisposalApprovalWorkflowStep::where('id', $stepData['id'])->update($stepAttributes);
                    $processedStepIds[] = $stepData['id'];
                } else {
                    $newStep = EquipmentDisposalApprovalWorkflowStep::create($stepAttributes);
                    $processedStepIds[] = $newStep->id;
                }
            }

            // Delete removed steps
            $stepsToDelete = array_diff($existingStepIds, $processedStepIds);
            if (!empty($stepsToDelete)) {
                EquipmentDisposalApprovalWorkflowStep::whereIn('id', $stepsToDelete)->delete();
            }

            DB::commit();

            session()->flash('message', 'Workflow saved successfully.');
            return redirect()->route('equipment.disposal.workflow.index');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error saving workflow: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $assetTypes = AssetType::all();
        $locations = AssetLocation::all();

        return view('livewire.equipment.workflow.workflow-form', [
            'assetTypes' => $assetTypes,
            'locations' => $locations,
        ]);
    }
}
