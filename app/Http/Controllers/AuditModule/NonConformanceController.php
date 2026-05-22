<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\RootCauseAnalysis;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\AuditActivityLog;
use App\Models\AuditModule\AuditAttachment;
use App\Services\AuditModule\AuditNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class NonConformanceController extends Controller
{
    protected $notificationService;

    public function __construct(AuditNotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'All NCs');
        return view('layouts.audit.non-conformances.index', compact('status'));
    }

    public function create(Request $request)
    {
        $auditId = $request->get('audit_id');
        $findingId = $request->get('finding_id');
        
        return view('layouts.audit.non-conformances.create', compact('auditId', 'findingId'));
    }

    public function store(Request $request)
    {
        // Workflow enforcement: Can only create NC if audit is at step 2 or later (Record Findings & NC step)
        if ($request->has('audit_id')) {
            $audit = \App\Models\AuditModule\Audit::forCompany()->find($request->get('audit_id'));
            if ($audit) {
                $currentStep = $audit->getCurrentWorkflowStep();
                if ($currentStep < 2) {
                    return back()->with('error', 'Cannot create non-conformance. Audit must be at "Record Findings & NC" step or later.');
                }
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'origin_id' => 'nullable|exists:nc_origins,id',
            'date_identified' => 'required|date',
            'audit_id' => 'nullable|exists:iso_audits,id',
            'finding_id' => 'nullable|exists:audit_findings,id',
            'iso_clause_violated' => 'nullable|string|max:255',
            'sop_reference' => 'nullable|string|max:255',
            'immediate_action_taken' => 'nullable|string',
            'responsible_person_id' => 'nullable|exists:users,id',
            'responsible_person_name' => 'nullable|string|max:255',
            'target_closure_date' => 'nullable|date|after_or_equal:date_identified',
            'risk_level_id' => 'nullable|exists:risk_levels,id',
            'severity_score' => 'nullable|integer|min:1|max:5',
            'likelihood_score' => 'nullable|integer|min:1|max:5',
            'risk_assessment_notes' => 'nullable|string',
        ]);

        $validated['nc_number'] = NonConformance::generateNcNumber();
        $validated['identified_by_user_id'] = Auth::id();
        $validated['identified_by'] = Auth::user()->name ?? 'System';
        $validated['status_name'] = 'Identified';
        $validated['company_id'] = getUserCompany() ?? 0;
        
        // Map finding_id to audit_finding_id if provided
        if (isset($validated['finding_id'])) {
            $validated['audit_finding_id'] = $validated['finding_id'];
            unset($validated['finding_id']);
        }

        $nc = NonConformance::create($validated);

        AuditActivityLog::logCreation($nc, 'Non-conformance identified');

        // If created from an audit finding, update the finding status
        if ($nc->audit_finding_id) {
            $finding = \App\Models\AuditModule\AuditModuleFinding::find($nc->audit_finding_id);
            if ($finding) {
                $finding->update(['status_name' => 'NC Raised']);
            }
        }

        // Send NC requires action notification
        try {
            $this->notificationService->sendNCRequiresActionNotification($nc);
        } catch (\Exception $e) {
            \Log::error('Failed to send NC notification: ' . $e->getMessage());
        }

        return redirect()->route('audit.nc.show', $nc->id)
            ->with('success', 'Non-conformance created successfully.');
    }

    public function show($id)
    {
        $eagerLoad = [
            'audit',
            'auditFinding',
            'auditFinding.findingCategory',
            'origin',
            'status',
            'riskLevel',
            'identifiedByUser',
            'rootCauseAnalysis.rootCauseMethod',
            'rootCauseAnalysis.approvedByUser',
            'rootCauseAnalysis.createdBy',
            'correctiveActions.actionOwnerUser',
            'correctiveActions.status',
            'correctiveActions.priority',
            'attachments',
        ];

        if (Schema::hasTable('audit_activity_logs')) {
            $eagerLoad[] = 'activityLogs.performedBy';
        }

        if (Schema::hasTable('audit_workflow_approvals')) {
            $eagerLoad[] = 'workflowApprovals.approver';
        }

        $nc = NonConformance::forCompany()
            ->with($eagerLoad)
            ->findOrFail($id);

        // Get root cause methods for RCA form
        $rootCauseMethods = getActiveRootCauseMethods();

        // Get data for CAPA form
        $capaCategories = getActiveCapaCategories();
        $capaActionTypes = getActiveCapaActionTypes();
        $capaPriorities = getActiveCapaPriorities();
        $users = getAuditorUsers();

        return view('layouts.audit.non-conformances.show', compact('nc', 'rootCauseMethods', 'capaCategories', 'capaActionTypes', 'capaPriorities', 'users'));
    }

    public function edit($id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);
        return view('layouts.audit.non-conformances.edit', compact('nc'));
    }

    public function update(Request $request, $id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'origin_id' => 'nullable|exists:nc_origins,id',
            'iso_clause_violated' => 'nullable|string|max:255',
            'sop_reference' => 'nullable|string|max:255',
            'immediate_action_taken' => 'nullable|string',
            'responsible_person_id' => 'nullable|exists:users,id',
            'responsible_person_name' => 'nullable|string|max:255',
            'target_closure_date' => 'nullable|date',
            'risk_level_id' => 'nullable|exists:risk_levels,id',
            'severity_score' => 'nullable|integer|min:1|max:5',
            'likelihood_score' => 'nullable|integer|min:1|max:5',
            'risk_assessment_notes' => 'nullable|string',
        ]);

        $nc->update($validated);

        AuditActivityLog::log($nc, 'Updated', 'Non-conformance details updated');

        return redirect()->route('audit.nc.show', $nc->id)
            ->with('success', 'Non-conformance updated successfully.');
    }

    public function destroy($id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);
        
        AuditActivityLog::log($nc, 'Deleted', 'Non-conformance deleted');
        
        $nc->delete();

        return redirect()->route('audit.nc.index')
            ->with('success', 'Non-conformance deleted successfully.');
    }

    public function changeStatus(Request $request, $id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:Identified,RCA In Progress,CAPA Assigned,Verification Pending,Closed,Cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $nc->status_name;
        $newStatus = $validated['status'];

        // Map status to workflow step for validation
        $statusToStep = [
            'Identified' => 3,
            'RCA In Progress' => 4,
            'CAPA Assigned' => 5,
            'Verification Pending' => 7,
            'Closed' => 8,
        ];

        $targetStep = $statusToStep[$newStatus] ?? null;

        // Workflow enforcement: validate step progression
        if ($targetStep && !$nc->canProceedToStep($targetStep)) {
            $currentStep = $nc->getCurrentWorkflowStep();
            $currentStepName = $nc->getWorkflowStepName() ?? 'Unknown';
            return back()->with('error', "Cannot change status to '{$newStatus}'. Current workflow step: {$currentStepName}. Please complete the required steps in order.");
        }

        // Validation based on status transitions
        if ($newStatus === 'Closed' && !$nc->canBeClosed()) {
            return back()->with('error', 'Cannot close NC. There are still open corrective actions that need to be verified.');
        }

        $nc->status_name = $newStatus;
        
        if ($newStatus === 'Closed') {
            $nc->actual_closure_date = now();
            $nc->closed_by = Auth::id();
            $nc->closure_notes = $validated['notes'] ?? null;
        }

        $nc->save();

        AuditActivityLog::logStatusChange($nc, $oldStatus, $newStatus, $validated['notes'] ?? null);

        return back()->with('success', "Non-conformance status changed to {$newStatus}.");
    }

    public function generatePdf($id)
    {
        $nc = NonConformance::forCompany()
            ->with([
                'riskLevel',
                'identifiedByUser',
                'rootCauseAnalysis',
                'correctiveActions.latestVerification',
            ])
            ->findOrFail($id);

        $company = \App\Company::find(getUserCompany() ?? 0);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('layouts.audit.pdf.nc-report', compact('nc', 'company'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'NC_Report_' . $nc->nc_number . '.pdf';

        return $pdf->stream($filename);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('audit-attachments/' . date('Y/m'), 'public');
            
            $attachment = AuditAttachment::create([
                'attachable_type' => NonConformance::class,
                'attachable_id' => $nc->id,
                'file_name' => $file->hashName(),
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'uploaded_by' => Auth::id(),
                'company_id' => getUserCompany() ?? 0,
            ]);

            AuditActivityLog::log($nc, 'Attachment Uploaded', "File uploaded: {$file->getClientOriginalName()}");

            return redirect()->route('audit.nc.show', $nc->id)
                ->with('success', 'Attachment uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('audit.nc.show', $nc->id)
                ->with('error', 'Failed to upload attachment: ' . $e->getMessage());
        }
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = AuditAttachment::forCompany()->findOrFail($attachmentId);
        
        // Verify the attachment belongs to an NC
        if ($attachment->attachable_type !== NonConformance::class) {
            abort(404, 'Attachment not found.');
        }

        $nc = NonConformance::forCompany()->findOrFail($attachment->attachable_id);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->original_name ?? $attachment->file_name
        );
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = AuditAttachment::forCompany()->findOrFail($attachmentId);
        
        // Verify the attachment belongs to an NC
        if ($attachment->attachable_type !== NonConformance::class) {
            abort(404, 'Attachment not found.');
        }

        $nc = NonConformance::forCompany()->findOrFail($attachment->attachable_id);

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $fileName = $attachment->original_name ?? $attachment->file_name;
            $attachment->delete();

            AuditActivityLog::log($nc, 'Attachment Deleted', "File deleted: {$fileName}");

            return redirect()->route('audit.nc.show', $nc->id)
                ->with('success', 'Attachment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('audit.nc.show', $nc->id)
                ->with('error', 'Failed to delete attachment: ' . $e->getMessage());
        }
    }

    /**
     * Store a root cause analysis for an NC
     */
    public function storeRca(Request $request, $id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'root_cause_method_id' => 'required|exists:root_cause_methods,id',
            'root_cause_description' => 'required|string',
            'contributing_factors' => 'nullable|string',
            'evidence_supporting_rca' => 'nullable|string',
        ]);

        // Get root cause method
        $method = \App\Models\AuditModule\RootCauseMethod::find($validated['root_cause_method_id']);

        // Get default status (Draft)
        $defaultStatus = \App\Models\AuditModule\RcaStatus::where('code', 'DRAFT')->first();
        if (!$defaultStatus) {
            $defaultStatus = \App\Models\AuditModule\RcaStatus::where('is_active', true)->orderBy('order_index')->first();
        }

        $rca = RootCauseAnalysis::create([
            'non_conformance_id' => $nc->id,
            'root_cause_method_id' => $validated['root_cause_method_id'],
            'method_name' => $method->name ?? null,
            'root_cause_description' => $validated['root_cause_description'],
            'contributing_factors' => $validated['contributing_factors'] ?? null,
            'evidence_supporting_rca' => $validated['evidence_supporting_rca'] ?? null,
            'status_id' => $defaultStatus->id ?? null,
            'status_name' => $defaultStatus->name ?? 'Draft',
            'created_by' => Auth::id(),
        ]);

        // Update NC status to "RCA In Progress" if it's still "Identified" and this is the first RCA
        if ($nc->status_name === 'Identified' && $nc->rootCauseAnalyses()->count() === 1) {
            $nc->update(['status_name' => 'RCA In Progress']);
        }

        AuditActivityLog::log($nc, 'RCA Created', 'Root cause analysis created');

        return back()->with('success', 'Root cause analysis created successfully.');
    }

    /**
     * Edit a root cause analysis
     */
    public function editRca($rcaId)
    {
        $rca = RootCauseAnalysis::with(['nonConformance', 'rootCauseMethod', 'status'])
            ->whereHas('nonConformance', function($q) {
                $q->forCompany();
            })
            ->findOrFail($rcaId);

        $nc = $rca->nonConformance;
        
        // Check if NC is closed
        if ($nc->status_name === 'Closed') {
            return back()->with('error', 'Cannot edit root cause analysis. Non-conformance is closed.');
        }

        $rootCauseMethods = getActiveRootCauseMethods();
        $rcaStatuses = \App\Models\AuditModule\RcaStatus::active()->ordered()->get();

        return response()->json([
            'rca' => $rca,
            'rootCauseMethods' => $rootCauseMethods,
            'rcaStatuses' => $rcaStatuses,
        ]);
    }

    /**
     * Update a root cause analysis
     */
    public function updateRca(Request $request, $rcaId)
    {
        $rca = RootCauseAnalysis::with('nonConformance')
            ->whereHas('nonConformance', function($q) {
                $q->forCompany();
            })
            ->findOrFail($rcaId);

        $nc = $rca->nonConformance;
        
        // Check if NC is closed
        if ($nc->status_name === 'Closed') {
            return back()->with('error', 'Cannot update root cause analysis. Non-conformance is closed.');
        }

        $validated = $request->validate([
            'root_cause_method_id' => 'required|exists:root_cause_methods,id',
            'root_cause_description' => 'required|string',
            'contributing_factors' => 'nullable|string',
            'evidence_supporting_rca' => 'nullable|string',
            'status_id' => 'nullable|exists:rca_statuses,id',
        ]);

        // Get root cause method
        $method = \App\Models\AuditModule\RootCauseMethod::find($validated['root_cause_method_id']);

        // Get status if provided
        $status = null;
        if (!empty($validated['status_id'])) {
            $status = \App\Models\AuditModule\RcaStatus::find($validated['status_id']);
        }

        $rca->update([
            'root_cause_method_id' => $validated['root_cause_method_id'],
            'method_name' => $method->name ?? null,
            'root_cause_description' => $validated['root_cause_description'],
            'contributing_factors' => $validated['contributing_factors'] ?? null,
            'evidence_supporting_rca' => $validated['evidence_supporting_rca'] ?? null,
            'status_id' => $status->id ?? $rca->status_id,
            'status_name' => $status->name ?? $rca->status_name,
            'updated_by' => Auth::id(),
        ]);

        AuditActivityLog::log($nc, 'RCA Updated', 'Root cause analysis updated');

        return back()->with('success', 'Root cause analysis updated successfully.');
    }

    /**
     * Delete a root cause analysis
     */
    public function deleteRca($rcaId)
    {
        $rca = RootCauseAnalysis::with('nonConformance')
            ->whereHas('nonConformance', function($q) {
                $q->forCompany();
            })
            ->findOrFail($rcaId);

        $nc = $rca->nonConformance;
        
        // Check if NC is closed
        if ($nc->status_name === 'Closed') {
            return back()->with('error', 'Cannot delete root cause analysis. Non-conformance is closed.');
        }

        // Check if this RCA is referenced by any CAPAs
        $hasCapaReferences = CorrectiveAction::where('root_cause_analysis_id', $rcaId)->exists();
        if ($hasCapaReferences) {
            return back()->with('error', 'Cannot delete root cause analysis. It is referenced by one or more corrective actions.');
        }

        AuditActivityLog::log($nc, 'RCA Deleted', 'Root cause analysis deleted');

        $rca->delete();

        return back()->with('success', 'Root cause analysis deleted successfully.');
    }

    /**
     * Store a corrective action for an NC
     */
    public function storeCapa(Request $request, $id)
    {
        $nc = NonConformance::forCompany()->findOrFail($id);

        // Workflow enforcement: Can only create CAPA at step 5 (after RCA is created)
        $currentStep = $nc->getCurrentWorkflowStep();
        if (!$nc->canProceedToStep(5)) {
            $currentStepName = $nc->getWorkflowStepName() ?? 'Unknown';
            return back()->with('error', "Cannot create corrective action. Current workflow step: {$currentStepName}. Root cause analysis must be completed (Step 4) before creating CAPAs.");
        }

        // Verify RCA exists
        if (!$nc->hasRca()) {
            return back()->with('error', 'Cannot create corrective action. Root cause analysis must be completed first.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'action_type_id' => 'nullable|exists:capa_action_types,id',
            'action_owner_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after_or_equal:today',
            'priority_id' => 'nullable|exists:capa_priorities,id',
            'category_id' => 'nullable|exists:capa_categories,id',
            'expected_outcome' => 'nullable|string',
            'preventive_measure' => 'nullable|string',
        ]);

        // Get action type, priority, and category names
        $actionType = $validated['action_type_id'] ? \App\Models\AuditModule\CapaActionType::find($validated['action_type_id']) : null;
        $priority = $validated['priority_id'] ? \App\Models\AuditModule\CapaPriority::find($validated['priority_id']) : null;
        $category = $validated['category_id'] ? \App\Models\AuditModule\CapaCategory::find($validated['category_id']) : null;
        $actionOwner = \App\User::find($validated['action_owner_id']);

        $capa = CorrectiveAction::create([
            'non_conformance_id' => $nc->id,
            'capa_number' => CorrectiveAction::generateCapaNumber(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'action_type_id' => $validated['action_type_id'] ?? null,
            'action_type_name' => $actionType->name ?? 'Corrective',
            'action_owner_id' => $validated['action_owner_id'],
            'action_owner_name' => $actionOwner->name ?? null,
            'due_date' => $validated['due_date'],
            'priority_id' => $validated['priority_id'] ?? null,
            'priority_name' => $priority->name ?? 'Medium',
            'category_id' => $validated['category_id'] ?? null,
            'capa_category_name' => $category->name ?? null,
            'expected_outcome' => $validated['expected_outcome'] ?? null,
            'preventive_measure' => $validated['preventive_measure'] ?? null,
            'status_name' => 'Open',
            'created_by' => Auth::id(),
            'assigned_date' => now(),
            'company_id' => getUserCompany() ?? 0,
        ]);

        AuditActivityLog::logCreation($capa, 'Corrective action assigned');

        // Update NC status if this is the first CAPA
        if (in_array($nc->status_name, ['Identified', 'RCA In Progress'])) {
            $nc->update(['status_name' => 'CAPA Assigned']);
        }

        // Check if CAPA is already overdue (shouldn't happen, but just in case)
        if ($capa->due_date && now()->greaterThan($capa->due_date)) {
            try {
                $this->notificationService->sendOverdueCAPANotification($capa);
            } catch (\Exception $e) {
                \Log::error('Failed to send overdue CAPA notification: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Corrective action created successfully.');
    }

    /**
     * Search samples for linking to NCs
     */
    public function searchSamples(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 15;

        $query = \App\SampleHeader::query()
            ->where(function($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Cancelled');
            })
            ->where(function($q) {
                $q->whereNull('isactive')->orWhere('isactive', 1);
            });

        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('batch_code', 'like', '%' . $search . '%')
                  ->orWhere('reference_number', 'like', '%' . $search . '%')
                  ->orWhere('sap_batch', 'like', '%' . $search . '%')
                  ->orWhereHas('samples', function($sampleQuery) use ($search) {
                      $sampleQuery->where('sample_code', 'like', '%' . $search . '%');
                  });
            });
        }

        $totalCount = $query->count();
        $samples = $query->orderBy('batch_code', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['id', 'batch_code', 'reference_number', 'receipt_date']);

        $results = [];
        foreach ($samples as $sample) {
            $displayText = $sample->batch_code ?? 'N/A';
            if ($sample->reference_number) {
                $displayText .= ' - ' . $sample->reference_number;
            }
            $results[] = [
                'id' => $sample->id,
                'text' => $displayText
            ];
        }

        return response()->json([
            'success' => true,
            'samples' => $results,
            'pagination' => [
                'has_more' => ($page * $perPage) < $totalCount
            ]
        ]);
    }

    /**
     * Search equipment for linking to NCs
     */
    public function searchEquipment(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 15;

        $query = \App\Models\Equipments\Equipment::query()
            ->where('active', 1)
            ->where(function($q) {
                $q->whereNull('is_disposal')->orWhere('is_disposal', 0);
            });

        // Apply company filter if available
        $companyId = getUserCompany();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('equipment_number', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        $totalCount = $query->count();
        $equipments = $query->orderBy('name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['id', 'name', 'equipment_number', 'serial_number']);

        $results = [];
        foreach ($equipments as $equipment) {
            $displayText = $equipment->name ?? 'N/A';
            if ($equipment->equipment_number) {
                $displayText .= ' [' . $equipment->equipment_number . ']';
            }
            if ($equipment->serial_number) {
                $displayText .= ' - S/N: ' . $equipment->serial_number;
            }
            $results[] = [
                'id' => $equipment->id,
                'text' => $displayText
            ];
        }

        return response()->json([
            'success' => true,
            'equipment' => $results,
            'pagination' => [
                'has_more' => ($page * $perPage) < $totalCount
            ]
        ]);
    }

    /**
     * Search methods for linking to NCs
     */
    public function searchMethods(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 15;

        $query = \App\AnalysisMethod::query()
            ->where('active', 1)
            ->where('is_sampling_method', 0); // Exclude sampling methods

        // Apply company filter if available
        $companyId = getUserCompany();
        if ($companyId) {
            $query->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            });
        }

        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $totalCount = $query->count();
        $methods = $query->orderBy('name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['id', 'name', 'code']);

        $results = [];
        foreach ($methods as $method) {
            $displayText = $method->name ?? 'N/A';
            if ($method->code) {
                $displayText .= ' (' . $method->code . ')';
            }
            $results[] = [
                'id' => $method->id,
                'text' => $displayText
            ];
        }

        return response()->json([
            'success' => true,
            'methods' => $results,
            'pagination' => [
                'has_more' => ($page * $perPage) < $totalCount
            ]
        ]);
    }
}
