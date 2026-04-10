<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditChecklist;
use App\Models\AuditModule\AuditChecklistItemResponse;
use App\Models\AuditModule\AuditActivityLog;
use Livewire\Component;

class AuditChecklistsManager extends Component
{
    public $auditId;
    public $checklistName = '';
    public $checklistDescription = '';
    public $checklistIsoStandard = '';
    public $auditTypeId = null;
    public $selectedItemId = null;
    public $showAddChecklistModal = false;
    public $showDeleteModal = false;
    public $checklistToDelete = null;
    
    // Item addition fields
    public $selectedChecklistIdForItem = null;
    public $showAddItemModal = false;
    public $item_number = '';
    public $iso_clause = '';
    public $requirement = '';
    public $guidance = '';
    public $evidence_required = '';
    public $is_mandatory = false;
    
    // Response fields
    public $compliance_status = '';
    public $audit_question = '';
    public $evidence_collected = '';
    public $observation = '';
    public $findings = '';
    public $auditor_notes = '';
    public $requires_follow_up = false;
    public $follow_up_notes = '';

    public function mount($auditId)
    {
        $this->auditId = $auditId;
        $audit = Audit::forCompany()->findOrFail($auditId);
        $this->auditTypeId = $audit->audit_type_id;
        
        // Set default compliance status from configuration
        $defaultStatus = \App\Models\AuditModule\ComplianceStatus::active()
            ->forCompany()
            ->where('code', 'pending')
            ->first();
        
        $this->compliance_status = $defaultStatus ? $defaultStatus->code : 'pending';
    }

    public function addChecklist()
    {
        $this->validate([
            'checklistName' => 'required|string|max:255',
            'checklistDescription' => 'nullable|string',
            'checklistIsoStandard' => 'nullable|string|max:255',
        ], [
            'checklistName.required' => 'Please enter a checklist name.',
            'checklistName.max' => 'Checklist name cannot exceed 255 characters.',
        ]);

        $audit = Audit::forCompany()->findOrFail($this->auditId);
        
        // Check if a checklist with the same name already exists for this audit
        $existingChecklist = AuditChecklist::forCompany()
            ->where('name', $this->checklistName)
            ->first();

        if ($existingChecklist) {
            // Check if it's already attached to this audit
            if ($audit->checklists()->where('audit_checklist_id', $existingChecklist->id)->exists()) {
                $this->dispatch('notify', ['type' => 'warning', 'message' => 'This checklist is already added to the audit.']);
                return;
            }
            $checklist = $existingChecklist;
        } else {
            // Create a new checklist
            $companyId = getUserCompany() ?? 0;
            
            // Generate a unique code
            $code = 'CHK-' . strtoupper(substr(md5($this->checklistName . $companyId . time()), 0, 8));
            
            // Ensure code is unique
            while (AuditChecklist::where('code', $code)->exists()) {
                $code = 'CHK-' . strtoupper(substr(md5($this->checklistName . $companyId . time() . rand()), 0, 8));
            }

            $checklist = AuditChecklist::create([
                'name' => $this->checklistName,
                'code' => $code,
                'description' => $this->checklistDescription,
                'iso_standard' => $this->checklistIsoStandard,
                'audit_type_id' => $this->auditTypeId,
                'is_active' => true,
                'created_by' => auth()->id(),
                'company_id' => $companyId,
            ]);
        }

        // Get the next order index
        $maxOrder = $audit->checklists()->max('order_index') ?? 0;
        
        // Attach checklist with order index
        $audit->checklists()->attach($checklist->id, [
            'order_index' => $maxOrder + 1,
        ]);

        AuditActivityLog::log($audit, 'Checklist Added', "Checklist '{$checklist->name}' added to audit");
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Checklist added successfully.']);
        
        // Reset form fields and close modal
        $this->resetChecklistForm();
        $this->closeAddChecklistModal();
    }

    public function openAddChecklistModal()
    {
        $this->showAddChecklistModal = true;
    }

    public function closeAddChecklistModal()
    {
        $this->showAddChecklistModal = false;
        $this->resetChecklistForm();
    }

    public function resetChecklistForm()
    {
        $this->checklistName = '';
        $this->checklistDescription = '';
        $this->checklistIsoStandard = '';
    }

    public function openAddItemModal($checklistId)
    {
        $this->selectedChecklistIdForItem = $checklistId;
        $this->showAddItemModal = true;
        $this->resetItemForm();
    }

    public function closeAddItemModal()
    {
        $this->showAddItemModal = false;
        $this->selectedChecklistIdForItem = null;
        $this->resetItemForm();
    }

    public function resetItemForm()
    {
        $this->item_number = '';
        $this->iso_clause = '';
        $this->requirement = '';
        $this->guidance = '';
        $this->evidence_required = '';
        $this->is_mandatory = false;
    }

    public function addChecklistItem()
    {
        $this->validate([
            'item_number' => 'required|string|max:255',
            'requirement' => 'required|string',
            'iso_clause' => 'nullable|string|max:255',
            'guidance' => 'nullable|string',
            'evidence_required' => 'nullable|string',
            'is_mandatory' => 'boolean',
        ], [
            'item_number.required' => 'Item number is required.',
            'requirement.required' => 'Requirement is required.',
        ]);

        $checklist = AuditChecklist::forCompany()->findOrFail($this->selectedChecklistIdForItem);
        
        // Get the next order index
        $maxOrder = $checklist->items()->max('order_index') ?? 0;

        \App\Models\AuditModule\AuditChecklistItem::create([
            'audit_checklist_id' => $checklist->id,
            'item_number' => $this->item_number,
            'iso_clause' => $this->iso_clause,
            'requirement' => $this->requirement,
            'guidance' => $this->guidance,
            'evidence_required' => $this->evidence_required,
            'order_index' => $maxOrder + 1,
            'is_mandatory' => $this->is_mandatory,
            'is_active' => true,
        ]);

        $audit = Audit::forCompany()->findOrFail($this->auditId);
        AuditActivityLog::log($audit, 'Checklist Item Added', "Item '{$this->item_number}' added to checklist '{$checklist->name}'");
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Checklist item added successfully.']);
        
        $this->closeAddItemModal();
    }

    public function confirmRemoveChecklist($checklistId)
    {
        $this->checklistToDelete = $checklistId;
        $this->showDeleteModal = true;
    }

    public function removeChecklist()
    {
        if (!$this->checklistToDelete) {
            return;
        }

        $audit = Audit::forCompany()->findOrFail($this->auditId);
        $checklist = AuditChecklist::forCompany()->findOrFail($this->checklistToDelete);

        $audit->checklists()->detach($this->checklistToDelete);

        AuditActivityLog::log($audit, 'Checklist Removed', "Checklist '{$checklist->name}' removed from audit");
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Checklist removed successfully.']);
        
        // Close modal and reset
        $this->showDeleteModal = false;
        $this->checklistToDelete = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->checklistToDelete = null;
    }

    public function openItemModal($itemId)
    {
        $this->selectedItemId = $itemId;
        $response = AuditChecklistItemResponse::forCompany()
            ->where('audit_id', $this->auditId)
            ->where('audit_checklist_item_id', $itemId)
            ->first();

        if ($response) {
            $this->compliance_status = $response->compliance_status;
            $this->audit_question = $response->audit_question ?? '';
            $this->evidence_collected = $response->evidence_collected ?? '';
            $this->observation = $response->observation ?? '';
            $this->findings = $response->findings ?? '';
            $this->auditor_notes = $response->auditor_notes ?? '';
            $this->requires_follow_up = $response->requires_follow_up ?? false;
            $this->follow_up_notes = $response->follow_up_notes ?? '';
        } else {
            $this->resetResponseFields();
        }
    }

    public function closeItemModal()
    {
        $this->selectedItemId = null;
        $this->resetResponseFields();
    }

    public function resetResponseFields()
    {
        // Get default status from configuration (first active status or 'pending' code)
        $defaultStatus = \App\Models\AuditModule\ComplianceStatus::active()
            ->forCompany()
            ->where('code', 'pending')
            ->first();
        
        $this->compliance_status = $defaultStatus ? $defaultStatus->code : 'pending';
        $this->audit_question = '';
        $this->evidence_collected = '';
        $this->observation = '';
        $this->findings = '';
        $this->auditor_notes = '';
        $this->requires_follow_up = false;
        $this->follow_up_notes = '';
    }

    public function saveItemResponse()
    {
        // Get valid compliance status codes from configuration
        $validStatuses = \App\Models\AuditModule\ComplianceStatus::active()
            ->forCompany()
            ->pluck('code')
            ->toArray();
        
        $this->validate([
            'compliance_status' => 'required|in:' . implode(',', $validStatuses),
            'audit_question' => 'nullable|string',
            'evidence_collected' => 'nullable|string',
            'observation' => 'nullable|string',
            'findings' => 'nullable|string',
            'auditor_notes' => 'nullable|string',
            'follow_up_notes' => 'nullable|string',
        ]);

        $audit = Audit::forCompany()->findOrFail($this->auditId);
        
        $response = AuditChecklistItemResponse::forCompany()
            ->where('audit_id', $this->auditId)
            ->where('audit_checklist_item_id', $this->selectedItemId)
            ->first();

        $data = [
            'audit_id' => $this->auditId,
            'audit_checklist_item_id' => $this->selectedItemId,
            'compliance_status' => $this->compliance_status,
            'audit_question' => $this->audit_question,
            'evidence_collected' => $this->evidence_collected,
            'observation' => $this->observation,
            'findings' => $this->findings,
            'auditor_notes' => $this->auditor_notes,
            'audited_by' => auth()->id(),
            'audited_at' => now(),
            'requires_follow_up' => $this->requires_follow_up,
            'follow_up_notes' => $this->follow_up_notes,
            'company_id' => getUserCompany() ?? 0,
        ];

        if ($response) {
            $response->update($data);
            $message = 'Checklist item response updated successfully.';
        } else {
            AuditChecklistItemResponse::create($data);
            $message = 'Checklist item response saved successfully.';
        }

        AuditActivityLog::log($audit, 'Checklist Item Response', 'Checklist item compliance recorded');
        
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
        $this->closeItemModal();
    }

    public function render()
    {
        $audit = Audit::forCompany()
            ->with(['checklists.items.responses' => function($query) {
                $query->where('audit_id', $this->auditId);
            }])
            ->findOrFail($this->auditId);

        // Get all checklist items from attached checklists with their responses
        $allItems = collect();
        foreach ($audit->checklists as $checklist) {
            foreach ($checklist->items->where('is_active', true) as $item) {
                $response = $item->responses->where('audit_id', $this->auditId)->first();
                $allItems->push([
                    'item' => $item,
                    'checklist' => $checklist,
                    'response' => $response,
                ]);
            }
        }

        $selectedItem = null;
        if ($this->selectedItemId) {
            $itemData = $allItems->firstWhere('item.id', $this->selectedItemId);
            $selectedItem = $itemData['item'] ?? null;
        }

        // Get compliance statuses from configuration
        $complianceStatuses = \App\Models\AuditModule\ComplianceStatus::active()
            ->forCompany()
            ->ordered()
            ->get();

        // Get checklist to delete name for modal
        $checklistToDeleteName = null;
        if ($this->checklistToDelete) {
            $checklist = AuditChecklist::forCompany()->find($this->checklistToDelete);
            $checklistToDeleteName = $checklist ? $checklist->name : null;
        }

        return view('livewire.audit-module.audit-checklists-manager', [
            'audit' => $audit,
            'allItems' => $allItems,
            'selectedItem' => $selectedItem,
            'complianceStatuses' => $complianceStatuses,
            'checklistToDeleteName' => $checklistToDeleteName,
        ]);
    }
}
