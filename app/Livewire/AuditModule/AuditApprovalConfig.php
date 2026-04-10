<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\AuditWorkflowApprover;
use Livewire\Component;
use Livewire\WithPagination;

class AuditApprovalConfig extends Component
{
    use WithPagination;

    public $module = 'audit';
    public $allowModuleSwitching = true;
    public $search = '';
    public $selectedStep = null;
    public $showModal = false;

    // Form properties
    public $workflowStep;
    public $roleType = 'approver';
    public $userId;
    public $isoRole;
    public $isRequired = true;
    public $approvalType = 'single';
    public $isEdit = false;
    public $editId; 

    protected function rules()
    {
        $isoRoles = $this->module === 'risk' 
            ? 'Risk Manager,Risk Owner,Top Management' 
            : 'Lead Auditor,Quality Manager,Top Management,Auditee';
            
        return [
            'module' => 'required|in:audit,risk',
            'workflowStep' => 'required|integer|min:1|max:8',
            'roleType' => 'required|in:approver,verifier',
            'userId' => 'required|exists:users,id',
            'isoRole' => 'nullable|in:' . $isoRoles,
            'isRequired' => 'boolean',
            'approvalType' => 'required|in:single,multiple',
        ];
    }

    public function updatedModule()
    {
        $this->resetPage();
        $this->resetForm();
    }

    public function mount()
    {
        // Initialize component
    }
 
    public function openModal($module = null, $id = null)
    {
        $this->resetForm();
        
        if ($module) {
            $this->module = $module;
        }

        if ($id) {
            $this->isEdit = true;
            $this->editId = $id;
            $this->loadApprover($id);
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    public function delete($id)
    {
        $approver = AuditWorkflowApprover::forCompany()->findOrFail($id);
        $approver->delete();
        session()->flash('success', 'Approver removed successfully.');
    }

    public function resetForm()
    {
        // Keep selected module
        $this->workflowStep = null;
        $this->roleType = 'approver';
        $this->userId = null;
        $this->isoRole = null;
        $this->isRequired = true;
        $this->approvalType = 'single';
        $this->isEdit = false;
        $this->editId = null;
        $this->resetValidation();
    }

    public function loadApprover($id)
    {
        $approver = AuditWorkflowApprover::forCompany()->findOrFail($id);
        $this->module = $approver->module ?? 'audit';
        $this->workflowStep = $approver->workflow_step;
        $this->roleType = $approver->role_type;
        $this->userId = $approver->user_id;
        $this->isoRole = $approver->iso_role;
        $this->isRequired = $approver->is_required;
        $this->approvalType = $approver->approval_type;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'module' => $this->module,
            'workflow_step' => (int) $this->workflowStep,
            'role_type' => $this->roleType,
            'user_id' => (int) $this->userId,
            'iso_role' => $this->isoRole,
            'is_required' => $this->isRequired,
            'approval_type' => $this->approvalType,
            'company_id' => getUserCompany() ?? 0,
        ];

        if ($this->isEdit && $this->editId) {
            $approver = AuditWorkflowApprover::forCompany()->findOrFail($this->editId);
            $approver->update($data);
            session()->flash('success', 'Approver configuration updated successfully.');
        } else {
            AuditWorkflowApprover::create($data);
            session()->flash('success', 'Approver configuration created successfully.');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function render()
    {
        if ($this->module === 'risk') {
            $workflowSteps = [
                2 => 'Identified',
                3 => 'Under Assessment',
                4 => 'Under Evaluation',
                5 => 'Treatment Planning',
                6 => 'Treatment Implementation',
                7 => 'Risk Monitoring',
                8 => 'Closed',
            ];
            $isoRoles = [
                'Risk Manager',
                'Risk Owner',
                'Top Management',
            ];
        } else {
            $workflowSteps = function_exists('getAuditWorkflowSteps') ? getAuditWorkflowSteps() : [];
            $isoRoles = [
                'Lead Auditor',
                'Quality Manager',
                'Top Management',
                'Auditee',
            ];
        }
        
        $query = AuditWorkflowApprover::forCompany()
            ->with('user')
            ->orderBy('workflow_step', 'asc')
            ->orderBy('role_type', 'asc');

        if ($this->module) {
             $query->forModule($this->module);
        }

        if ($this->selectedStep) {
            $query->where('workflow_step', $this->selectedStep);
        }

        if ($this->search) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $approvers = $query->paginate(20);

        $users = \App\User::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('livewire.audit-module.audit-approval-config', [
            'approvers' => $approvers,
            'workflowSteps' => $workflowSteps,
            'users' => $users,
            'isoRoles' => $isoRoles,
        ]);
    }
}
