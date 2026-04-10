<?php

namespace App\Http\Controllers\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\RiskManagement\Risk;
use App\Models\RiskManagement\RiskStatus;
use App\Models\RiskManagement\RiskCategory;
use App\Models\RiskManagement\RiskSource;
use App\Models\RiskManagement\TreatmentType;
use App\Models\RiskManagement\RiskTreatmentPlan;
use App\Models\RiskManagement\RiskReview;
use App\Models\RiskManagement\RiskAttachment;
use App\Models\RiskManagement\RiskAssessment;
use App\Models\RiskManagement\RiskEvaluation;
use App\Models\RiskManagement\RiskConfigurationOption;
use App\Models\AuditModule\LikelihoodScale;
use App\Models\AuditModule\SeverityScale;
use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditModuleFinding;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use App\Services\AuditModule\AuditChainOfCustodyService;
use App\User;
use App\InventoryDepartment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class RiskManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of risks
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'All Risks');
        
        return view('layouts.risk.risks.index', compact('status'));
    }

    /**
     * Show the form for creating a new risk
     */
    public function create(Request $request)
    {
        // Check if risk is being created from another entity
        $sourceType = $request->get('source_type'); // audit, nc, complaint, etc.
        $sourceId = $request->get('source_id');
        
        $prefillData = [];
        
        if ($sourceType === 'audit_finding' && $sourceId) {
            $finding = AuditModuleFinding::find($sourceId);
            if ($finding) {
                $prefillData = [
                    'title' => 'Risk from Audit Finding: ' . $finding->requirement,
                    'description' => $finding->observation,
                    'audit_finding_id' => $finding->id,
                    'audit_id' => $finding->audit_id,
                    'source_name' => 'Audit Finding',
                    'category_name' => $finding->findingCategory->name ?? 'Quality',
                ];
            }
        } elseif ($sourceType === 'nc' && $sourceId) {
            $nc = NonConformance::find($sourceId);
            if ($nc) {
                $prefillData = [
                    'title' => 'Risk from NC: ' . $nc->title,
                    'description' => $nc->description,
                    'non_conformance_id' => $nc->id,
                    'audit_id' => $nc->audit_id,
                    'source_name' => 'Non-Conformance',
                    'category_name' => 'Quality',
                ];
            }
        }
        
        // Load all data needed for the form
        $categories = RiskCategory::forCompany()->active()->get();
        $sources = RiskSource::forCompany()->active()->get();
        $users = User::where('active', 1)->get();
        $departments = InventoryDepartment::where('company_id', getUserCompany())
            ->where('location_id', getCurrentUserLocation()->id)
            ->get();
        
        // Load samples for dropdown
        $samples = \App\SampleHeader::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'Cancelled');
        })->where(function($q) {
            $q->whereNull('isactive')->orWhere('isactive', 1);
        })->orderBy('batch_code', 'desc')->get(['id', 'batch_code', 'reference_number']);
        
        // Load equipment for dropdown
        $equipment = \App\Models\Equipments\Equipment::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Load methods for dropdown
        $methods = getMethods();
        
        return view('layouts.risk.risks.create', compact(
            'prefillData',
            'categories',
            'sources',
            'users',
            'departments',
            'samples',
            'equipment',
            'methods'
        ));
    }

    /**
     * Store a newly created risk
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:risk_categories,id',
            'other_source_id' => 'nullable|exists:risk_sources,id',
            'risk_owner_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:inventory_departments,id',
            'date_identified' => 'required|date',
            'audit_id' => 'nullable|exists:iso_audits,id',
            'audit_finding_id' => 'nullable|exists:audit_module_findings,id',
            'non_conformance_id' => 'nullable|exists:non_conformances,id',
            'complaint_id' => 'nullable|integer',
            'equipment_id' => 'nullable|exists:equipment,id',
            'personnel_id' => 'nullable|exists:users,id',
            'sample_id' => 'nullable|exists:sample_headers,id',
            'sample_reference' => 'nullable|string|max:255',
            'method_id' => 'nullable|exists:analysis_methods,id',
            'method_reference' => 'nullable|string|max:255',
        ]);

        $validated['risk_number'] = Risk::generateRiskNumber();
        $validated['status_name'] = 'Identified';
        $validated['workflow_step'] = 2; // Step 2 is Identified (Step 1 is "All Risks" filter only)
        $validated['created_by'] = Auth::id();
        $validated['identified_by_user_id'] = Auth::id();
        $validated['identified_by'] = Auth::user()->name;
        $validated['company_id'] = getUserCompany() ?? 0;

        // Set category and other source names if IDs provided
        if (isset($validated['category_id'])) {
            $category = RiskCategory::find($validated['category_id']);
            $validated['category_name'] = $category->name ?? null;
        }

        if (isset($validated['other_source_id'])) {
            $source = RiskSource::find($validated['other_source_id']);
            $validated['other_source_name'] = $source->name ?? null;
        }

        // Set sample reference if sample_id provided
        if (isset($validated['sample_id'])) {
            $sample = \App\SampleHeader::find($validated['sample_id']);
            if ($sample) {
                $validated['sample_reference'] = $sample->batch_code ?? '';
            }
        }

        // Set method reference if method_id provided
        if (isset($validated['method_id'])) {
            $method = \App\AnalysisMethod::find($validated['method_id']);
            if ($method) {
                $validated['method_reference'] = $method->name ?? ($method->code ?? '');
            }
        }

        // Set risk owner name
        if (isset($validated['risk_owner_id'])) {
            $owner = User::find($validated['risk_owner_id']);
            $validated['risk_owner_name'] = $owner->name ?? null;
        }

        // Set department name
        if (isset($validated['department_id'])) {
            $dept = InventoryDepartment::find($validated['department_id']);
            $validated['department'] = $dept->name ?? null;
        }

        // Get initial status (Step 2 is Identified)
        $status = RiskStatus::forCompany()
            ->where('workflow_step', 2)
            ->ordered()
            ->first();
        
        if ($status) {
            $validated['status_id'] = $status->id;
            $validated['status_name'] = $status->name;
        }

        $risk = Risk::create($validated);

        // Log creation activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                \App\Models\AuditModule\AuditActivityLog::logCreation(
                    $risk,
                    "Risk '{$risk->title}' ({$risk->risk_number}) was created and identified."
                );
                
                // Log initial workflow transition (Step 1: Risk Identification)
                \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                    $risk,
                    1, // Step 1 for chain of custody
                    'Risk Identification',
                    'New',
                    $risk->status_name ?? 'Identified',
                    'Risk created and identified'
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log risk creation: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk created successfully.');
    }

    /**
     * Display the specified risk
     */
    public function show($id)
    {
        $risk = Risk::forCompany()
            ->with([
                'category',
                'otherSource',
                'status',
                'riskOwner',
                'identifiedByUser',
                'likelihoodScale',
                'severityScale',
                'audit',
                'auditFinding',
                'nonConformance',
                'sample',
                'method',
                'equipment',
                'personnel',
                'assessments.likelihoodScale',
                'assessments.severityScale',
                'assessments.assessedByUser',
                'currentAssessment.likelihoodScale',
                'currentAssessment.severityScale',
                'currentAssessment.assessedByUser',
                'evaluations.assessment',
                'evaluations.evaluatedByUser',
                'evaluations.escalatedToUser',
                'currentEvaluation.assessment',
                'currentEvaluation.evaluatedByUser',
                'treatmentPlans.treatmentType',
                'treatmentPlans.responsibleUser',
                'treatmentPlans.capa',
                'reviews.reviewedByUser',
                'attachments.uploadedBy',
            ])
            ->findOrFail($id);

        // Get available statuses for workflow
        $availableStatuses = RiskStatus::forCompany()
            ->active()
            ->ordered()
            ->get()
            ->groupBy('workflow_step');

        // Get next status
        $nextWorkflowStatus = $risk->getNextWorkflowStatus();
        
        // If no next status but we're at step 2 (Identified), try to get step 3 status directly
        if (!$nextWorkflowStatus && ($risk->workflow_step == 2 || $risk->workflow_step == 0 || $risk->workflow_step == 1)) {
            $nextWorkflowStatus = RiskStatus::forCompany()
                ->active()
                ->where('workflow_step', 3)
                ->ordered()
                ->first();
        }

        // Get related data for forms
        $categories = RiskCategory::forCompany()->active()->get();
        $sources = RiskSource::forCompany()->active()->get();
        $treatmentTypes = TreatmentType::forCompany()->active()->get();
        $likelihoodScales = LikelihoodScale::forCompany()->active()->ordered()->get();
        $severityScales = SeverityScale::forCompany()->active()->ordered()->get();
        $evaluationResults = getEvaluationResults();
        $users = User::where('active', 1)->get();
        $departments = InventoryDepartment::where('company_id', getUserCompany())
            ->where('location_id', getCurrentUserLocation()->id)
            ->get();

        // Calculate chain of custody for activity log
        $chainOfCustodyService = new AuditChainOfCustodyService();
        $chainOfCustody = $chainOfCustodyService->getChainOfCustody($risk);

        // Get approvals history
        $approvals = $risk->workflowApprovals()
            ->with('approver')
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('layouts.risk.risks.show', compact(
            'risk',
            'approvals',
            'availableStatuses',
            'nextWorkflowStatus',
            'categories',
            'sources',
            'treatmentTypes',
            'likelihoodScales',
            'severityScales',
            'evaluationResults',
            'users',
            'departments',
            'chainOfCustody'
        ));
    }

    /**
     * Show the form for editing the specified risk
     */
    public function edit($id)
    {
        $risk = Risk::forCompany()->findOrFail($id);
        
        $categories = RiskCategory::forCompany()->active()->get();
        $sources = RiskSource::forCompany()->active()->get();
        $users = User::where('active', 1)->get();
        $departments = InventoryDepartment::where('company_id', getUserCompany())
            ->where('location_id', getCurrentUserLocation()->id)
            ->get();
        
        // Load samples for dropdown
        $samples = \App\SampleHeader::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'Cancelled');
        })->where(function($q) {
            $q->whereNull('isactive')->orWhere('isactive', 1);
        })->orderBy('batch_code', 'desc')->get(['id', 'batch_code', 'reference_number']);
        
        // Load equipment for dropdown
        $equipment = \App\Models\Equipments\Equipment::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Load methods for dropdown
        $methods = getMethods();

        return view('layouts.risk.risks.edit', compact(
            'risk',
            'categories',
            'sources',
            'users',
            'departments',
            'samples',
            'equipment',
            'methods'
        ));
    }

    /**
     * Update the specified risk
     */
    public function update(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:risk_categories,id',
            'other_source_id' => 'nullable|exists:risk_sources,id',
            'risk_owner_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:inventory_departments,id',
            'date_identified' => 'required|date',
            'closure_justification' => 'nullable|string',
            'sample_id' => 'nullable|exists:sample_headers,id',
            'sample_reference' => 'nullable|string|max:255',
            'method_id' => 'nullable|exists:analysis_methods,id',
            'method_reference' => 'nullable|string|max:255',
            'equipment_id' => 'nullable|exists:equipment,id',
            'personnel_id' => 'nullable|exists:users,id',
        ]);

        $validated['updated_by'] = Auth::id();

        // Update names
        if (isset($validated['category_id'])) {
            $category = RiskCategory::find($validated['category_id']);
            $validated['category_name'] = $category->name ?? null;
        }

        if (isset($validated['other_source_id'])) {
            $source = RiskSource::find($validated['other_source_id']);
            $validated['other_source_name'] = $source->name ?? null;
        }

        // Set sample reference if sample_id provided
        if (isset($validated['sample_id'])) {
            $sample = \App\SampleHeader::find($validated['sample_id']);
            if ($sample) {
                $validated['sample_reference'] = $sample->batch_code ?? '';
            }
        }

        // Set method reference if method_id provided
        if (isset($validated['method_id'])) {
            $method = \App\AnalysisMethod::find($validated['method_id']);
            if ($method) {
                $validated['method_reference'] = $method->name ?? ($method->code ?? '');
            }
        }

        if (isset($validated['risk_owner_id'])) {
            $owner = User::find($validated['risk_owner_id']);
            $validated['risk_owner_name'] = $owner->name ?? null;
        }

        if (isset($validated['department_id'])) {
            $dept = InventoryDepartment::find($validated['department_id']);
            $validated['department'] = $dept->name ?? null;
        }

        $risk->update($validated);

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk updated successfully.');
    }

    /**
     * Update closure justification only
     */
    public function updateClosureJustification(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'closure_justification' => 'required|string',
        ]);

        $risk->update([
            'closure_justification' => $validated['closure_justification'],
            'updated_by' => Auth::id(),
        ]);

        // Log activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                \App\Models\AuditModule\AuditActivityLog::log(
                    $risk,
                    'Closure Justification Updated',
                    'Closure justification has been added/updated.'
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log closure justification update activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Closure justification saved successfully.');
    }

    /**
     * Remove the specified risk
     */
    public function destroy($id)
    {
        $risk = Risk::forCompany()->findOrFail($id);
        $risk->delete();

        return redirect()->route('risk.risks.index')
            ->with('success', 'Risk deleted successfully.');
    }

    /**
     * Store risk assessment (Step 2)
     */
    public function storeAssessment(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'likelihood_scale_id' => 'required|exists:likelihood_scales,id',
            'likelihood_score' => 'required|integer|min:1|max:10',
            'severity_scale_id' => 'required|exists:severity_scales,id',
            'severity_score' => 'required|integer|min:1|max:10',
            'assessment_notes' => 'nullable|string',
            'assessed_by_user_id' => 'nullable|array',
            'assessed_by_user_id.*' => 'exists:users,id',
            'reassessment_reason' => 'nullable|string',
        ]);

        // Always get scores from the selected scales (source of truth)
        $likelihoodScale = LikelihoodScale::find($validated['likelihood_scale_id']);
        $severityScale = SeverityScale::find($validated['severity_scale_id']);
        
        // Use scale score if provided, otherwise use submitted score (for backward compatibility)
        $validated['likelihood_score'] = $likelihoodScale->score ?? $validated['likelihood_score'] ?? 1;
        $validated['severity_score'] = $severityScale->score ?? $validated['severity_score'] ?? 1;

        $rpn = $validated['likelihood_score'] * $validated['severity_score'];
        $riskLevel = $risk->determineRiskLevel($rpn);
        
        // Get version number (increment if reassessing)
        $maxVersion = RiskAssessment::where('risk_id', $risk->id)->max('version') ?? 0;
        $version = $maxVersion + 1;

        // Handle multiple assessors
        $assessedById = null;
        $assessedByName = null;
        $additionalAssessorsNote = '';

        if (!empty($validated['assessed_by_user_id'])) {
            $assessorIds = $validated['assessed_by_user_id'];
            // Use the first selected user as the primary assessor for the FK relationship
            $assessedById = $assessorIds[0];
            $assessedByName = \App\User::find($assessedById)->name ?? null;

            // If multiple assessors, add them to notes
            if (count($assessorIds) > 1) {
                $allAssessors = \App\User::whereIn('id', $assessorIds)->pluck('name')->toArray();
                $additionalAssessorsNote = "\n\n**Assessment Team:** " . implode(', ', $allAssessors);
            } elseif (count($assessorIds) === 1) {
                 // Even if single, ensure name is populated
                 $assessedByName = \App\User::find($assessorIds[0])->name ?? null;
            }
        } else {
            // Fallback to current user
            $assessedById = Auth::id();
            $assessedByName = Auth::user()->name;
        }

        // Create new assessment record
        $assessment = RiskAssessment::create([
            'risk_id' => $risk->id,
            'assessment_number' => RiskAssessment::generateAssessmentNumber(),
            'likelihood_scale_id' => $validated['likelihood_scale_id'],
            'likelihood_score' => $validated['likelihood_score'],
            'severity_scale_id' => $validated['severity_scale_id'],
            'severity_score' => $validated['severity_score'],
            'rpn' => $rpn,
            'risk_level' => $riskLevel,
            'assessment_notes' => ($validated['assessment_notes'] ?? '') . $additionalAssessorsNote,
            'assessed_by_user_id' => $assessedById,
            'assessed_by' => $assessedByName,
            'assessment_date' => now(),
            'is_current' => true,
            'version' => $version,
            'reassessment_reason' => $validated['reassessment_reason'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'company_id' => getUserCompany() ?? 0,
        ]);

        // Mark previous assessments as not current
        RiskAssessment::where('risk_id', $risk->id)
            ->where('id', '!=', $assessment->id)
            ->update(['is_current' => false]);

        // Check current workflow step BEFORE updating
        $currentWorkflowStep = (int) $risk->workflow_step;
        
        // Prepare update data - include workflow step update if needed
        $updateData = [
            'likelihood_scale_id' => $validated['likelihood_scale_id'],
            'likelihood_score' => $validated['likelihood_score'],
            'severity_scale_id' => $validated['severity_scale_id'],
            'severity_score' => $validated['severity_score'],
            'rpn' => $rpn,
            'risk_level' => $riskLevel,
            'assessment_notes' => $validated['assessment_notes'] ?? null,
            'assessment_date' => now(),
            'current_assessment_id' => $assessment->id,
            'updated_by' => Auth::id(),
        ];
        
        // Move to Step 3 (Under Assessment) if currently at Step 2 (Identified) or 0/null/1
        if ($currentWorkflowStep == 2 || $currentWorkflowStep == 1 || $currentWorkflowStep == 0) {
            $status = RiskStatus::forCompany()
                ->where('workflow_step', 3)
                ->where('is_active', true)
                ->ordered()
                ->first();
            
            $updateData['workflow_step'] = 3;
            
            if ($status) {
                $updateData['status_id'] = $status->id;
                $updateData['status_name'] = $status->name;
            } else {
                if (!isset($updateData['status_name'])) {
                    $updateData['status_name'] = 'Under Assessment';
                }
            }
        }
        
        // Store old status for logging
        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        // Update risk (backward compatibility)
        $risk->update($updateData);
        
        // Refresh to get updated values
        $risk->refresh();

        // Log assessment activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                $statusChanged = ($oldStatus !== $risk->status_name);
                
                if ($statusChanged || $oldWorkflowStep != $risk->workflow_step) {
                    // Log as step 2 for chain of custody (comes after step 1: Risk Identification)
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        2, // Step 2: Risk Assessment (for chain of custody timeline)
                        'Risk Assessment',
                        $oldStatus,
                        $risk->status_name,
                        "Risk assessment completed. RPN: {$rpn}, Risk Level: {$riskLevel}"
                    );
                }
                
                // Also log to assessment model
                \App\Models\AuditModule\AuditActivityLog::log(
                    $assessment,
                    $version > 1 ? 'Reassessment Completed' : 'Assessment Completed',
                    "Risk assessment completed. RPN: {$rpn}, Risk Level: {$riskLevel}" . ($version > 1 ? " (Version {$version})" : '')
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log assessment activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk assessment completed successfully.');
    }

    /**
     * Store risk evaluation (Step 3)
     */
    public function storeEvaluation(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        // Get valid evaluation results from configuration (for optional manual override)
        $validEvaluationResults = getEvaluationResults()->pluck('code')->toArray();
        $validEvaluationResultNames = getEvaluationResults()->pluck('name')->toArray();
        
        $validated = $request->validate([
            'evaluation_result' => 'nullable|in:' . implode(',', array_merge($validEvaluationResults, $validEvaluationResultNames, [''])),
            'rpn' => 'required|integer|min:1',
            'evaluation_notes' => 'nullable|string',
            'evaluated_by_user_id' => 'nullable|exists:users,id',
            'escalated_to_user_id' => 'nullable|exists:users,id',
            'escalation_reason' => 'nullable|string',
        ]);

        // Get current assessment
        $assessment = $risk->currentAssessment;
        if (!$assessment) {
            return redirect()->route('risk.risks.show', $risk->id)
                ->with('error', 'Please complete risk assessment before evaluation.');
        }

        // Get entered RPN from request
        $enteredRpn = (int)$validated['rpn'];

        // Auto-determine evaluation result based on RPN using helper function
        $evaluationResultOption = getEvaluationResultByRPN($enteredRpn);
        
        if (!$evaluationResultOption) {
            return redirect()->back()
                ->withErrors(['rpn' => "No evaluation result found for RPN value ({$enteredRpn}). Please check the RPN range configuration in settings."])
                ->withInput();
        }
        
        $evaluationResultCode = $evaluationResultOption->code;
        
        // If evaluation_result was provided, verify it matches the RPN-derived result
        if (!empty($validated['evaluation_result'])) {
            $providedResult = strtolower($validated['evaluation_result']);
            if ($providedResult !== $evaluationResultCode) {
                // Override with RPN-derived result (RPN is source of truth)
                \Log::info("Evaluation result mismatch - provided: {$providedResult}, RPN-derived: {$evaluationResultCode}. Using RPN-derived result.");
            }
        }
        
        // Get workflow step from evaluation result metadata
        $metadata = is_array($evaluationResultOption->metadata) ? $evaluationResultOption->metadata : (is_string($evaluationResultOption->metadata) ? json_decode($evaluationResultOption->metadata, true) : []);
        $nextWorkflowStep = $metadata['workflow_step'] ?? null;

        // Create new evaluation record
        $evaluation = RiskEvaluation::create([
            'risk_id' => $risk->id,
            'assessment_id' => $assessment->id,
            'evaluation_number' => RiskEvaluation::generateEvaluationNumber(),
            'risk_score' => $enteredRpn, // Use entered RPN value
            'acceptance_threshold_rpn' => null, // No longer used
            'evaluation_result' => $evaluationResultCode,
            'evaluation_notes' => $validated['evaluation_notes'] ?? null,
            'evaluated_by_user_id' => $validated['evaluated_by_user_id'] ?? Auth::id(),
            'evaluated_by' => Auth::user()->name ?? null,
            'evaluation_date' => now(),
            'is_current' => true,
            'escalated_to_user_id' => $validated['escalated_to_user_id'] ?? null,
            'escalation_reason' => $validated['escalation_reason'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'company_id' => getUserCompany() ?? 0,
        ]);

        // Mark previous evaluations as not current
        RiskEvaluation::where('risk_id', $risk->id)
            ->where('id', '!=', $evaluation->id)
            ->update(['is_current' => false]);

        // Determine if treatment is required
        $requiresTreatment = in_array($evaluationResultCode, ['unacceptable', 'escalate']);

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        // Update risk (backward compatibility)
        $risk->update([
            'evaluation_result' => $evaluationResultCode,
            'evaluation_notes' => $validated['evaluation_notes'] ?? null,
            'evaluation_date' => now(),
            'acceptance_threshold_rpn' => null, // No longer used
            'requires_treatment' => $requiresTreatment,
            'current_evaluation_id' => $evaluation->id,
            'updated_by' => Auth::id(),
        ]);

        // Transition to configured workflow step from evaluation result
        if ($nextWorkflowStep && ($risk->workflow_step === 2 || $risk->workflow_step === 3)) {
            $status = RiskStatus::forCompany()
                ->where('workflow_step', $nextWorkflowStep)
                ->ordered()
                ->first();
            
            if ($status) {
                $risk->update([
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'workflow_step' => $nextWorkflowStep,
                ]);
            }
        } elseif (!$nextWorkflowStep && $risk->workflow_step === 2) {
            // Fallback: Move to Step 3 if no workflow step configured
            $status = RiskStatus::forCompany()
                ->where('workflow_step', 3)
                ->ordered()
                ->first();
            
            if ($status) {
                $risk->update([
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'workflow_step' => 3,
                ]);
            }
        }
        
        // Refresh to get updated values
        $risk->refresh();

        // Log evaluation activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                if ($oldWorkflowStep != $risk->workflow_step || $oldStatus !== $risk->status_name) {
                    // Log as step 3 for chain of custody (comes after step 2: Risk Assessment)
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        3, // Step 3: Risk Evaluation (for chain of custody timeline)
                        'Risk Evaluation',
                        $oldStatus,
                        $risk->status_name,
                        "Risk evaluation completed. Result: {$risk->evaluation_result}" . ($risk->requires_treatment ? ' - Treatment required' : '')
                    );
                }
                
                // Also log to evaluation model
                \App\Models\AuditModule\AuditActivityLog::log(
                    $evaluation,
                    'Evaluation Completed',
                    "Risk evaluation completed. Result: {$evaluationResultCode}" . ($requiresTreatment ? ' - Treatment required' : '')
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log evaluation activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk evaluation completed successfully.');
    }

    /**
     * Store treatment plan (Step 4)
     */
    public function storeTreatmentPlan(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        // Get valid options from configuration
        $validImplementationStatuses = getImplementationStatuses()->pluck('code')->merge(getImplementationStatuses()->pluck('name'))->toArray();
        $validPriorities = getTreatmentPriorities()->pluck('code')->merge(getTreatmentPriorities()->pluck('name'))->toArray();
        
        // Calculate current RPN for validation
        $likelihoodScore = $risk->likelihoodScale->score ?? $risk->likelihood_score ?? null;
        $severityScore = $risk->severityScale->score ?? $risk->severity_score ?? null;
        $currentRpn = null;
        if ($likelihoodScore && $severityScore) {
            $currentRpn = $likelihoodScore * $severityScore;
        } elseif ($risk->rpn) {
            $currentRpn = $risk->rpn;
        }
        
        // Set max value for residual risk expected
        $maxResidualRpn = $currentRpn ? $currentRpn : 25;
        
        $validated = $request->validate([
            'treatment_type_id' => 'required|exists:treatment_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'control_measures' => 'nullable|string',
            'expected_outcome' => 'nullable|string',
            'priority' => 'nullable|in:' . implode(',', $validPriorities),
            'resources_required' => 'nullable|string',
            'residual_risk_expected' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . $maxResidualRpn,
                function ($attribute, $value, $fail) use ($currentRpn, $maxResidualRpn) {
                    if ($value !== null && $value !== '') {
                        // Check if value is less than 1
                        if ($value < 1) {
                            $fail('The residual risk expected RPN must be at least 1. You entered ' . $value . '.');
                        }
                        // Check if value exceeds current RPN
                        if ($currentRpn && $value > $currentRpn) {
                            $fail('The residual risk expected RPN (' . $value . ') cannot exceed the current RPN (' . $currentRpn . ').');
                        }
                        // Check if value exceeds max
                        if ($value > $maxResidualRpn) {
                            $fail('The residual risk expected RPN cannot exceed ' . $maxResidualRpn . '.');
                        }
                    }
                },
            ],
            'responsible_user_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string',
            'target_completion_date' => 'nullable|date',
            'implementation_status' => 'required|in:' . implode(',', $validImplementationStatuses),
            'implementation_notes' => 'nullable|string',
            'implementation_start_date' => 'nullable|date',
            'implementation_end_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'capa_id' => 'nullable|exists:corrective_actions,id',
        ]);

        $treatmentType = TreatmentType::find($validated['treatment_type_id']);
        $validated['treatment_type_name'] = $treatmentType->name ?? null;
        $validated['risk_id'] = $risk->id;
        $validated['created_by'] = Auth::id();

        if (isset($validated['responsible_user_id'])) {
            $user = User::find($validated['responsible_user_id']);
            $validated['responsible_person'] = $user->name ?? null;
        }

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        $treatmentPlan = RiskTreatmentPlan::create($validated);

        // Move to Step 5 (Treatment Planning) if currently at Step 4 (Under Evaluation)
        if ($risk->workflow_step === 4) {
            $status = RiskStatus::forCompany()
                ->where('workflow_step', 5)
                ->ordered()
                ->first();
            
            if ($status) {
                $risk->update([
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'workflow_step' => 5,
                ]);
            }
        }
        
        // Refresh to get updated values
        $risk->refresh();

        // Log treatment plan creation activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                if ($oldWorkflowStep != $risk->workflow_step || $oldStatus !== $risk->status_name) {
                    // Log as step 4 for chain of custody (comes after step 3: Risk Evaluation)
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        4, // Step 4: Treatment Planning (for chain of custody timeline)
                        'Treatment Planning',
                        $oldStatus,
                        $risk->status_name,
                        "Treatment plan '{$treatmentPlan->title}' created"
                    );
                } else {
                    \App\Models\AuditModule\AuditActivityLog::log(
                        $risk,
                        'Treatment Plan Created',
                        "Treatment plan '{$treatmentPlan->title}' created"
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to log treatment plan activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Treatment plan created successfully.');
    }

    /**
     * Update treatment plan implementation status (Step 5)
     */
    public function showTreatmentPlan($riskId, $treatmentPlanId)
    {
        $risk = Risk::forCompany()->findOrFail($riskId);
        $treatmentPlan = RiskTreatmentPlan::where('risk_id', $riskId)->findOrFail($treatmentPlanId);
        
        // Load relationships
        $treatmentPlan->load([
            'risk', 
            'treatmentType', 
            'responsibleUser', 
            'createdBy', 
            'updatedBy', 
            'attachments', 
            'activityLogs.performedBy'
        ]);
        
        // Also load risk activity logs for reference
        $risk->load('activityLogs.performedBy');
        
        // Format file sizes for attachments
        foreach ($treatmentPlan->attachments as $attachment) {
            $attachment->file_size_formatted = $this->formatFileSize($attachment->file_size ?? 0);
        }
        
        // Get next workflow status - always try to get it for workflow actions
        $nextWorkflowStatus = $risk->getNextWorkflowStatus();
        
        // If no next status but we're at step 2 (Identified), try to get step 3 status directly
        if (!$nextWorkflowStatus && ($risk->workflow_step == 2 || $risk->workflow_step == 0 || $risk->workflow_step == 1)) {
            $nextWorkflowStatus = RiskStatus::forCompany()
                ->active()
                ->where('workflow_step', 3)
                ->ordered()
                ->first();
        }
        
        // Get approvals history
        $approvals = $risk->workflowApprovals()
            ->with('approver')
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('layouts.risk.treatment-plans.show', compact('risk', 'treatmentPlan', 'nextWorkflowStatus', 'approvals'));
    }

    public function updateTreatmentPlan(Request $request, $riskId, $treatmentPlanId)
    {
        $risk = Risk::forCompany()->findOrFail($riskId);
        $treatmentPlan = RiskTreatmentPlan::where('risk_id', $riskId)->findOrFail($treatmentPlanId);

        // Get valid options from configuration
        $validImplementationStatuses = getImplementationStatuses()->pluck('code')->merge(getImplementationStatuses()->pluck('name'))->toArray();
        $validPriorities = getTreatmentPriorities()->pluck('code')->merge(getTreatmentPriorities()->pluck('name'))->toArray();
        
        // Calculate current RPN for validation
        $likelihoodScore = $risk->likelihoodScale->score ?? $risk->likelihood_score ?? null;
        $severityScore = $risk->severityScale->score ?? $risk->severity_score ?? null;
        $currentRpn = null;
        if ($likelihoodScore && $severityScore) {
            $currentRpn = $likelihoodScore * $severityScore;
        } elseif ($risk->rpn) {
            $currentRpn = $risk->rpn;
        }
        
        // Set max value for residual risk expected
        $maxResidualRpn = $currentRpn ? $currentRpn : 25;
        
        $validated = $request->validate([
            'treatment_type_id' => 'required|exists:treatment_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'control_measures' => 'nullable|string',
            'expected_outcome' => 'nullable|string',
            'priority' => 'nullable|in:' . implode(',', $validPriorities),
            'resources_required' => 'nullable|string',
            'residual_risk_expected' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . $maxResidualRpn,
                function ($attribute, $value, $fail) use ($currentRpn, $maxResidualRpn) {
                    if ($value !== null && $value !== '') {
                        // Check if value is less than 1
                        if ($value < 1) {
                            $fail('The residual risk expected RPN must be at least 1. You entered ' . $value . '.');
                        }
                        // Check if value exceeds current RPN
                        if ($currentRpn && $value > $currentRpn) {
                            $fail('The residual risk expected RPN (' . $value . ') cannot exceed the current RPN (' . $currentRpn . ').');
                        }
                        // Check if value exceeds max
                        if ($value > $maxResidualRpn) {
                            $fail('The residual risk expected RPN cannot exceed ' . $maxResidualRpn . '.');
                        }
                    }
                },
            ],
            'responsible_user_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string',
            'target_completion_date' => 'nullable|date',
            'implementation_status' => 'required|in:' . implode(',', $validImplementationStatuses),
            'implementation_notes' => 'nullable|string',
            'implementation_start_date' => 'nullable|date',
            'implementation_end_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'implementation_attachments' => 'nullable|array',
            'implementation_attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $validated['updated_by'] = Auth::id();

        // Update treatment type name if treatment type changed
        if (isset($validated['treatment_type_id'])) {
            $treatmentType = TreatmentType::find($validated['treatment_type_id']);
            $validated['treatment_type_name'] = $treatmentType->name ?? null;
        }

        // Update responsible person name if responsible user changed
        if (isset($validated['responsible_user_id']) && $validated['responsible_user_id']) {
            $user = User::find($validated['responsible_user_id']);
            $validated['responsible_person'] = $user->name ?? null;
        } elseif (isset($validated['responsible_user_id']) && !$validated['responsible_user_id']) {
            // Clear responsible person if user is removed
            $validated['responsible_person'] = null;
        }

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        $oldImplementationStatus = $treatmentPlan->implementation_status;

        $treatmentPlan->update($validated);

        // Check if all treatment plans are completed, move to Step 6 (Treatment Implementation)
        $allCompleted = $risk->treatmentPlans()
            ->where('implementation_status', 'Completed')
            ->count() === $risk->treatmentPlans()->count();

        if ($allCompleted && $risk->workflow_step === 5) {
            $status = RiskStatus::forCompany()
                ->where('workflow_step', 6)
                ->ordered()
                ->first();
            
            if ($status) {
                $risk->update([
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'workflow_step' => 6,
                ]);
            }
        }
        
        // Refresh to get updated values
        $risk->refresh();
        $treatmentPlan->refresh();
        
        // Handle implementation evidence attachments
        $uploadedFiles = [];
        if ($request->hasFile('implementation_attachments')) {
            foreach ($request->file('implementation_attachments') as $file) {
                if ($file->isValid()) {
                    // Use the same strategy as SampleWorkFlowController - no disk specified (uses default 'local')
                    $path = $file->path();
                    $storedPath = Storage::putFile('treatment_plan_attachments', new File($path));
                    
                    RiskAttachment::create([
                        'attachable_type' => RiskTreatmentPlan::class,
                        'attachable_id' => $treatmentPlan->id,
                        'file_name' => $file->hashName(),
                        'file_path' => $storedPath,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'description' => 'Implementation evidence',
                        'uploaded_by' => Auth::id(),
                        'company_id' => getUserCompany() ?? 0,
                    ]);
                    
                    $uploadedFiles[] = $file->getClientOriginalName();
                }
            }
        }

        // Log treatment plan update activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                // Log for treatment plan
                $implementationDetails = [];
                if (isset($validated['implementation_status'])) {
                    $implementationDetails[] = "Status: {$validated['implementation_status']}";
                }
                if (isset($validated['implementation_start_date']) && $validated['implementation_start_date']) {
                    $implementationDetails[] = "Start Date: " . date('M d, Y', strtotime($validated['implementation_start_date']));
                }
                if (isset($validated['implementation_end_date']) && $validated['implementation_end_date']) {
                    $implementationDetails[] = "End Date: " . date('M d, Y', strtotime($validated['implementation_end_date']));
                }
                if (isset($validated['actual_completion_date']) && $validated['actual_completion_date']) {
                    $implementationDetails[] = "Completion Date: " . date('M d, Y', strtotime($validated['actual_completion_date']));
                }
                
                $description = "Treatment plan '{$treatmentPlan->title}' implementation recorded";
                if (!empty($implementationDetails)) {
                    $description .= ". " . implode(', ', $implementationDetails);
                }
                if (!empty($validated['implementation_notes'])) {
                    $notesText = strip_tags($validated['implementation_notes']);
                    $description .= ". Notes: " . substr($notesText, 0, 100) . (strlen($notesText) > 100 ? '...' : '');
                }
                
                \App\Models\AuditModule\AuditActivityLog::log(
                    $treatmentPlan,
                    'Implementation Recorded',
                    $description
                );
                
                // Also log for risk if workflow step changed
                if ($oldWorkflowStep != $risk->workflow_step || $oldStatus !== $risk->status_name) {
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        $risk->workflow_step ?? 5,
                        'Treatment Implementation',
                        $oldStatus,
                        $risk->status_name,
                        "Treatment plan '{$treatmentPlan->title}' status changed to: {$validated['implementation_status']}"
                    );
                } else {
                    \App\Models\AuditModule\AuditActivityLog::log(
                        $risk,
                        'Treatment Plan Updated',
                        "Treatment plan '{$treatmentPlan->title}' status changed from '{$oldImplementationStatus}' to '{$validated['implementation_status']}'"
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to log treatment plan update activity: ' . $e->getMessage());
            }
        }

        // Check if request came from treatment plan show page
        $fromTreatmentPlanPage = $request->has('from_treatment_plan_page') || 
                                  ($request->has('from_treatment_plan_page') && $request->input('from_treatment_plan_page') == '1') ||
                                  (strpos($request->header('Referer', ''), 'treatment-plan') !== false);
        
        if ($fromTreatmentPlanPage) {
            return redirect()->route('risk.risks.treatment-plan.show', ['riskId' => $riskId, 'treatmentPlanId' => $treatmentPlanId])
                ->with('success', 'Treatment plan updated successfully.');
        }
        
        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Treatment plan updated successfully.');
    }

    /**
     * Store risk review (Step 6)
     */
    public function storeReview(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        // Get valid options from configuration
        $validReviewTypes = getReviewTypes()->pluck('code')->merge(getReviewTypes()->pluck('name'))->toArray();
        $validReviewDecisions = getReviewDecisions()->pluck('code')->merge(getReviewDecisions()->pluck('name'))->toArray();
        $validScores = getRiskScores()->pluck('code')->toArray();
        
        $validated = $request->validate([
            'review_date' => 'required|date',
            'review_type' => 'required|in:' . implode(',', $validReviewTypes),
            'review_reason' => 'nullable|string',
            'review_likelihood_score' => 'nullable|in:' . implode(',', $validScores),
            'review_severity_score' => 'nullable|in:' . implode(',', $validScores),
            'control_effectiveness_assessment' => 'nullable|string',
            'controls_effective' => 'nullable|boolean',
            'effectiveness_evidence' => 'nullable|string',
            'review_findings' => 'nullable|string',
            'opportunities_for_improvement' => 'nullable|string',
            'kpi_metrics' => 'nullable|string',
            'action_required' => 'nullable|boolean',
            'action_required_reason' => 'nullable|string',
            'reassess_risk' => 'nullable|boolean',
            'review_decision' => 'required|in:' . implode(',', $validReviewDecisions),
            'decision_justification' => 'nullable|string',
            'next_review_date' => 'nullable|date',
        ]);

        // Calculate review RPN if scores provided
        if ($validated['review_likelihood_score'] && $validated['review_severity_score']) {
            // Convert code to integer value
            $likelihoodValue = (int) $validated['review_likelihood_score'];
            $severityValue = (int) $validated['review_severity_score'];
            $validated['review_rpn'] = $likelihoodValue * $severityValue;
            $validated['review_risk_level'] = $risk->determineRiskLevel($validated['review_rpn']);
        }

        $validated['risk_id'] = $risk->id;
        $validated['review_number'] = RiskReview::generateReviewNumber();
        $validated['reviewed_by_user_id'] = Auth::id();
        $validated['reviewed_by'] = Auth::user()->name;
        $validated['created_by'] = Auth::id();

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        $review = RiskReview::create($validated);

        // Update risk with residual risk if provided
        if (isset($validated['review_rpn']) && $validated['review_rpn']) {
            // Convert codes to integer values for storage
            $likelihoodValue = (int) $validated['review_likelihood_score'];
            $severityValue = (int) $validated['review_severity_score'];
            $risk->update([
                'residual_likelihood_score' => $likelihoodValue,
                'residual_severity_score' => $severityValue,
                'residual_rpn' => $validated['review_rpn'],
                'residual_risk_level' => $validated['review_risk_level'] ?? null,
                'last_review_date' => $validated['review_date'],
                'next_review_date' => $validated['next_review_date'] ?? null,
            ]);
        } else {
            // Update last_review_date and next_review_date even if RPN not calculated
            $risk->update([
                'last_review_date' => $validated['review_date'],
                'next_review_date' => $validated['next_review_date'] ?? null,
            ]);
        }

        // Move to Step 7 (Risk Monitoring) if currently at Step 6 (Treatment Implementation)
        if ($risk->workflow_step === 6) {
            $status = RiskStatus::forCompany()
                ->where('workflow_step', 7)
                ->ordered()
                ->first();
            
            if ($status) {
                $risk->update([
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'workflow_step' => 7,
                ]);
            }
        }

        // Handle review decision - ISO 31000 Review Decision Loop
        // Normalize decision value (could be code or name)
        $decisionCode = $validated['review_decision'];
        $decisionName = getReviewDecisions()->firstWhere('code', $decisionCode)?->name ?? $decisionCode;
        
        // Check if it's a code or name and normalize
        if (in_array($decisionCode, ['continue_monitoring', 'close_risk', 'additional_controls_needed'])) {
            $decisionCode = $decisionCode;
        } elseif (in_array($decisionCode, ['Continue Monitoring', 'Close Risk', 'Additional Controls Needed'])) {
            // Convert name to code
            $decisionCode = match($decisionCode) {
                'Continue Monitoring' => 'continue_monitoring',
                'Close Risk' => 'close_risk',
                'Additional Controls Needed' => 'additional_controls_needed',
                default => $decisionCode
            };
        }
        
        // Handle the 4 decision paths
        switch ($decisionCode) {
            case 'close_risk':
            case 'Close Risk':
                // Decision 1: Close the Risk
                // Move to Step 8 (Closed) - but validate first
                $closedStatus = RiskStatus::forCompany()
                    ->where('workflow_step', 8)
                    ->ordered()
                    ->first();
                
                if ($closedStatus) {
                    // Validate closure requirements before closing
                    $canClose = true;
                    $closureErrors = [];
                    
                    // Check if at least one review exists (we just created one)
                    if ($risk->reviews()->count() === 0) {
                        $canClose = false;
                        $closureErrors[] = 'At least one review must be completed';
                    }
                    
                    // Check treatment plans if they exist
                    if ($risk->treatmentPlans()->count() > 0) {
                        $completedCount = $risk->treatmentPlans()
                            ->whereIn('implementation_status', ['Completed', 'completed', 'Cancelled', 'cancelled'])
                            ->count();
                        if ($completedCount < $risk->treatmentPlans()->count()) {
                            $canClose = false;
                            $closureErrors[] = 'All treatment plans must be completed or cancelled';
                        }
                    }
                    
                    // Check residual risk and closure justification
                    if (isset($validated['review_rpn']) && $validated['review_rpn'] && $validated['review_rpn'] > ($risk->acceptance_threshold_rpn ?? 15)) {
                        if (empty($validated['decision_justification'])) {
                            $canClose = false;
                            $closureErrors[] = 'Closure justification required when residual RPN exceeds threshold';
                        }
                    }
                    
                    if ($canClose) {
                        $risk->update([
                            'status_id' => $closedStatus->id,
                            'status_name' => $closedStatus->name,
                            'workflow_step' => 8,
                            'closure_date' => now(),
                            'closed_by' => Auth::id(),
                            'closure_justification' => $validated['decision_justification'] ?? $risk->closure_justification,
                        ]);
                    } else {
                        // Don't close, but log the review
                        \Log::warning("Risk {$risk->id} review decision is 'Close Risk' but closure requirements not met: " . implode(', ', $closureErrors));
                    }
                }
                break;
                
            case 'continue_monitoring':
            case 'Continue Monitoring':
                // Decision 2: Continue Monitoring
                // Stay in Step 7 (Risk Monitoring), just update next review date
                if ($risk->workflow_step !== 7) {
                    $monitoringStatus = RiskStatus::forCompany()
                        ->where('workflow_step', 7)
                        ->ordered()
                        ->first();
                    
                    if ($monitoringStatus) {
                        $risk->update([
                            'status_id' => $monitoringStatus->id,
                            'status_name' => $monitoringStatus->name,
                            'workflow_step' => 7,
                        ]);
                    }
                }
                // Update next review date (already done above)
                break;
                
            case 'additional_controls_needed':
            case 'Additional Controls Needed':
                // Decision 4: Escalate & Re-treat the Risk
                // Move to Step 5 (Treatment Planning) for additional treatment planning
                $status = RiskStatus::forCompany()
                    ->where('workflow_step', 5)
                    ->ordered()
                    ->first();
                
                if ($status) {
                    $risk->update([
                        'status_id' => $status->id,
                        'status_name' => $status->name,
                        'workflow_step' => 5,
                    ]);
                }
                break;
        }
        
        // Decision 3: Reassess the Risk
        // Check if user explicitly requested reassessment OR if scores differ significantly
        $shouldReassess = false;
        $reassessReason = '';
        
        // Check 1: User explicitly checked "Reassess Risk" checkbox
        if (isset($validated['reassess_risk']) && $validated['reassess_risk']) {
            $shouldReassess = true;
            $reassessReason = 'User requested reassessment';
        }
        // Check 2: Auto-detect if review scores differ significantly from current assessment
        elseif (isset($validated['review_likelihood_score']) && isset($validated['review_severity_score']) && 
                $validated['review_likelihood_score'] && $validated['review_severity_score']) {
            $currentRPN = $risk->rpn ?? 0;
            $reviewRPN = $validated['review_rpn'] ?? 0;
            
            if ($currentRPN > 0 && $reviewRPN > 0) {
                $difference = abs($currentRPN - $reviewRPN);
                $percentageChange = ($difference / $currentRPN) * 100;
                
                // Auto-reassess if:
                // - Difference is 5 or more points, OR
                // - Percentage change is 50% or more, OR
                // - Risk level changed (e.g., from Low to High)
                $currentRiskLevel = $risk->risk_level ?? '';
                $reviewRiskLevel = $validated['review_risk_level'] ?? '';
                
                if ($difference >= 5 || $percentageChange >= 50 || 
                    ($currentRiskLevel && $reviewRiskLevel && $currentRiskLevel !== $reviewRiskLevel)) {
                    $shouldReassess = true;
                    $reassessReason = "Significant RPN change detected (Current: {$currentRPN}, Review: {$reviewRPN}, Change: {$difference} points / " . round($percentageChange, 1) . "%)";
                }
            }
        }
        
        // Move to Step 3 (Assessment) if reassessment is needed
        if ($shouldReassess) {
            $assessmentStatus = RiskStatus::forCompany()
                ->where('workflow_step', 3)
                ->ordered()
                ->first();
            
            if ($assessmentStatus) {
                $risk->update([
                    'status_id' => $assessmentStatus->id,
                    'status_name' => $assessmentStatus->name,
                    'workflow_step' => 3,
                ]);
                
                // Log the reassessment reason
                \Log::info("Risk {$risk->id} moved to Assessment step. Reason: {$reassessReason}");
            }
        }
        
        // Refresh to get updated values
        $risk->refresh();

        // Log review activity with decision path
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                $decisionDisplay = $decisionName ?? $validated['review_decision'];
                $workflowStepName = match($decisionCode) {
                    'close_risk' => 'Risk Review - Closed',
                    'continue_monitoring' => 'Risk Review - Continue Monitoring',
                    'additional_controls_needed' => 'Risk Review - Returned for Treatment',
                    default => 'Risk Review'
                };
                
                $remarks = "Review completed. Decision: {$decisionDisplay}" . 
                          (isset($validated['review_rpn']) && $validated['review_rpn'] ? ". Residual RPN: {$validated['review_rpn']}" : '') .
                          (isset($validated['decision_justification']) && $validated['decision_justification'] ? ". Justification: " . \Str::limit($validated['decision_justification'], 100) : '');
                
                if ($oldWorkflowStep != $risk->workflow_step || $oldStatus !== $risk->status_name) {
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        $risk->workflow_step ?? 7,
                        $workflowStepName,
                        $oldStatus,
                        $risk->status_name,
                        $remarks
                    );
                } else {
                    \App\Models\AuditModule\AuditActivityLog::log(
                        $risk,
                        'Review Completed',
                        $remarks
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to log review activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk review completed successfully.');
    }

    /**
     * Update risk review
     */
    public function updateReview(Request $request, $riskId, $reviewId)
    {
        $risk = Risk::forCompany()->findOrFail($riskId);
        $review = RiskReview::where('risk_id', $riskId)->findOrFail($reviewId);

        // Get valid options from configuration
        $validReviewTypes = getReviewTypes()->pluck('code')->merge(getReviewTypes()->pluck('name'))->toArray();
        $validReviewDecisions = getReviewDecisions()->pluck('code')->merge(getReviewDecisions()->pluck('name'))->toArray();
        $validScores = getRiskScores()->pluck('code')->toArray();
        
        $validated = $request->validate([
            'review_date' => 'required|date',
            'review_type' => 'required|in:' . implode(',', $validReviewTypes),
            'review_reason' => 'nullable|string',
            'review_likelihood_score' => 'nullable|in:' . implode(',', $validScores),
            'review_severity_score' => 'nullable|in:' . implode(',', $validScores),
            'control_effectiveness_assessment' => 'nullable|string',
            'controls_effective' => 'nullable|boolean',
            'effectiveness_evidence' => 'nullable|string',
            'review_findings' => 'nullable|string',
            'opportunities_for_improvement' => 'nullable|string',
            'kpi_metrics' => 'nullable|string',
            'action_required' => 'nullable|boolean',
            'action_required_reason' => 'nullable|string',
            'reassess_risk' => 'nullable|boolean',
            'review_decision' => 'required|in:' . implode(',', $validReviewDecisions),
            'decision_justification' => 'nullable|string',
            'next_review_date' => 'nullable|date',
        ]);

        // Calculate review RPN if scores provided
        if ($validated['review_likelihood_score'] && $validated['review_severity_score']) {
            // Convert code to integer value
            $likelihoodValue = (int) $validated['review_likelihood_score'];
            $severityValue = (int) $validated['review_severity_score'];
            $validated['review_rpn'] = $likelihoodValue * $severityValue;
            $validated['review_risk_level'] = $risk->determineRiskLevel($validated['review_rpn']);
        } else {
            $validated['review_rpn'] = null;
            $validated['review_risk_level'] = null;
        }

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        $review->update($validated);

        // Update risk with residual risk if provided
        if (isset($validated['review_rpn']) && $validated['review_rpn']) {
            // Convert codes to integer values for storage
            $likelihoodValue = (int) $validated['review_likelihood_score'];
            $severityValue = (int) $validated['review_severity_score'];
            $risk->update([
                'residual_likelihood_score' => $likelihoodValue,
                'residual_severity_score' => $severityValue,
                'residual_rpn' => $validated['review_rpn'],
                'residual_risk_level' => $validated['review_risk_level'] ?? null,
                'last_review_date' => $validated['review_date'],
                'next_review_date' => $validated['next_review_date'] ?? null,
            ]);
        } else {
            // Update last_review_date and next_review_date even if RPN not calculated
            $risk->update([
                'last_review_date' => $validated['review_date'],
                'next_review_date' => $validated['next_review_date'] ?? null,
            ]);
        }
        
        // Handle review decision - ISO 31000 Review Decision Loop (same as storeReview)
        // Normalize decision value (could be code or name)
        $decisionCode = $validated['review_decision'];
        $decisionName = getReviewDecisions()->firstWhere('code', $decisionCode)?->name ?? $decisionCode;
        
        // Check if it's a code or name and normalize
        if (in_array($decisionCode, ['continue_monitoring', 'close_risk', 'additional_controls_needed'])) {
            $decisionCode = $decisionCode;
        } elseif (in_array($decisionCode, ['Continue Monitoring', 'Close Risk', 'Additional Controls Needed'])) {
            // Convert name to code
            $decisionCode = match($decisionCode) {
                'Continue Monitoring' => 'continue_monitoring',
                'Close Risk' => 'close_risk',
                'Additional Controls Needed' => 'additional_controls_needed',
                default => $decisionCode
            };
        }
        
        // Handle the 4 decision paths
        switch ($decisionCode) {
            case 'close_risk':
            case 'Close Risk':
                // Decision 1: Close the Risk
                $closedStatus = RiskStatus::forCompany()
                    ->where('workflow_step', 8)
                    ->ordered()
                    ->first();
                
                if ($closedStatus) {
                    // Validate closure requirements
                    $canClose = true;
                    $closureErrors = [];
                    
                    if ($risk->reviews()->count() === 0) {
                        $canClose = false;
                        $closureErrors[] = 'At least one review must be completed';
                    }
                    
                    if ($risk->treatmentPlans()->count() > 0) {
                        $completedCount = $risk->treatmentPlans()
                            ->whereIn('implementation_status', ['Completed', 'completed', 'Cancelled', 'cancelled'])
                            ->count();
                        if ($completedCount < $risk->treatmentPlans()->count()) {
                            $canClose = false;
                            $closureErrors[] = 'All treatment plans must be completed or cancelled';
                        }
                    }
                    
                    if (isset($validated['review_rpn']) && $validated['review_rpn'] && $validated['review_rpn'] > ($risk->acceptance_threshold_rpn ?? 15)) {
                        if (empty($validated['decision_justification'])) {
                            $canClose = false;
                            $closureErrors[] = 'Closure justification required when residual RPN exceeds threshold';
                        }
                    }
                    
                    if ($canClose) {
                        $risk->update([
                            'status_id' => $closedStatus->id,
                            'status_name' => $closedStatus->name,
                            'workflow_step' => 8,
                            'closure_date' => now(),
                            'closed_by' => Auth::id(),
                            'closure_justification' => $validated['decision_justification'] ?? $risk->closure_justification,
                        ]);
                    }
                }
                break;
                
            case 'continue_monitoring':
            case 'Continue Monitoring':
                // Decision 2: Continue Monitoring
                if ($risk->workflow_step !== 7) {
                    $monitoringStatus = RiskStatus::forCompany()
                        ->where('workflow_step', 7)
                        ->ordered()
                        ->first();
                    
                    if ($monitoringStatus) {
                        $risk->update([
                            'status_id' => $monitoringStatus->id,
                            'status_name' => $monitoringStatus->name,
                            'workflow_step' => 7,
                        ]);
                    }
                }
                break;
                
            case 'additional_controls_needed':
            case 'Additional Controls Needed':
                // Decision 4: Escalate & Re-treat the Risk
                $status = RiskStatus::forCompany()
                    ->where('workflow_step', 5)
                    ->ordered()
                    ->first();
                
                if ($status) {
                    $risk->update([
                        'status_id' => $status->id,
                        'status_name' => $status->name,
                        'workflow_step' => 5,
                    ]);
                }
                break;
        }
        
        // Decision 3: Reassess the Risk (same logic as storeReview)
        $shouldReassess = false;
        $reassessReason = '';
        
        // Check 1: User explicitly checked "Reassess Risk" checkbox
        if (isset($validated['reassess_risk']) && $validated['reassess_risk']) {
            $shouldReassess = true;
            $reassessReason = 'User requested reassessment';
        }
        // Check 2: Auto-detect if review scores differ significantly from current assessment
        elseif (isset($validated['review_likelihood_score']) && isset($validated['review_severity_score']) && 
                $validated['review_likelihood_score'] && $validated['review_severity_score']) {
            $currentRPN = $risk->rpn ?? 0;
            $reviewRPN = $validated['review_rpn'] ?? 0;
            
            if ($currentRPN > 0 && $reviewRPN > 0) {
                $difference = abs($currentRPN - $reviewRPN);
                $percentageChange = ($difference / $currentRPN) * 100;
                
                $currentRiskLevel = $risk->risk_level ?? '';
                $reviewRiskLevel = $validated['review_risk_level'] ?? '';
                
                if ($difference >= 5 || $percentageChange >= 50 || 
                    ($currentRiskLevel && $reviewRiskLevel && $currentRiskLevel !== $reviewRiskLevel)) {
                    $shouldReassess = true;
                    $reassessReason = "Significant RPN change detected (Current: {$currentRPN}, Review: {$reviewRPN}, Change: {$difference} points / " . round($percentageChange, 1) . "%)";
                }
            }
        }
        
        // Move to Step 3 (Assessment) if reassessment is needed
        if ($shouldReassess) {
            $assessmentStatus = RiskStatus::forCompany()
                ->where('workflow_step', 3)
                ->ordered()
                ->first();
            
            if ($assessmentStatus) {
                $risk->update([
                    'status_id' => $assessmentStatus->id,
                    'status_name' => $assessmentStatus->name,
                    'workflow_step' => 3,
                ]);
                
                \Log::info("Risk {$risk->id} moved to Assessment step. Reason: {$reassessReason}");
            }
        }
        
        // Refresh to get updated values
        $risk->refresh();

        // Log review update activity with decision path
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                $decisionDisplay = $decisionName ?? $validated['review_decision'];
                $workflowStepName = match($decisionCode) {
                    'close_risk' => 'Risk Review Updated - Closed',
                    'continue_monitoring' => 'Risk Review Updated - Continue Monitoring',
                    'additional_controls_needed' => 'Risk Review Updated - Returned for Treatment',
                    default => 'Risk Review Updated'
                };
                
                $remarks = "Review updated. Decision: {$decisionDisplay}" . 
                          (isset($validated['review_rpn']) && $validated['review_rpn'] ? ". Residual RPN: {$validated['review_rpn']}" : '') .
                          (isset($validated['decision_justification']) && $validated['decision_justification'] ? ". Justification: " . \Str::limit($validated['decision_justification'], 100) : '');
                
                if ($oldWorkflowStep != $risk->workflow_step || $oldStatus !== $risk->status_name) {
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        $risk->workflow_step ?? 7,
                        $workflowStepName,
                        $oldStatus,
                        $risk->status_name,
                        $remarks
                    );
                } else {
                    \App\Models\AuditModule\AuditActivityLog::log(
                        $risk,
                        'Review Updated',
                        $remarks
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to log review update activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk review updated successfully.');
    }

    /**
     * Close risk (Step 7)
     */
    public function closeRisk(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        if (!$risk->canBeClosed()) {
            return redirect()->route('risk.risks.show', $risk->id)
                ->with('error', 'Risk cannot be closed. Please complete all required steps.');
        }

        $validated = $request->validate([
            'closure_type' => 'required|in:Eliminated,Controlled,Accepted',
            'closure_justification' => 'required|string',
        ]);

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        $validated['closure_date'] = now();
        $validated['closed_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $status = RiskStatus::forCompany()
            ->where('workflow_step', 8) // Step 8 is Closed
            ->ordered()
            ->first();
        
        if ($status) {
            $validated['status_id'] = $status->id;
            $validated['status_name'] = $status->name;
            $validated['workflow_step'] = 8; // Step 8 is Closed
        }

        $risk->update($validated);
        
        // Refresh to get updated values
        $risk->refresh();

        // Log risk closure activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                    $risk,
                    7,
                    'Risk Closure',
                    $oldStatus,
                    $risk->status_name,
                    "Risk closed. Closure type: {$validated['closure_type']}. Justification: {$validated['closure_justification']}"
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log risk closure activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk closed successfully.');
    }

    /**
     * Approve risk to next workflow step
     */
    public function approveToNextStep(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);
        
        // Prevent status changes for closed risks
        if ($risk->status_name === 'Closed') {
            return back()->with('error', 'Cannot proceed. Risk is closed and finalized.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,return,hold',
            'target_status_id' => 'required|integer|exists:risk_statuses,id',
            'remarks' => 'required|string|min:10',
        ], [
            'action.required' => 'Please select an action.',
            'action.in' => 'Invalid action selected.',
            'target_status_id.required' => 'Please select a target status.',
            'target_status_id.exists' => 'Selected status does not exist.',
            'remarks.required' => 'Remarks are required for ISO compliance.',
            'remarks.min' => 'Remarks must be at least 10 characters.',
        ]);

        $targetStatus = RiskStatus::findOrFail($validated['target_status_id']);
        $oldStatus = $risk->status_name;
        $newStatus = $targetStatus->name;

        // Validate workflow progression based on action
        if ($validated['action'] === 'approve') {
            // For approve, check if can proceed to next status
            $nextStatus = $risk->getNextWorkflowStatus();
            
            // If no next status but we're at step 2 (Identified), try to get step 3 status directly
            if (!$nextStatus && ($risk->workflow_step == 2 || $risk->workflow_step == 0 || $risk->workflow_step == 1)) {
                $nextStatus = RiskStatus::forCompany()
                    ->active()
                    ->where('workflow_step', 3)
                    ->ordered()
                    ->first();
            }
            
            // If still no next status, allow manual status selection for step 2
            if (!$nextStatus && ($risk->workflow_step == 2 || $risk->workflow_step == 0 || $risk->workflow_step == 1)) {
                // Allow proceeding to any status with workflow_step 3 (Under Assessment)
                if ($targetStatus->workflow_step != 3) {
                    return back()->with('error', 'Cannot approve to this status. Please select a status for workflow step 3 (Under Assessment).');
                }
            } elseif ($nextStatus && $nextStatus->id != $targetStatus->id) {
                return back()->with('error', 'Cannot approve to this status. Please select the correct next workflow step.');
            }

            // Check if can proceed to the target status (not just next status)
            $targetStep = $targetStatus->workflow_step ?? null;
            if ($targetStep && !$risk->canProceedToStep($targetStep)) {
                $currentStep = $risk->getCurrentWorkflowStep() ?? 2;
                $errorMessage = "Cannot proceed to '{$newStatus}'. Please complete the required actions.";
                
                // Step-specific validation messages
                if ($targetStep === 3 && $currentStep === 2) {
                    // Step 2 to Step 3 - no specific requirements, assessment happens in step 3
                    $errorMessage = "Cannot proceed to '{$newStatus}'. Please ensure the risk is properly identified.";
                } elseif ($targetStep === 4 && $currentStep === 3) {
                    if (!$risk->likelihood_score || !$risk->severity_score || !$risk->assessment_date) {
                        $errorMessage = "Please complete the risk assessment before proceeding to evaluation.";
                    }
                } elseif ($targetStep === 5 && $currentStep === 4) {
                    if (!$risk->evaluation_date) {
                        $errorMessage = "Please complete the risk evaluation before proceeding.";
                    }
                } elseif ($targetStep === 6 && $currentStep === 5) {
                    if ($risk->requires_treatment && $risk->treatmentPlans()->count() === 0) {
                        $errorMessage = "Please add at least one treatment plan before proceeding to implementation.";
                    }
                } elseif ($targetStep === 7 && $currentStep === 6) {
                    if ($risk->requires_treatment) {
                        $allImplemented = $risk->treatmentPlans()
                            ->where('implementation_status', 'Completed')
                            ->count() === $risk->treatmentPlans()->count();
                        if (!$allImplemented) {
                            $errorMessage = "Cannot proceed to Risk Monitoring. All treatment plans must be completed. Please ensure all treatment plans have 'Completed' implementation status.";
                        }
                    }
                } elseif ($targetStep === 8 && $currentStep === 7) {
                    // Step 7 (Risk Monitoring) to Step 8 (Closed) - requires review
                    // Refresh the risk to ensure we have latest data
                    $risk->refresh();
                    
                    $reviewsCount = $risk->reviews()->count();
                    $treatmentPlansCount = $risk->treatmentPlans()->count();
                    
                    // Check reviews first
                    if ($reviewsCount === 0) {
                        $errorMessage = "Cannot close the risk. Please complete at least one risk review before closing. Go to the 'Reviews' tab and click 'Add Review'.";
                    } 
                    // Check treatment plans if they exist
                    elseif ($treatmentPlansCount > 0) {
                        $completedCount = $risk->treatmentPlans()
                            ->whereIn('implementation_status', ['Completed', 'Cancelled'])
                            ->count();
                        $allCompleted = $completedCount === $treatmentPlansCount;
                        
                        if (!$allCompleted) {
                            $incompleteCount = $treatmentPlansCount - $completedCount;
                            $errorMessage = "Cannot close the risk. {$incompleteCount} treatment plan(s) must be completed before closing. Please ensure all treatment plans have 'Completed' or 'Cancelled' implementation status.";
                        } 
                        // Check residual risk if all treatment plans are completed
                        elseif ($risk->residual_rpn && $risk->residual_rpn > ($risk->acceptance_threshold_rpn ?? 15)) {
                            if (empty($risk->closure_justification)) {
                                $errorMessage = "Cannot close the risk. The residual RPN ({$risk->residual_rpn}) exceeds the acceptance threshold (" . ($risk->acceptance_threshold_rpn ?? 15) . "). Please provide closure justification in the risk details explaining why this risk can be closed despite the high residual risk level.";
                            }
                        }
                    } 
                    // Check residual risk if no treatment plans
                    elseif ($risk->residual_rpn && $risk->residual_rpn > ($risk->acceptance_threshold_rpn ?? 15)) {
                        if (empty($risk->closure_justification)) {
                            $errorMessage = "Cannot close the risk. The residual RPN ({$risk->residual_rpn}) exceeds the acceptance threshold (" . ($risk->acceptance_threshold_rpn ?? 15) . "). Please provide closure justification in the risk details explaining why this risk can be closed despite the high residual risk level.";
                        }
                    }
                    
                    // If we still don't have a specific error message, provide a generic one with details
                    if ($errorMessage === "Cannot proceed to '{$newStatus}'. Please complete the required actions.") {
                        $errorMessage = "Cannot close the risk. Please ensure: (1) At least one review is completed, (2) All treatment plans are completed (if any), and (3) Closure justification is provided if residual RPN exceeds threshold.";
                    }
                }
                
                return back()->with('error', $errorMessage);
            }
            
            // Also check canProceedToNextStatus for backward compatibility
            if (!$risk->canProceedToNextStatus() && !$targetStep) {
                $currentStepName = $risk->getCurrentWorkflowStep() ?? 'Unknown';
                $errorMessage = "Cannot proceed to '{$newStatus}'. Please complete the required actions for the current step: {$currentStepName}.";
                
                $currentStep = $risk->getCurrentWorkflowStep() ?? 2;
                if ($currentStep === 3 && (!$risk->likelihood_score || !$risk->severity_score || !$risk->assessment_date)) {
                    $errorMessage = "Please complete the risk assessment before proceeding.";
                } elseif ($currentStep === 4 && !$risk->evaluation_date) {
                    $errorMessage = "Please complete the risk evaluation before proceeding.";
                } elseif ($currentStep === 5 && $risk->requires_treatment && $risk->treatmentPlans()->count() === 0) {
                    $errorMessage = "Please add at least one treatment plan before proceeding.";
                }
                
                return back()->with('error', $errorMessage);
            }

            // Check approval requirements for current workflow step
            $currentStep = $risk->getCurrentWorkflowStep();
            if ($currentStep) {
                $requiredApprovers = \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
                    ->forModule('risk')
                    ->forWorkflowStep($currentStep)
                    ->required()
                    ->get();

                if ($requiredApprovers->count() > 0) {
                    // Check if current user is authorized to approve
                    $userApprover = $requiredApprovers->firstWhere('user_id', Auth::id());
                    if (!$userApprover) {
                        return back()->with('error', 'You are not authorized to approve this workflow step. Please contact the configured approver.');
                    }

                    // Check if approval already exists for this user and step moving to target status
                    $existingApproval = \App\Models\AuditModule\AuditWorkflowApproval::forCompany()
                        ->where('approvable_type', Risk::class)
                        ->where('approvable_id', $risk->id)
                        ->where('workflow_step', $currentStep)
                        ->where('approver_id', Auth::id())
                        ->where('to_status', $targetStatus->name)
                        ->first();

                    if ($existingApproval && $risk->status_name === $targetStatus->name) {
                        return back()->with('error', 'You have already approved this workflow step and the risk has already moved to the target status.');
                    }
                    
                    if ($risk->status_name === $targetStatus->name) {
                        return back()->with('error', 'The risk has already moved to the target status. No approval needed.');
                    }

                    // For multiple approvers, check if all required approvers have approved
                    $multipleApprovers = $requiredApprovers->where('approval_type', 'multiple')->count() > 0;
                    if ($multipleApprovers) {
                        $approvedCount = \App\Models\AuditModule\AuditWorkflowApproval::forCompany()
                            ->where('approvable_type', Risk::class)
                            ->where('approvable_id', $risk->id)
                            ->where('workflow_step', $currentStep)
                            ->whereIn('approver_id', $requiredApprovers->pluck('user_id'))
                            ->count();

                        if ($approvedCount < $requiredApprovers->count()) {
                            // Not all approvers have approved yet, but allow this approval
                        }
                    }
                }
            }
        } elseif ($validated['action'] === 'reject') {
            // For reject, can move to a previous status or specific rejection status
            // Additional validation can be added here
        } elseif ($validated['action'] === 'return') {
            // For return, typically move to a previous status
            // Additional validation can be added here
        } elseif ($validated['action'] === 'hold') {
            // For hold, can set to a hold status
            // Additional validation can be added here
        }

        // Update status
        $risk->status_name = $newStatus;
        $risk->status_id = $targetStatus->id;
        $risk->workflow_step = $targetStatus->workflow_step ?? $risk->workflow_step;
        
        // Set closure date if closing
        if ($newStatus === 'Closed') {
            $risk->closure_date = now();
            $risk->closed_by = Auth::id();
        }

        $risk->updated_by = Auth::id();
        $risk->save();

        // Create approval record if action is approve
        $currentStep = $risk->getCurrentWorkflowStep();
        if ($validated['action'] === 'approve' && $currentStep) {
            $approverConfig = \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
                ->forModule('risk')
                ->forWorkflowStep($currentStep)
                ->where('user_id', Auth::id())
                ->first();

            if ($approverConfig) {
                $user = Auth::user();
                \App\Models\AuditModule\AuditWorkflowApproval::create([
                    'approvable_type' => Risk::class,
                    'approvable_id' => $risk->id,
                    'workflow_step' => $currentStep,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'approver_id' => Auth::id(),
                    'approver_name' => $user->name,
                    'role_type' => $approverConfig->role_type,
                    'iso_role' => $approverConfig->iso_role,
                    'remarks' => $validated['remarks'],
                    'approved_at' => now(),
                    'company_id' => getUserCompany() ?? 0,
                ]);

                // Check if all required approvers have approved (for multiple approvers)
                $requiredApprovers = \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
                    ->forWorkflowStep($currentStep)
                    ->required()
                    ->get();

                if ($requiredApprovers->count() > 1) {
                    $approvedCount = \App\Models\AuditModule\AuditWorkflowApproval::forCompany()
                        ->where('approvable_type', Risk::class)
                        ->where('approvable_id', $risk->id)
                        ->where('workflow_step', $currentStep)
                        ->whereIn('approver_id', $requiredApprovers->pluck('user_id'))
                        ->count();

                    if ($approvedCount < $requiredApprovers->count()) {
                        $remaining = $requiredApprovers->count() - $approvedCount;
                        $successMessage = "Your approval has been recorded. Waiting for {$remaining} more approver(s) before proceeding to: {$newStatus}.";
                        return back()->with('success', $successMessage);
                    }
                }
            }
        }

        // Log workflow transition for chain of custody
        $actionText = ucfirst($validated['action']);
        $remarks = "[{$actionText}] {$validated['remarks']}";
        
        // Map workflow_step to sequential chain of custody step number
        // workflow_step 2 (Identified) -> chain step 1 (Risk Identification) - already logged on creation
        // workflow_step 3 (Under Assessment) -> chain step 2 (Risk Assessment)
        // workflow_step 4 (Evaluated) -> chain step 3 (Risk Evaluation)
        // workflow_step 5 (Treatment Planned) -> chain step 4 (Treatment Planning)
        // workflow_step 6 (Under Implementation) -> chain step 5 (Treatment Implementation)
        // workflow_step 7 (Under Review) -> chain step 6 (Review)
        // workflow_step 8 (Closed) -> chain step 7 (Closed)
        $chainOfCustodyStep = null;
        $chainOfCustodyStepName = null;
        
        if ($risk->workflow_step == 3) {
            $chainOfCustodyStep = 2;
            $chainOfCustodyStepName = 'Risk Assessment';
        } elseif ($risk->workflow_step == 4) {
            $chainOfCustodyStep = 3;
            $chainOfCustodyStepName = 'Risk Evaluation';
        } elseif ($risk->workflow_step == 5) {
            $chainOfCustodyStep = 4;
            $chainOfCustodyStepName = 'Treatment Planning';
        } elseif ($risk->workflow_step == 6) {
            $chainOfCustodyStep = 5;
            $chainOfCustodyStepName = 'Treatment Implementation';
        } elseif ($risk->workflow_step == 7) {
            $chainOfCustodyStep = 6;
            $chainOfCustodyStepName = 'Review';
        } elseif ($risk->workflow_step == 8) {
            $chainOfCustodyStep = 7;
            $chainOfCustodyStepName = 'Closed';
        }
        
        // Log activity if AuditActivityLog is available
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                if ($chainOfCustodyStep !== null) {
                    \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                        $risk,
                        $chainOfCustodyStep,
                        $chainOfCustodyStepName,
                        $oldStatus,
                        $newStatus,
                        $remarks
                    );
                } else {
                    // Fallback if no mapping found
                    \App\Models\AuditModule\AuditActivityLog::logStatusChange(
                        $risk,
                        $oldStatus,
                        $newStatus,
                        $remarks
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to log workflow transition: ' . $e->getMessage());
            }
        }

        $successMessage = "Risk {$validated['action']}d and moved to: {$newStatus}.";
        return back()->with('success', $successMessage);
    }

    /**
     * Change workflow status
     */
    public function changeStatus(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'status_id' => 'required|exists:risk_statuses,id',
        ]);

        $status = RiskStatus::find($validated['status_id']);

        // Check if can proceed to this step
        if ($status->workflow_step && !$risk->canProceedToStep($status->workflow_step)) {
            return redirect()->route('risk.risks.show', $risk->id)
                ->with('error', 'Cannot proceed to this workflow step. Please complete required prerequisites.');
        }

        $oldStatus = $risk->status_name;
        $oldWorkflowStep = $risk->workflow_step;
        
        $risk->update([
            'status_id' => $status->id,
            'status_name' => $status->name,
            'workflow_step' => $status->workflow_step ?? $risk->workflow_step,
            'updated_by' => Auth::id(),
        ]);
        
        // Refresh to get updated values
        $risk->refresh();

        // Log status change activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                if ($oldWorkflowStep != $risk->workflow_step) {
                    // Map workflow_step to sequential chain of custody step number
                    // workflow_step 2 (Identified) -> chain step 1 (Risk Identification) - already logged on creation
                    // workflow_step 3 (Under Assessment) -> chain step 2 (Risk Assessment)
                    // workflow_step 4 (Evaluated) -> chain step 3 (Risk Evaluation)
                    // workflow_step 5 (Treatment Planned) -> chain step 4 (Treatment Planning)
                    // workflow_step 6 (Under Implementation) -> chain step 5 (Treatment Implementation)
                    // workflow_step 7 (Under Review) -> chain step 6 (Review)
                    // workflow_step 8 (Closed) -> chain step 7 (Closed)
                    $chainOfCustodyStep = null;
                    $chainOfCustodyStepName = null;
                    
                    if ($risk->workflow_step == 3) {
                        $chainOfCustodyStep = 2;
                        $chainOfCustodyStepName = 'Risk Assessment';
                    } elseif ($risk->workflow_step == 4) {
                        $chainOfCustodyStep = 3;
                        $chainOfCustodyStepName = 'Risk Evaluation';
                    } elseif ($risk->workflow_step == 5) {
                        $chainOfCustodyStep = 4;
                        $chainOfCustodyStepName = 'Treatment Planning';
                    } elseif ($risk->workflow_step == 6) {
                        $chainOfCustodyStep = 5;
                        $chainOfCustodyStepName = 'Treatment Implementation';
                    } elseif ($risk->workflow_step == 7) {
                        $chainOfCustodyStep = 6;
                        $chainOfCustodyStepName = 'Review';
                    } elseif ($risk->workflow_step == 8) {
                        $chainOfCustodyStep = 7;
                        $chainOfCustodyStepName = 'Closed';
                    }
                    
                    // Only log if we have a valid chain of custody step mapping
                    if ($chainOfCustodyStep !== null) {
                        \App\Models\AuditModule\AuditActivityLog::logWorkflowTransition(
                            $risk,
                            $chainOfCustodyStep,
                            $chainOfCustodyStepName,
                            $oldStatus,
                            $risk->status_name,
                            "Status manually changed"
                        );
                    } else {
                        // Fallback to status change log if no mapping
                        \App\Models\AuditModule\AuditActivityLog::logStatusChange(
                            $risk,
                            $oldStatus,
                            $risk->status_name,
                            "Status manually changed"
                        );
                    }
                } else {
                    \App\Models\AuditModule\AuditActivityLog::logStatusChange(
                        $risk,
                        $oldStatus,
                        $risk->status_name,
                        "Status manually changed"
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to log status change activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Risk status updated successfully.');
    }



    /**
     * Upload attachment
     */
    public function uploadAttachment(Request $request, $id)
    {
        $risk = Risk::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->path();
            
            // Use the same strategy as SampleWorkFlowController - no disk specified (uses default 'local')
            $storedPath = Storage::putFile('risk_attachments', new File($path));
            $pathParts = explode('/', $storedPath);
            
            // Construct URL the same way as SampleWorkFlowController
            $fileUrl = '/storage/risk_attachments/' . urlencode(end($pathParts));
            
            // Construct file details
            $fileName = $file->hashName();
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();
            
            $attachment = RiskAttachment::create([
                'attachable_type' => Risk::class,
                'attachable_id' => $risk->id,
                'file_name' => $fileName,
                'file_path' => $storedPath,  // Store the actual storage path
                'file_type' => $mimeType,
                'file_size' => $size,
                'original_name' => $originalName,
                'description' => $validated['description'] ?? null,
                'uploaded_by' => Auth::id(),
                'company_id' => getUserCompany() ?? 0,
            ]);

            // Log attachment upload activity
            if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
                try {
                    $fileSizeFormatted = $this->formatFileSize($size);
                    $description = "Attachment uploaded: '{$originalName}' ({$fileSizeFormatted})";
                    if (!empty($validated['description'])) {
                        $description .= ". Description: {$validated['description']}";
                    }
                    
                    \App\Models\AuditModule\AuditActivityLog::log(
                        $risk,
                        'Attachment Uploaded',
                        $description
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to log attachment upload activity: ' . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            \Log::error('Attachment upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload attachment: ' . $e->getMessage());
        }

        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Attachment uploaded successfully.');
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($attachmentId)
    {
        $attachment = RiskAttachment::findOrFail($attachmentId);
        
        // Use default disk (same as upload strategy)
        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment($attachmentId)
    {
        $attachment = RiskAttachment::findOrFail($attachmentId);
        $riskId = $attachment->attachable_id;
        $risk = Risk::forCompany()->findOrFail($riskId);
        $fileName = $attachment->original_name ?? 'Unknown';
        
        // Use default disk (same as upload strategy)
        Storage::delete($attachment->file_path);
        $attachment->delete();

        // Log attachment deletion activity
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                \App\Models\AuditModule\AuditActivityLog::log(
                    $risk,
                    'Attachment Deleted',
                    "Attachment deleted: '{$fileName}'"
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log attachment deletion activity: ' . $e->getMessage());
            }
        }

        return redirect()->route('risk.risks.show', $riskId)
            ->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Upload attachment for treatment plan
     */
    public function uploadTreatmentPlanAttachment(Request $request, $riskId, $treatmentPlanId)
    {
        $risk = Risk::forCompany()->findOrFail($riskId);
        $treatmentPlan = RiskTreatmentPlan::where('risk_id', $riskId)->findOrFail($treatmentPlanId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('risk_attachments', 'public');

        $attachment = RiskAttachment::create([
            'attachable_type' => RiskTreatmentPlan::class,
            'attachable_id' => $treatmentPlan->id,
            'title' => $validated['title'],
            'file_name' => $file->hashName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'description' => $validated['description'] ?? null,
            'uploaded_by' => Auth::id(),
            'company_id' => getUserCompany() ?? 0,
        ]);

        // Log attachment upload activity for treatment plan
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                $fileSizeFormatted = $this->formatFileSize($file->getSize());
                $description = "Attachment uploaded to treatment plan '{$treatmentPlan->title}': '{$file->getClientOriginalName()}' ({$fileSizeFormatted})";
                if (!empty($validated['description'])) {
                    $description .= ". Description: {$validated['description']}";
                }
                
                \App\Models\AuditModule\AuditActivityLog::log(
                    $treatmentPlan,
                    'Attachment Uploaded',
                    $description
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log treatment plan attachment upload activity: ' . $e->getMessage());
            }
        }

        // Check if request came from treatment plan show page
        $fromTreatmentPlanPage = $request->has('from_treatment_plan_page') || 
                                  (strpos($request->header('Referer', ''), 'treatment-plan') !== false);
        
        if ($fromTreatmentPlanPage) {
            return redirect()->route('risk.risks.treatment-plan.show', ['riskId' => $riskId, 'treatmentPlanId' => $treatmentPlanId])
                ->with('success', 'Attachment uploaded successfully.');
        }
        
        return redirect()->route('risk.risks.show', $risk->id)
            ->with('success', 'Attachment uploaded successfully.');
    }

    /**
     * Delete attachment for treatment plan
     */
    public function deleteTreatmentPlanAttachment($attachmentId)
    {
        $attachment = RiskAttachment::findOrFail($attachmentId);
        
        if ($attachment->attachable_type !== RiskTreatmentPlan::class) {
            abort(404, 'Attachment not found');
        }
        
        $treatmentPlan = RiskTreatmentPlan::findOrFail($attachment->attachable_id);
        $riskId = $treatmentPlan->risk_id;
        $fileName = $attachment->title ?? $attachment->original_name ?? 'Unknown';
        
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        // Log attachment deletion activity for treatment plan
        if (class_exists(\App\Models\AuditModule\AuditActivityLog::class)) {
            try {
                \App\Models\AuditModule\AuditActivityLog::log(
                    $treatmentPlan,
                    'Attachment Deleted',
                    "Attachment deleted from treatment plan '{$treatmentPlan->title}': '{$fileName}'"
                );
            } catch (\Exception $e) {
                \Log::error('Failed to log treatment plan attachment deletion activity: ' . $e->getMessage());
            }
        }

        // Check referer to determine redirect
        $referer = request()->header('Referer', '');
        if (strpos($referer, 'treatment-plan') !== false) {
            return redirect()->route('risk.risks.treatment-plan.show', ['riskId' => $riskId, 'treatmentPlanId' => $treatmentPlan->id])
                ->with('success', 'Attachment deleted successfully.');
        }
        
        return redirect()->route('risk.risks.show', $riskId)
            ->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Format file size in human-readable format
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Server-side data for DataTables
     */
    public function serverSide(Request $request)
    {
        $columns = array(
            array('db' => 'id', 'dt' => -1),
            array('db' => 'risk_number', 'dt' => 0),
            array('db' => 'title', 'dt' => 1),
            array('db' => 'category_name', 'dt' => 2),
            array('db' => 'risk_level', 'dt' => 3),
            array('db' => 'status_name', 'dt' => 4),
            array('db' => 'date_identified', 'dt' => 5),
            array('db' => 'next_review_date', 'dt' => 6),
        );

        $risks = Risk::forCompany()
            ->selectRaw('risks.id, risk_number, title, category_name, risk_level, status_name, date_identified, next_review_date');

        // Filter by status if provided
        $statusFilter = $request->get('status');
        if ($statusFilter && $statusFilter !== 'All Risks') {
            $risks->where('status_name', $statusFilter);
        }

        // Filter by workflow step
        $workflowStep = $request->get('workflow_step');
        if ($workflowStep !== null) {
            $risks->where('workflow_step', $workflowStep);
        }

        $results = new \App\Datatables\Datatables($risks, $request, $columns);
        $results = $results->execute();

        return response()->json($results, 200);
    }

    /**
     * Store a newly created process link in storage.
     */
    public function storeProcessLink(Request $request, $riskId)
    {
        $validated = $request->validate([
            'business_process_id' => 'required|exists:risk_business_processes,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $risk = Risk::forCompany()->findOrFail($riskId);

        // Check permission (using a general edit permission for now)
        if (!auth()->user()->check_permission(['Risk-Management', 'components', 'Risks', 'Edit'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $link = new \App\Models\RiskManagement\RiskProcessLink();
        $link->risk_id = $risk->id;
        $link->business_process_id = $validated['business_process_id'];
        $link->description = $validated['description'];
        $link->created_by = auth()->id();
        $link->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Process linked successfully',
                'link' => $link->load('businessProcess', 'creator')
            ]);
        }

        return redirect()->back()->with('success', 'Process linked successfully');
    }

    /**
     * Update the specified process link in storage.
     */
    public function updateProcessLink(Request $request, $id)
    {
        $link = \App\Models\RiskManagement\RiskProcessLink::findOrFail($id);

        // Check permission
        if (!auth()->user()->check_permission(['Risk-Management', 'components', 'Risks', 'Edit'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'business_process_id' => 'required|exists:risk_business_processes,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $link->business_process_id = $validated['business_process_id'];
        $link->description = $validated['description'];
        $link->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Process link updated successfully',
                'link' => $link->load('businessProcess', 'creator')
            ]);
        }

        return redirect()->back()->with('success', 'Process link updated successfully');
    }

    /**
     * Remove the specified process link from storage.
     */
    public function destroyProcessLink($id)
    {
        $link = \App\Models\RiskManagement\RiskProcessLink::findOrFail($id);
        
        // Use general risk edit permission or ensure user owns the link/risk
        if (!auth()->user()->check_permission(['Risk-Management', 'components', 'Risks', 'Edit'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $link->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Process link removed successfully']);
        }

        return redirect()->back()->with('success', 'Process link removed successfully');
    }
}


