<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\AuditActivityLog;
use Livewire\Component;

class CorrectiveActionForm extends Component
{
    public $capaId = null;
    public $isEdit = false;
    public $ncId = null;
    
    public $non_conformance_id = '';
    public $title = '';
    public $description = '';
    public $action_type_id = '';
    public $action_type_name = 'Corrective';
    public $action_owner_id = '';
    public $action_owner_name = '';
    public $due_date = '';
    public $priority_id = '';
    public $priority_name = 'Medium';
    public $category_id = '';
    public $category_name = '';
    public $expected_outcome = '';
    public $implementation_notes = '';
    public $preventive_measure = '';

    protected $rules = [
        'non_conformance_id' => 'required|exists:non_conformances,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'action_type_id' => 'nullable|exists:capa_action_types,id',
        'action_owner_id' => 'required|exists:users,id',
        'due_date' => 'required|date',
        'priority_id' => 'nullable|exists:capa_priorities,id',
        'category_id' => 'nullable|exists:capa_categories,id',
        'expected_outcome' => 'nullable|string',
        'preventive_measure' => 'nullable|string',
    ];

    public function mount($capaId = null, $ncId = null)
    {
        $this->ncId = $ncId;
        
        if ($capaId) {
            $this->capaId = $capaId;
            $this->isEdit = true;
            $this->loadCAPA();
        } else {
            $this->non_conformance_id = $ncId ?? '';
            $this->due_date = now()->addDays(14)->format('Y-m-d');
        }
    }

    public function loadCAPA()
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($this->capaId);
        
        $this->non_conformance_id = $capa->non_conformance_id;
        $this->title = $capa->title;
        $this->description = $capa->description;
        $this->action_type_id = $capa->action_type_id;
        $this->action_type_name = $capa->action_type_name;
        $this->action_owner_id = $capa->action_owner_id;
        $this->action_owner_name = $capa->action_owner_name;
        $this->due_date = $capa->due_date?->format('Y-m-d');
        $this->priority_id = $capa->priority_id;
        $this->priority_name = $capa->priority_name;
        $this->category_id = $capa->category_id;
        $this->category_name = $capa->category_name;
        $this->expected_outcome = $capa->expected_outcome;
        $this->implementation_notes = $capa->implementation_notes;
        $this->preventive_measure = $capa->preventive_measure;
    }

    public function updatedActionTypeId($value)
    {
        if ($value) {
            $type = \App\Models\AuditModule\CapaActionType::find($value);
            $this->action_type_name = $type?->name ?? '';
        }
    }

    public function updatedPriorityId($value)
    {
        if ($value) {
            $priority = \App\Models\AuditModule\CapaPriority::find($value);
            $this->priority_name = $priority?->name ?? '';
        }
    }

    public function updatedCategoryId($value)
    {
        if ($value) {
            $category = \App\Models\AuditModule\CapaCategory::find($value);
            $this->category_name = $category?->name ?? '';
        }
    }

    public function updatedActionOwnerId($value)
    {
        if ($value) {
            $user = \App\User::find($value);
            $this->action_owner_name = $user?->name ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'non_conformance_id' => $this->non_conformance_id,
            'title' => $this->title,
            'description' => $this->description,
            'action_type_id' => $this->action_type_id ?: null,
            'action_type_name' => $this->action_type_name,
            'action_owner_id' => $this->action_owner_id,
            'action_owner_name' => $this->action_owner_name,
            'due_date' => $this->due_date,
            'priority_id' => $this->priority_id ?: null,
            'priority_name' => $this->priority_name,
            'category_id' => $this->category_id ?: null,
            'category_name' => $this->category_name,
            'expected_outcome' => $this->expected_outcome,
            'preventive_measure' => $this->preventive_measure,
        ];

        if ($this->isEdit) {
            $capa = CorrectiveAction::forCompany()->findOrFail($this->capaId);
            $data['implementation_notes'] = $this->implementation_notes;
            $data['updated_by'] = auth()->id();
            $capa->update($data);
            AuditActivityLog::log($capa, 'Updated', 'Corrective action details updated');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Corrective action updated successfully.']);
        } else {
            $data['capa_number'] = CorrectiveAction::generateCapaNumber();
            $data['status_name'] = 'Open';
            $data['created_by'] = auth()->id();
            $data['assigned_date'] = now();
            $data['company_id'] = getUserCompany() ?? 0;
            
            $capa = CorrectiveAction::create($data);
            AuditActivityLog::logCreation($capa, 'Corrective action assigned');
            
            // Update NC status if this is the first CAPA
            $nc = $capa->nonConformance;
            if (in_array($nc->status_name, ['Identified', 'RCA In Progress'])) {
                $nc->update(['status_name' => 'CAPA Assigned']);
            }
            
            return redirect()->route('audit.capa.show', $capa->id)
                ->with('success', 'Corrective action created successfully.');
        }
    }

    public function render()
    {
        $categories = getActiveCapaCategories();
        $users = getAuditorUsers();
        $ncs = NonConformance::forCompany()
            ->whereNotIn('status_name', ['Closed', 'Cancelled'])
            ->orderBy('nc_number', 'desc')
            ->get(['id', 'nc_number', 'title', 'description']);
        $actionTypes = getActiveCapaActionTypes();
        $priorities = getActiveCapaPriorities();

        // Get workflow step info if editing
        $currentStep = null;
        $workflowStepName = null;
        if ($this->isEdit && $this->capaId) {
            $capa = CorrectiveAction::forCompany()->find($this->capaId);
            if ($capa) {
                $currentStep = $capa->getCurrentWorkflowStep();
                $workflowStepName = $capa->getWorkflowStepName();
            }
        }

        return view('livewire.audit-module.corrective-action-form', [
            'categories' => $categories,
            'users' => $users,
            'ncs' => $ncs,
            'actionTypes' => $actionTypes,
            'priorities' => $priorities,
            'currentStep' => $currentStep,
            'workflowStepName' => $workflowStepName,
        ]);
    }
}
