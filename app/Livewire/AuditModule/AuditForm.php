<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditActivityLog;
use App\Models\AuditModule\AuditType;
use App\Models\AuditModule\AuditChecklist;
use App\InventoryDepartment;
use App\User;
use Livewire\Component;

class AuditForm extends Component
{
    public $auditId = null;
    public $isEdit = false;
    
    public $title = '';
    public $audit_type_id = '';
    public $audit_checklist_id = '';
    public $scheduled_date = '';
    public $start_date = '';
    public $end_date = '';
    public $scope = '';
    public $criteria = '';
    public $objectives = '';
    public $department = '';
    public $lead_auditor_id = '';
    public $lead_auditor_name = '';
    public $auditee_name = '';
    public $auditee_id = '';
    public $auditee_department_id = '';
    public $summary = '';
    public $conclusion = '';
    public $recommendations = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'audit_type_id' => 'required|uuid|exists:audit_types,id',
        'scheduled_date' => 'required|date',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'scope' => 'nullable|string',
        'criteria' => 'nullable|string',
        'objectives' => 'nullable|string',
        'department' => 'nullable|string|max:255',
        'lead_auditor_id' => 'nullable|uuid|exists:users,id',
        'lead_auditor_name' => 'nullable|string|max:255',
        'auditee_name' => 'nullable|string|max:255',
        'auditee_department_id' => 'nullable|uuid|exists:inventory_departments,id',
        'audit_checklist_id' => 'nullable|uuid|exists:audit_checklists,id',
    ];

    public function mount($auditId = null)
    {
        if ($auditId) {
            $this->auditId = $auditId;
            $this->isEdit = true;
            $this->loadAudit();
        } else {
            $this->scheduled_date = now()->addDays(7)->format('Y-m-d');
        }
    }

    public function loadAudit()
    {
        $audit = Audit::forCompany()->findOrFail($this->auditId);
        
        $this->title = $audit->title;
        $this->audit_type_id = $audit->audit_type_id;
        $this->audit_checklist_id = $audit->checklist_id ?? '';
        $this->scheduled_date = $audit->scheduled_date?->format('Y-m-d');
        $this->start_date = $audit->start_date?->format('Y-m-d');
        $this->end_date = $audit->end_date?->format('Y-m-d');
        $this->scope = $audit->scope;
        $this->criteria = $audit->criteria;
        $this->objectives = $audit->objective;
        $this->department = $audit->department;
        $this->lead_auditor_id = $audit->lead_auditor_id;
        $this->lead_auditor_name = $audit->lead_auditor_name;
        $this->auditee_name = $audit->auditee_name;
        $this->auditee_id = $audit->auditee_id;
        $this->auditee_department_id = $audit->auditee_department_id;
        $this->summary = $audit->executive_summary;
        $this->conclusion = $audit->conclusions;
        $this->recommendations = $audit->recommendations;
    }

    public function updatedAuditTypeId($value): void
    {
        $this->audit_checklist_id = '';
    }

    public function save()
    {
        $this->validate();

        // Check status for edit mode to determine if summary fields can be saved
        $canSaveSummaryFields = false;
        if ($this->isEdit) {
            $audit = Audit::forCompany()->find($this->auditId);
            $statusName = $audit ? $audit->status_name : null;
            // Allow saving summary fields when status is "Verify" or later statuses
            $allowedStatuses = ['Verify', 'Pending Closure', 'Closed'];
            $canSaveSummaryFields = $statusName && in_array($statusName, $allowedStatuses);
        }

        $data = [
            'title' => $this->title,
            'audit_type_id' => $this->audit_type_id,
            'checklist_id' => !empty($this->audit_checklist_id) ? $this->audit_checklist_id : null,
            'scheduled_date' => $this->scheduled_date,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'scope' => $this->scope,
            'criteria' => $this->criteria,
            'objective' => $this->objectives,
            'department' => $this->department,
            'lead_auditor_id' => !empty($this->lead_auditor_id) ? $this->lead_auditor_id : null,
            'lead_auditor_name' => $this->lead_auditor_name,
            'auditee_name' => $this->auditee_name,
            'auditee_department_id' => !empty($this->auditee_department_id) ? $this->auditee_department_id : null,
        ];

        // Only allow Summary, Conclusion, and Recommendations to be saved when status is "Verify" or later
        if ($canSaveSummaryFields) {
            $data['executive_summary'] = $this->summary;
            $data['conclusions'] = $this->conclusion;
            $data['recommendations'] = $this->recommendations;
        }
        // For new audits or early statuses, these fields are not included in the update

        if ($this->isEdit) {
            $audit = Audit::forCompany()->findOrFail($this->auditId);
            $data['updated_by'] = auth()->id();
            $audit->update($data);
            AuditActivityLog::log($audit, 'Updated', 'Audit details updated');
            
            return redirect()->route('audit.audits.show', $audit->id)
                ->with('success', 'Audit updated successfully.');
        } else {
            $data['audit_number'] = Audit::generateAuditNumber();
            $data['status_name'] = 'Scheduled';
            $data['created_by'] = auth()->id();
            $data['company_id'] = getUserCompany();
            
            $audit = Audit::create($data);
            AuditActivityLog::logCreation($audit, 'Audit scheduled');
            
            // Log initial workflow transition (Step 1: Scheduled)
            $initialStep = $audit->getCurrentWorkflowStep() ?? 1;
            $workflowStepName = $audit->getWorkflowStepName() ?? 'Scheduled';
            AuditActivityLog::logWorkflowTransition(
                $audit,
                $initialStep,
                $workflowStepName,
                'New',
                'Scheduled',
                'Audit created and scheduled'
            );
            
            return redirect()->route('audit.audits.show', $audit->id)
                ->with('success', 'Audit created successfully.');
        }
    }

    public function render()
    {
        $auditTypes = AuditType::forCompany()
            ->active()
            ->orderBy('name', 'asc')
            ->get();

        $checklistsQuery = AuditChecklist::forCompany()
            ->active()
            ->orderBy('name', 'asc');
        if (!empty($this->audit_type_id)) {
            $checklistsQuery->where('audit_type_id', $this->audit_type_id);
        }
        $checklists = $checklistsQuery->get();

        $auditors = User::where('company_id', getUserCompany())
            ->where('active', 1)
            ->where('is_client', 0)
            ->where('is_support_staff', 0)
            ->whereNull('supplier_id')
            ->orderBy('name', 'asc')
            ->get();

        $departments = InventoryDepartment::where('company_id', getUserCompany())
            ->where('location_id', getCurrentUserLocation()->id)
            ->orderBy('name', 'asc')
            ->get();

        // Get status info if editing
        $statusName = null;
        $canEditSummaryFields = false;
        if ($this->isEdit && $this->auditId) {
            $audit = Audit::forCompany()->find($this->auditId);
            if ($audit) {
                $statusName = $audit->status_name;
                // Allow editing summary fields when status is "Verify" or later
                $allowedStatuses = ['Verify', 'Pending Closure', 'Closed'];
                $canEditSummaryFields = $statusName && in_array($statusName, $allowedStatuses);
            }
        }

        return view('livewire.audit-module.audit-form', [
            'auditTypes' => $auditTypes,
            'checklists' => $checklists,
            'auditors' => $auditors,
            'departments' => $departments,
            'statusName' => $statusName,
            'canEditSummaryFields' => $canEditSummaryFields,
        ]);
    }
}

