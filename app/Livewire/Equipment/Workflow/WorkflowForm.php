<?php

namespace App\Livewire\Equipment\Workflow;

use Livewire\Component;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflow;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflowStep;
use App\Models\Assets\AssetType;
use App\Models\Assets\AssetLocation;
use App\Models\Auth\Role as AuthRole;
use App\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRole;

class WorkflowForm extends Component
{
    public $workflowId;
    public $workflow_name;
    public $description;
    public $equipment_type_id;
    public $location_id;
    public $is_active = true;
    
    // Tag Select states for Asset Type
    public $showAssetTypeDropdown = false;
    public $assetTypeSearch = '';
    public $selectedAssetTypeName = '';

    // Tag Select states for Location
    public $showLocationDropdown = false;
    public $locationSearch = '';
    public $selectedLocationName = '';

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
        'steps.*.assignee_ids' => 'nullable|array',
        'steps.*.is_required' => 'boolean',
    ];

    public function mount($id = null)
    {
        // Load Users and Roles
        $this->users = User::where('company_id', getUserCompany())->where('active', 1)->orderBy('name')->get();
        $this->roles = AuthRole::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        if ($id) {
            $this->workflowId = $id;
            $workflow = EquipmentDisposalApprovalWorkflow::with(['steps', 'equipmentType', 'location'])->findOrFail($id);
            
            $this->workflow_name = $workflow->workflow_name;
            $this->description = $workflow->description;
            $this->equipment_type_id = $workflow->equipment_type_id;
            $this->location_id = $workflow->location_id;
            $this->is_active = $workflow->is_active;

            if ($workflow->equipmentType) {
                $this->selectedAssetTypeName = $workflow->equipmentType->asset_code . ' - ' . $workflow->equipmentType->descripton;
            }
            if ($workflow->location) {
                $this->selectedLocationName = $workflow->location->name;
            }
            
            foreach ($workflow->steps as $step) {
                
                $roleId = null;
                $roleName = '';
                $assigneeName = '';
                
                if (in_array($step->assignee_type, [SpatieRole::class, AuthRole::class, 'App\\Models\\Role', 'App\\Role'], true)) {
                    $roleId = $step->assignee_id;
                    $roleModel = AuthRole::find($roleId);
                    $roleName = $roleModel ? $roleModel->name : '';
                    $assigneeName = 'All';
                } else if ($step->assignee_type === User::class || $step->assignee_type === 'App\User') {
                    $userModel = User::find($step->assignee_id);
                    $assigneeName = $userModel ? $userModel->name : '';
                    // Try to deduce their primary role for the UI column
                    if ($userModel && $userModel->roles->count() > 0) {
                        $roleId = $userModel->roles->first()->id;
                        $roleName = $userModel->roles->first()->name;
                    }
                }

                $this->steps[] = [
                    'id' => $step->id,
                    'step_name' => $step->step_name,
                    'assignee_type' => $step->assignee_type,
                    'assignee_id' => $step->assignee_id,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'assignee_name' => $assigneeName,
                    'is_required' => $step->is_required,
                    'showRoleDropdown' => false,
                    'showAssigneeDropdown' => false,
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
            'role_ids' => [],
            'role_names' => [],
            'assignee_ids' => [],
            'assignee_names' => [],
            'assignee_types' => [],
            'is_required' => true,
            'showRoleDropdown' => false,
            'showAssigneeDropdown' => false,
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

    public function selectAssetType($id, $name)
    {
        $this->equipment_type_id = $id;
        $this->selectedAssetTypeName = $name;
        $this->showAssetTypeDropdown = false;
        $this->assetTypeSearch = '';
    }

    public function clearAssetType()
    {
        $this->equipment_type_id = null;
        $this->selectedAssetTypeName = '';
    }

    public function selectLocation($id, $name)
    {
        $this->location_id = $id;
        $this->selectedLocationName = $name;
        $this->showLocationDropdown = false;
        $this->locationSearch = '';
    }

    public function clearLocation()
    {
        $this->location_id = null;
        $this->selectedLocationName = '';
    }

    public function toggleAssetTypeDropdown() { $this->showAssetTypeDropdown = !$this->showAssetTypeDropdown; }
    public function hideAssetTypeDropdown() { $this->showAssetTypeDropdown = false; }
    
    public function toggleLocationDropdown() { $this->showLocationDropdown = !$this->showLocationDropdown; }
    public function hideLocationDropdown() { $this->showLocationDropdown = false; }

    public function toggleStepRoleDropdown($index) {
        if(isset($this->steps[$index])) {
            $this->steps[$index]['showRoleDropdown'] = !$this->steps[$index]['showRoleDropdown'];
        }
    }
    public function hideStepRoleDropdown($index) {
        if(isset($this->steps[$index])) {
            $this->steps[$index]['showRoleDropdown'] = false;
        }
    }

    public function toggleStepAssigneeDropdown($index) {
        if(isset($this->steps[$index])) {
            $this->steps[$index]['showAssigneeDropdown'] = !$this->steps[$index]['showAssigneeDropdown'];
        }
    }
    public function hideStepAssigneeDropdown($index) {
        if(isset($this->steps[$index])) {
            $this->steps[$index]['showAssigneeDropdown'] = false;
        }
    }

    public function selectStepRole($index, $roleId, $roleName)
    {
        if (!isset($this->steps[$index]['role_ids'])) {
            $this->steps[$index]['role_ids'] = [];
            $this->steps[$index]['role_names'] = [];
        }
        
        // Prevent duplicate selections
        if (!in_array($roleId, $this->steps[$index]['role_ids'], true)) {
            $this->steps[$index]['role_ids'][] = $roleId;
            $this->steps[$index]['role_names'][] = $roleName;
        }
        
        // If this is the first role selection, add "All" as default assignee for this role
        if (count($this->steps[$index]['role_ids']) === 1) {
            $this->steps[$index]['assignee_ids'][] = $roleId;
            $this->steps[$index]['assignee_names'][] = 'All (for ' . $roleName . ')';
            $this->steps[$index]['assignee_types'][] = 'role';
        }
        
        $this->steps[$index]['showRoleDropdown'] = false;
    }

    public function removeStepRole($index, $roleId)
    {
        if (isset($this->steps[$index]['role_ids'])) {
            $key = array_search($roleId, $this->steps[$index]['role_ids'], true);
            if ($key !== false) {
                unset($this->steps[$index]['role_ids'][$key]);
                unset($this->steps[$index]['role_names'][$key]);
                
                // Re-index arrays
                $this->steps[$index]['role_ids'] = array_values($this->steps[$index]['role_ids']);
                $this->steps[$index]['role_names'] = array_values($this->steps[$index]['role_names']);
                
                // Also remove associated assignees for this role
                foreach ($this->steps[$index]['assignee_ids'] as $k => $aId) {
                    if ($aId === $roleId || (isset($this->steps[$index]['_assignee_role_map'][$k]) && $this->steps[$index]['_assignee_role_map'][$k] === $roleId)) {
                        unset($this->steps[$index]['assignee_ids'][$k]);
                        unset($this->steps[$index]['assignee_names'][$k]);
                        unset($this->steps[$index]['assignee_types'][$k]);
                    }
                }
                $this->steps[$index]['assignee_ids'] = array_values($this->steps[$index]['assignee_ids']);
                $this->steps[$index]['assignee_names'] = array_values($this->steps[$index]['assignee_names']);
                $this->steps[$index]['assignee_types'] = array_values($this->steps[$index]['assignee_types']);
            }
        }
    }

    public function selectStepAssignee($index, $userId, $userName, $roleId = null)
    {
        if (!isset($this->steps[$index]['assignee_ids'])) {
            $this->steps[$index]['assignee_ids'] = [];
            $this->steps[$index]['assignee_names'] = [];
            $this->steps[$index]['assignee_types'] = [];
        }
        
        // Prevent duplicate selections
        if (!in_array($userId, $this->steps[$index]['assignee_ids'], true)) {
            $this->steps[$index]['assignee_ids'][] = $userId;
            $this->steps[$index]['assignee_names'][] = $userName;
            $this->steps[$index]['assignee_types'][] = ($userId === 'all') ? 'role' : 'user';
        }
        
        $this->steps[$index]['showAssigneeDropdown'] = false;
    }

    public function removeStepAssignee($index, $assigneeId)
    {
        if (isset($this->steps[$index]['assignee_ids'])) {
            $key = array_search($assigneeId, $this->steps[$index]['assignee_ids'], true);
            if ($key !== false) {
                unset($this->steps[$index]['assignee_ids'][$key]);
                unset($this->steps[$index]['assignee_names'][$key]);
                unset($this->steps[$index]['assignee_types'][$key]);
                
                $this->steps[$index]['assignee_ids'] = array_values($this->steps[$index]['assignee_ids']);
                $this->steps[$index]['assignee_names'] = array_values($this->steps[$index]['assignee_names']);
                $this->steps[$index]['assignee_types'] = array_values($this->steps[$index]['assignee_types']);
            }
        }
    }

    public function save()
    {
        $this->validate();

        // Ensure at least one assignee per step
        foreach ($this->steps as $index => $stepData) {
            if (empty($stepData['assignee_ids'] ?? [])) {
                $this->addError("steps.{$index}.assignee_ids", 'Please select at least one assignee for step ' . ($index + 1) . '.');
                return;
            }
        }

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
                // Store multiple assignees as JSON
                $assignees = [];
                if (isset($stepData['assignee_ids']) && is_array($stepData['assignee_ids'])) {
                    foreach ($stepData['assignee_ids'] as $key => $assigneeId) {
                        $assigneeType = $stepData['assignee_types'][$key] ?? 'role';
                        if ($assigneeType === 'role') {
                            $assignees[] = [
                                'type' => AuthRole::class,
                                'id' => $assigneeId,
                                'name' => $stepData['assignee_names'][$key] ?? '',
                            ];
                        } else {
                            $assignees[] = [
                                'type' => User::class,
                                'id' => $assigneeId,
                                'name' => $stepData['assignee_names'][$key] ?? '',
                            ];
                        }
                    }
                }

                // Use first assignee as primary, store all as JSON in a comment or separate field
                $primaryAssignee = $assignees[0] ?? null;
                
                $stepAttributes = [
                    'workflow_id' => $workflow->id,
                    'step_order' => $index + 1,
                    'step_name' => $stepData['step_name'],
                    'assignee_type' => $primaryAssignee['type'] ?? AuthRole::class,
                    'assignee_id' => $primaryAssignee['id'] ?? '',
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
        $assetTypesQuery = AssetType::query();
        if ($this->assetTypeSearch) {
            $assetTypesQuery->where(function($q) {
                $q->where('asset_code', 'like', '%' . $this->assetTypeSearch . '%')
                  ->orWhere('descripton', 'like', '%' . $this->assetTypeSearch . '%');
            });
        }
        $filteredAssetTypes = $assetTypesQuery->take(20)->get();

        $locationsQuery = AssetLocation::query();
        if ($this->locationSearch) {
            $locationsQuery->where('name', 'like', '%' . $this->locationSearch . '%');
        }
        $filteredLocations = $locationsQuery->take(20)->get();

        return view('livewire.equipment.workflow.workflow-form', [
            'filteredAssetTypes' => $filteredAssetTypes,
            'filteredLocations' => $filteredLocations,
        ]);
    }
}
