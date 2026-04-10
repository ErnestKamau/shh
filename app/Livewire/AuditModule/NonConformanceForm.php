<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\AuditActivityLog;
use Livewire\Component;

class NonConformanceForm extends Component
{
    public $ncId = null;
    public $isEdit = false;
    public $auditId = null;
    public $findingId = null;
    
    public $title = '';
    public $description = '';
    public $origin_id = '';
    public $origin_name = '';
    public $date_identified = '';
    public $iso_clause_violated = '';
    public $sop_reference = '';
    public $immediate_correction = '';
    public $responsible_person_id = '';
    public $responsible_person_name = '';
    public $target_closure_date = '';
    public $risk_level_id = '';
    public $risk_level_name = '';
    public $severity_score = '';
    public $severity_scale_id = '';
    public $likelihood_score = '';
    public $likelihood_scale_id = '';
    public $risk_assessment_notes = '';

    // Linking fields
    public $sample_id = '';
    public $sample_reference = '';
    public $equipment_id = '';
    public $equipment_reference = '';
    public $method_id = '';
    public $method_reference = '';
    public $personnel_id = '';
    public $personnel_reference = '';

    // Search fields for select2/searchable dropdowns
    public $sample_search = '';
    public $equipment_search = '';
    public $method_search = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'origin_id' => 'required|exists:nc_origins,id',
        'date_identified' => 'required|date',
        'iso_clause_violated' => 'nullable|string|max:255',
        'sop_reference' => 'nullable|string|max:255',
        'immediate_correction' => 'nullable|string',
        'responsible_person_id' => 'nullable|exists:users,id',
        'responsible_person_name' => 'nullable|string|max:255',
        'target_closure_date' => 'nullable|date|after_or_equal:date_identified',
        'risk_level_id' => 'nullable|exists:risk_levels,id',
        'severity_score' => 'nullable|integer|min:1|max:100',
        'severity_scale_id' => 'nullable|exists:severity_scales,id',
        'likelihood_score' => 'nullable|integer|min:1|max:100',
        'likelihood_scale_id' => 'nullable|exists:likelihood_scales,id',
        'risk_assessment_notes' => 'nullable|string',
        'sample_id' => 'nullable|exists:sample_headers,id',
        'sample_reference' => 'nullable|string|max:255',
        'equipment_id' => 'nullable|exists:equipment,id',
        'equipment_reference' => 'nullable|string|max:255',
        'method_id' => 'nullable|exists:analysis_methods,id',
        'method_reference' => 'nullable|string|max:255',
        'personnel_id' => 'nullable|exists:users,id',
        'personnel_reference' => 'nullable|string|max:255',
    ];

    public function mount($ncId = null, $auditId = null, $findingId = null)
    {
        $this->auditId = $auditId;
        $this->findingId = $findingId;
        
        if ($ncId) {
            $this->ncId = $ncId;
            $this->isEdit = true;
            $this->loadNC();
        } else {
            $this->date_identified = now()->format('Y-m-d');
            $this->target_closure_date = now()->addDays(30)->format('Y-m-d');
        }
    }

    public function loadNC()
    {
        $nc = NonConformance::forCompany()->findOrFail($this->ncId);
        
        $this->title = $nc->title;
        $this->description = $nc->description;
        $this->origin_id = $nc->origin_id;
        $this->origin_name = $nc->origin_name;
        $this->date_identified = $nc->date_identified?->format('Y-m-d');
        $this->iso_clause_violated = $nc->iso_clause_violated;
        $this->sop_reference = $nc->sop_reference;
        $this->immediate_correction = $nc->immediate_correction;
        $this->responsible_person_id = $nc->responsible_person_id;
        $this->responsible_person_name = $nc->responsible_person_name;
        $this->target_closure_date = $nc->target_closure_date?->format('Y-m-d');
        $this->risk_level_id = $nc->risk_level_id;
        $this->risk_level_name = $nc->risk_level_name;
        $this->severity_score = $nc->severity_score;
        $this->severity_scale_id = $nc->severity_scale_id;
        $this->likelihood_score = $nc->likelihood_score;
        $this->likelihood_scale_id = $nc->likelihood_scale_id;
        $this->risk_assessment_notes = $nc->risk_assessment_notes;
        $this->auditId = $nc->audit_id;
        $this->findingId = $nc->audit_finding_id;
        $this->sample_id = $nc->sample_id;
        $this->sample_reference = $nc->sample_reference;
        $this->equipment_id = $nc->equipment_id;
        $this->equipment_reference = $nc->equipment_reference;
        $this->method_id = $nc->method_id;
        $this->method_reference = $nc->method_reference;
        $this->personnel_id = $nc->personnel_id;
        $this->personnel_reference = $nc->personnel_reference;
    }

    public function updatedOriginId($value)
    {
        if ($value) {
            $origin = \App\Models\AuditModule\NcOrigin::find($value);
            $this->origin_name = $origin?->name ?? '';
        }
    }

    public function updatedRiskLevelId($value)
    {
        if ($value) {
            $level = \App\Models\AuditModule\RiskLevel::find($value);
            $this->risk_level_name = $level?->name ?? '';
        }
    }

    public function updatedSeverityScaleId($value)
    {
        if ($value) {
            $scale = \App\Models\AuditModule\SeverityScale::find($value);
            if ($scale) {
                $this->severity_score = $scale->score;
            }
        }
    }

    public function updatedLikelihoodScaleId($value)
    {
        if ($value) {
            $scale = \App\Models\AuditModule\LikelihoodScale::find($value);
            if ($scale) {
                $this->likelihood_score = $scale->score;
            }
        }
    }

    public function updatedSampleId($value)
    {
        if ($value) {
            $sample = \App\SampleHeader::find($value);
            if ($sample) {
                $this->sample_reference = $sample->batch_code ?? '';
            }
        } else {
            $this->sample_reference = '';
        }
    }

    public function updatedEquipmentId($value)
    {
        if ($value) {
            $equipment = \App\Models\Equipments\Equipment::find($value);
            if ($equipment) {
                $this->equipment_reference = $equipment->name ?? '';
            }
        } else {
            $this->equipment_reference = '';
        }
    }

    public function updatedMethodId($value)
    {
        if ($value) {
            $method = \App\AnalysisMethod::find($value);
            if ($method) {
                $this->method_reference = $method->name ?? ($method->code ?? '');
            }
        } else {
            $this->method_reference = '';
        }
    }

    public function updatedPersonnelId($value)
    {
        if ($value) {
            $user = \App\User::find($value);
            if ($user) {
                $this->personnel_reference = $user->name ?? '';
            }
        } else {
            $this->personnel_reference = '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'origin_id' => $this->origin_id ?: null,
            'origin_name' => $this->origin_name,
            'date_identified' => $this->date_identified,
            'iso_clause_violated' => $this->iso_clause_violated,
            'sop_reference' => $this->sop_reference,
            'immediate_correction' => $this->immediate_correction,
            'responsible_person_id' => $this->responsible_person_id ?: null,
            'responsible_person_name' => $this->responsible_person_name,
            'target_closure_date' => $this->target_closure_date ?: null,
            'risk_level_id' => $this->risk_level_id ?: null,
            'risk_level_name' => $this->risk_level_name,
            'severity_score' => $this->severity_score ?: null,
            'severity_scale_id' => $this->severity_scale_id ?: null,
            'likelihood_score' => $this->likelihood_score ?: null,
            'likelihood_scale_id' => $this->likelihood_scale_id ?: null,
            'risk_assessment_notes' => $this->risk_assessment_notes,
            'audit_id' => $this->auditId ?: null,
            'audit_finding_id' => $this->findingId ?: null,
            'sample_id' => $this->sample_id ?: null,
            'sample_reference' => $this->sample_reference,
            'equipment_id' => $this->equipment_id ?: null,
            'equipment_reference' => $this->equipment_reference,
            'method_id' => $this->method_id ?: null,
            'method_reference' => $this->method_reference,
            'personnel_id' => $this->personnel_id ?: null,
            'personnel_reference' => $this->personnel_reference,
        ];

        if ($this->isEdit) {
            $nc = NonConformance::forCompany()->findOrFail($this->ncId);
            $nc->update($data);
            AuditActivityLog::log($nc, 'Updated', 'Non-conformance details updated');
            
            return redirect()->route('audit.nc.show', $nc->id)
                ->with('success', 'Non-conformance updated successfully.');
        } else {
            $data['nc_number'] = NonConformance::generateNcNumber();
            $data['identified_by_user_id'] = auth()->id();
            $data['identified_by'] = auth()->user()->name ?? 'System';
            $data['status_name'] = 'Identified';
            $data['company_id'] = getUserCompany() ?? 0;
            
            $nc = NonConformance::create($data);
            AuditActivityLog::logCreation($nc, 'Non-conformance identified');
            
            // If created from an audit finding, update the finding status
            if ($nc->audit_finding_id) {
                $finding = \App\Models\AuditModule\AuditModuleFinding::find($nc->audit_finding_id);
                if ($finding) {
                    $finding->update(['status_name' => 'NC Raised']);
                }
            }
            
            return redirect()->route('audit.nc.show', $nc->id)
                ->with('success', 'Non-conformance created successfully.');
        }
    }

    public function render()
    {
        $riskLevels = getActiveRiskLevels();
        $origins = getActiveNcOrigins();
        $users = getAuditorUsers();

        // Load samples, equipment, and methods for dropdowns (like Personnel)
        $samples = \App\SampleHeader::query()
            ->where(function($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Cancelled');
            })
            ->where(function($q) {
                $q->whereNull('isactive')->orWhere('isactive', 1);
            })
            ->orderBy('batch_code', 'desc')
            ->get(['id', 'batch_code', 'reference_number']);

        // Load equipment (filtered to active only, like in search)
        $equipment = \App\Models\Equipments\Equipment::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $methods = getMethods(); // Helper function (already filters active and non-sampling methods)

        // Load severity and likelihood scales
        $severityScales = \App\Models\AuditModule\SeverityScale::forCompany()->active()->ordered()->get();
        $likelihoodScales = \App\Models\AuditModule\LikelihoodScale::forCompany()->active()->ordered()->get();

        // Get workflow step info if editing
        $currentStep = null;
        $workflowStepName = null;
        if ($this->isEdit && $this->ncId) {
            $nc = NonConformance::forCompany()->find($this->ncId);
            if ($nc) {
                $currentStep = $nc->getCurrentWorkflowStep();
                $workflowStepName = $nc->getWorkflowStepName();
            }
        }

        return view('livewire.audit-module.non-conformance-form', [
            'riskLevels' => $riskLevels,
            'origins' => $origins,
            'users' => $users,
            'samples' => $samples,
            'equipment' => $equipment,
            'methods' => $methods,
            'severityScales' => $severityScales,
            'likelihoodScales' => $likelihoodScales,
            'currentStep' => $currentStep,
            'workflowStepName' => $workflowStepName,
        ]);
    }
}
