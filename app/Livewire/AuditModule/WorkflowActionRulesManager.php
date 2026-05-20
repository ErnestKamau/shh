<?php

namespace App\Livewire\AuditModule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditModule\WorkflowAction;
use App\Models\AuditModule\WorkflowActionRule;
use App\Models\AuditModule\AuditStatus;
use App\Services\AuditModule\WorkflowValidationDiscoveryService;

class WorkflowActionRulesManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    
    public $showModal = false;
    public $isEdit = false;
    public $editId = null;
    
    // Form fields
    public $workflow_action_id = '';
    public $from_status_id = '';
    public $from_status_name = '';
    public $target_status_id = '';
    public $target_status_name = '';
    public $target_type = 'next'; // next, specific, current, previous
    public $validate_progression = true;
    public $validation_rules = '';
    public $conditions = '';
    public $success_message = '';
    public $error_message = '';
    public $is_active = true;
    public $order_index = 0;
    
    // Validation condition fields
    public $require_findings = false;
    public $min_findings_count = 0;
    public $require_nc_for_findings = false;
    public $require_rca_for_all_ncs = false;
    public $require_rca_approved = false;
    public $require_capa_for_all_ncs = false;
    public $min_capa_per_nc = 0;
    public $require_capa_implemented = false;
    public $require_capa_verified = false;
    public $require_capa_owners = false;
    public $require_capa_due_dates = false;
    
    // Dynamic validation conditions - will be populated from discovery service
    public $dynamicConditions = [];
    public $availableValidationOptions = [];

    public function mount()
    {
        $discoveryService = new WorkflowValidationDiscoveryService();
        $this->availableValidationOptions = $discoveryService->getValidationCategories();
    }

    public function openModal($ruleId = null)
    {
        if ($ruleId) {
            $this->isEdit = true;
            $this->editId = $ruleId;
            $this->loadItem($ruleId);
        } else {
            $this->isEdit = false;
            $this->editId = null;
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function loadItem($id)
    {
        $item = WorkflowActionRule::find($id);
        if ($item) {
            $this->workflow_action_id = $item->workflow_action_id;
            $this->from_status_id = $item->from_status_id;
            $this->from_status_name = $item->from_status_name;
            $this->target_status_id = $item->target_status_id;
            $this->target_status_name = $item->target_status_name;
            $this->target_type = $item->target_type;
            $this->validate_progression = $item->validate_progression;
            $this->validation_rules = is_array($item->validation_rules) ? json_encode($item->validation_rules, JSON_PRETTY_PRINT) : ($item->validation_rules ?? '');
            
            // Load conditions
            $conditions = is_array($item->conditions) ? $item->conditions : (json_decode($item->conditions ?? '{}', true) ?? []);
            $this->require_findings = $conditions['require_findings'] ?? false;
            $this->min_findings_count = $conditions['min_findings_count'] ?? 0;
            $this->require_nc_for_findings = $conditions['require_nc_for_findings'] ?? false;
            $this->require_rca_for_all_ncs = $conditions['require_rca_for_all_ncs'] ?? false;
            $this->require_rca_approved = $conditions['require_rca_approved'] ?? false;
            $this->require_capa_for_all_ncs = $conditions['require_capa_for_all_ncs'] ?? false;
            $this->min_capa_per_nc = $conditions['min_capa_per_nc'] ?? 0;
            $this->require_capa_implemented = $conditions['require_capa_implemented'] ?? false;
            $this->require_capa_verified = $conditions['require_capa_verified'] ?? false;
            $this->require_capa_owners = $conditions['require_capa_owners'] ?? false;
            $this->require_capa_due_dates = $conditions['require_capa_due_dates'] ?? false;
            
            // Load dynamic conditions (exclude standard ones)
            $standardKeys = [
                'require_findings', 'min_findings_count', 'require_nc_for_findings',
                'require_rca_for_all_ncs', 'require_rca_approved',
                'require_capa_for_all_ncs', 'min_capa_per_nc',
                'require_capa_implemented', 'require_capa_verified',
                'require_capa_owners', 'require_capa_due_dates'
            ];
            
            $this->dynamicConditions = [];
            foreach ($conditions as $key => $value) {
                if (!in_array($key, $standardKeys)) {
                    $this->dynamicConditions[$key] = $value;
                }
            }
            
            $this->success_message = $item->success_message;
            $this->error_message = $item->error_message;
            $this->is_active = $item->is_active;
            $this->order_index = $item->order_index ?? 0;
        }
    }

    public function resetForm()
    {
        $this->workflow_action_id = '';
        $this->from_status_id = '';
        $this->from_status_name = '';
        $this->target_status_id = '';
        $this->target_status_name = '';
        $this->target_type = 'next';
        $this->validate_progression = true;
        $this->validation_rules = '';
        $this->conditions = '';
        $this->success_message = '';
        $this->error_message = '';
        $this->is_active = true;
        $this->order_index = 0;
        
        // Reset validation conditions
        $this->require_findings = false;
        $this->min_findings_count = 0;
        $this->require_nc_for_findings = false;
        $this->require_rca_for_all_ncs = false;
        $this->require_rca_approved = false;
        $this->require_capa_for_all_ncs = false;
        $this->min_capa_per_nc = 0;
        $this->require_capa_implemented = false;
        $this->require_capa_verified = false;
        $this->require_capa_owners = false;
        $this->require_capa_due_dates = false;
        $this->dynamicConditions = [];
    }

    public function save()
    {
        $rules = [
            'workflow_action_id' => 'required|exists:workflow_actions,id',
            'target_type' => 'required|in:next,specific,current,previous',
            'validate_progression' => 'boolean',
            'is_active' => 'boolean',
            'order_index' => 'nullable|integer|min:0',
        ];

        // If target_type is 'specific', require target_status_id
        if ($this->target_type === 'specific') {
            $rules['target_status_id'] = 'required|exists:audit_statuses,id';
        }

        // Require either from_status_id or from_status_name
        if (empty($this->from_status_id) && empty($this->from_status_name)) {
            $this->addError('from_status_id', 'Please select a status or enter a status name.');
            return;
        }

        $this->validate($rules);

        $data = [
            'workflow_action_id' => $this->workflow_action_id,
            'from_status_id' => $this->from_status_id ?: null,
            'from_status_name' => $this->from_status_name ?: null,
            'target_status_id' => $this->target_type === 'specific' ? $this->target_status_id : null,
            'target_status_name' => $this->target_type === 'specific' && $this->target_status_id ? AuditStatus::find($this->target_status_id)->name : null,
            'target_type' => $this->target_type,
            'validate_progression' => $this->validate_progression,
            'validation_rules' => !empty($this->validation_rules) ? json_decode($this->validation_rules, true) : null,
            'conditions' => $this->buildConditionsArray(),
            'success_message' => $this->success_message,
            'error_message' => $this->error_message,
            'is_active' => $this->is_active,
            'order_index' => $this->order_index ?? 0,
            'company_id' => getUserCompany(),
        ];

        if ($this->isEdit) {
            WorkflowActionRule::where('id', $this->editId)->update($data);
            $message = 'Workflow action rule updated successfully.';
        } else {
            WorkflowActionRule::create($data);
            $message = 'Workflow action rule created successfully.';
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
        $this->closeModal();
    }

    public function delete($id)
    {
        $rule = WorkflowActionRule::find($id);
        if ($rule) {
            $rule->delete();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Workflow action rule deleted successfully.']);
        }
    }

    protected function buildConditionsArray(): array
    {
        $conditions = [];
        
        // Standard validations
        if ($this->require_findings) {
            $conditions['require_findings'] = true;
        }
        if ($this->min_findings_count > 0) {
            $conditions['min_findings_count'] = $this->min_findings_count;
        }
        if ($this->require_nc_for_findings) {
            $conditions['require_nc_for_findings'] = true;
        }
        if ($this->require_rca_for_all_ncs) {
            $conditions['require_rca_for_all_ncs'] = true;
        }
        if ($this->require_rca_approved) {
            $conditions['require_rca_approved'] = true;
        }
        if ($this->require_capa_for_all_ncs) {
            $conditions['require_capa_for_all_ncs'] = true;
        }
        if ($this->min_capa_per_nc > 0) {
            $conditions['min_capa_per_nc'] = $this->min_capa_per_nc;
        }
        if ($this->require_capa_implemented) {
            $conditions['require_capa_implemented'] = true;
        }
        if ($this->require_capa_verified) {
            $conditions['require_capa_verified'] = true;
        }
        if ($this->require_capa_owners) {
            $conditions['require_capa_owners'] = true;
        }
        if ($this->require_capa_due_dates) {
            $conditions['require_capa_due_dates'] = true;
        }
        
        // Merge dynamic conditions
        if (is_array($this->dynamicConditions)) {
            $conditions = array_merge($conditions, $this->dynamicConditions);
        }
        
        return $conditions;
    }
    
    public function updatedDynamicConditions($value, $key)
    {
        // Handle dynamic condition updates from wire:model
        if (!is_array($this->dynamicConditions)) {
            $this->dynamicConditions = [];
        }
        
        // Parse the key (e.g., "dynamicConditions.require_team_members")
        $parts = explode('.', $key);
        if (count($parts) >= 2 && $parts[0] === 'dynamicConditions') {
            $conditionKey = $parts[1];
            if ($value === '' || $value === null || $value === false || $value === 0) {
                unset($this->dynamicConditions[$conditionKey]);
            } else {
                $this->dynamicConditions[$conditionKey] = $value;
            }
        } elseif (count($parts) === 1) {
            if ($value === '' || $value === null || $value === false || $value === 0) {
                unset($this->dynamicConditions[$key]);
            } else {
                $this->dynamicConditions[$key] = $value;
            }
        }
    }

    public function render()
    {
        $query = WorkflowActionRule::forCompany()
            ->with(['workflowAction', 'fromStatus', 'targetStatus'])
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc');

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('workflowAction', function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                })
                ->orWhereHas('fromStatus', function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                })
                ->orWhere('from_status_name', 'like', $searchTerm)
                ->orWhere('target_type', 'like', $searchTerm);
            });
        }

        $rules = $query->paginate($this->perPage);
        $workflowActions = WorkflowAction::forCompany()->active()->ordered()->get();
        $auditStatuses = AuditStatus::forCompany()->active()->ordered()->get();

        return view('livewire.audit-module.workflow-action-rules-manager', [
            'rules' => $rules,
            'workflowActions' => $workflowActions,
            'auditStatuses' => $auditStatuses,
        ]);
    }
}
