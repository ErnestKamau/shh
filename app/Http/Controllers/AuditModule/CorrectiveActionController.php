<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\VerificationRecord;
use App\Models\AuditModule\AuditActivityLog;
use App\Models\AuditModule\AuditAttachment;
use App\Services\AuditModule\AuditNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CorrectiveActionController extends Controller
{
    protected $notificationService;

    public function __construct(AuditNotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'All CAPAs');
        return view('layouts.audit.corrective-actions.index', compact('status'));
    }

    public function create(Request $request)
    {
        $ncId = $request->get('nc_id');
        return view('layouts.audit.corrective-actions.create', compact('ncId'));
    }

    public function store(Request $request)
    {
        // Workflow enforcement: Validate NC can proceed to step 5
        $ncId = $request->get('non_conformance_id');
        if ($ncId) {
            $nc = \App\Models\AuditModule\NonConformance::forCompany()->find($ncId);
            if ($nc) {
                if (!$nc->canProceedToStep(5)) {
                    $currentStepName = $nc->getWorkflowStepName() ?? 'Unknown';
                    return back()->with('error', "Cannot create corrective action. Current workflow step: {$currentStepName}. Root cause analysis must be completed (Step 4) before creating CAPAs.");
                }
                // Verify RCA exists
                if (!$nc->hasRca()) {
                    return back()->with('error', 'Cannot create corrective action. Root cause analysis must be completed first.');
                }
            }
        }

        $validated = $request->validate([
            'non_conformance_id' => 'required|exists:non_conformances,id',
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

        $validated['capa_number'] = CorrectiveAction::generateCAPANumber();
        $validated['status_name'] = 'Open';
        $validated['created_by'] = Auth::id();
        $validated['assigned_date'] = now();
        $validated['company_id'] = getUserCompany() ?? 0;

        $capa = CorrectiveAction::create($validated);

        AuditActivityLog::logCreation($capa, 'Corrective action assigned');

        // Update NC status if this is the first CAPA
        $nc = $capa->nonConformance;
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

        return redirect()->route('audit.capa.show', $capa->id)
            ->with('success', 'Corrective action created successfully.');
    }

    public function show($id)
    {
        $eagerLoad = [
            'nonConformance.audit',
            'nonConformance.rootCauseAnalysis',
            'category',
            'actionOwnerUser',
            'createdBy',
            'updatedBy',
            'latestVerification.verifiedByUser',
            'attachments',
        ];

        if (Schema::hasTable('audit_activity_logs')) {
            $eagerLoad[] = 'activityLogs.performedBy';
        }

        if (Schema::hasTable('audit_workflow_approvals')) {
            $eagerLoad[] = 'workflowApprovals.approver';
        }

        $capa = CorrectiveAction::forCompany()
            ->with($eagerLoad)
            ->findOrFail($id);

        $currentWorkflowStep = $capa->getCurrentWorkflowStep();
        $canImplement = $capa->canProceedToStep(6) || $currentWorkflowStep === 6;
        
        // Get audit workflow step to determine if we're at Verify step (step 7)
        $audit = null;
        $auditWorkflowStep = null;
        $nextWorkflowStatus = null;
        $isAuditClosed = false;
        $canProceedToNext = false;
        
        if ($capa->nonConformance && $capa->nonConformance->audit) {
            $audit = $capa->nonConformance->audit;
            $auditWorkflowStep = $audit->getCurrentWorkflowStep();
            $nextWorkflowStatus = $audit->getNextWorkflowStatus();
            $isAuditClosed = $audit->status_name === 'Closed';
            $canProceedToNext = $audit->canProceedToNextStatus();
        }

        // Get verification results for the modal
        $verificationResults = getActiveVerificationResults();

        return view('layouts.audit.corrective-actions.show', compact('capa', 'currentWorkflowStep', 'canImplement', 'auditWorkflowStep', 'audit', 'nextWorkflowStatus', 'isAuditClosed', 'canProceedToNext', 'verificationResults'));
    }

    public function edit($id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);
        return view('layouts.audit.corrective-actions.edit', compact('capa'));
    }

    public function update(Request $request, $id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'action_type_id' => 'nullable|exists:capa_action_types,id',
            'action_owner_id' => 'required|exists:users,id',
            'due_date' => 'required|date',
            'priority_id' => 'nullable|exists:capa_priorities,id',
            'category_id' => 'nullable|exists:capa_categories,id',
            'expected_outcome' => 'nullable|string',
            'implementation_notes' => 'nullable|string',
            'implementation_evidence' => 'nullable|string',
            'preventive_measure' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();
        $capa->update($validated);

        AuditActivityLog::log($capa, 'Updated', 'Corrective action details updated');

        return redirect()->route('audit.capa.show', $capa->id)
            ->with('success', 'Corrective action updated successfully.');
    }

    public function destroy($id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);
        
        AuditActivityLog::log($capa, 'Deleted', 'Corrective action deleted');
        
        $capa->delete();

        return redirect()->route('audit.capa.index')
            ->with('success', 'Corrective action deleted successfully.');
    }

    public function changeStatus(Request $request, $id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Implemented,Verification Pending,Verified,Closed,Cancelled',
            'notes' => 'nullable|string',
            'implementation_notes' => 'nullable|string',
        ]);

        $oldStatus = $capa->status_name;
        $newStatus = $validated['status'];

        // Map status to workflow step for validation
        $statusToStep = [
            'Open' => 5,
            'Assigned' => 5,
            'In Progress' => 6,
            'Implemented' => 6,
            'Verification Pending' => 7,
            'Verified' => 7,
            'Closed' => 8,
        ];

        $targetStep = $statusToStep[$newStatus] ?? null;

        // Workflow enforcement: validate step progression
        if ($targetStep && !$capa->canProceedToStep($targetStep)) {
            $currentStep = $capa->getCurrentWorkflowStep();
            $currentStepName = $capa->getWorkflowStepName() ?? 'Unknown';
            return back()->with('error', "Cannot change status to '{$newStatus}'. Current workflow step: {$currentStepName}. Please complete the required steps in order.");
        }

        $capa->status_name = $newStatus;
        
        if (in_array($newStatus, ['Implemented', 'Verification Pending'])) {
            $capa->implementation_date = now();
            if (isset($validated['implementation_notes'])) {
                $capa->implementation_notes = $validated['implementation_notes'];
            }
            
            // Send verification pending notification
            if ($newStatus === 'Verification Pending') {
                try {
                    $this->notificationService->sendVerificationPendingNotification($capa);
                } catch (\Exception $e) {
                    \Log::error('Failed to send verification pending notification: ' . $e->getMessage());
                }
            }
        }

        // Check if CAPA is overdue
        if ($capa->due_date && now()->greaterThan($capa->due_date) && !in_array($newStatus, ['Verified', 'Closed', 'Cancelled'])) {
            try {
                $this->notificationService->sendOverdueCAPANotification($capa);
            } catch (\Exception $e) {
                \Log::error('Failed to send overdue CAPA notification: ' . $e->getMessage());
            }
        }

        $capa->updated_by = Auth::id();
        $capa->save();

        AuditActivityLog::logStatusChange($capa, $oldStatus, $newStatus, $validated['notes'] ?? null);

        // Check if all CAPAs for the NC are closed/verified
        $this->updateNCStatusIfNeeded($capa->nonConformance);

        return back()->with('success', "Corrective action status changed to {$newStatus}.");
    }

    public function implement(Request $request, $id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);
        
        // Workflow enforcement: Must be at step 5 or 6 to implement
        $currentStep = $capa->getCurrentWorkflowStep();
        if ($currentStep < 5) {
            $currentStepName = $capa->getWorkflowStepName() ?? 'Unknown';
            return back()->with('error', "Cannot implement corrective action. Current workflow step: {$currentStepName}. CAPA must be assigned (Step 5) before implementation.");
        }

        $validated = $request->validate([
            'implementation_date' => 'required|date|before_or_equal:today',
            'implementation_notes' => 'required|string|min:10',
            'implementation_evidence' => 'nullable|string',
        ]);

        $oldStatus = $capa->status_name;
        
        // Update implementation details
        $capa->update([
            'implementation_date' => $validated['implementation_date'],
            'implementation_notes' => $validated['implementation_notes'],
            'implementation_evidence' => $validated['implementation_evidence'] ?? null,
            'status_name' => 'Implemented',
            'updated_by' => Auth::id(),
        ]);

        AuditActivityLog::log($capa, 'Implemented', "Corrective action implemented. Notes: " . Str::limit($validated['implementation_notes'], 100));

        return back()->with('success', 'Corrective action marked as implemented successfully.');
    }

    public function verify(Request $request, $id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);
        
        // Workflow enforcement: Can only verify at step 7
        $currentStep = $capa->getCurrentWorkflowStep();
        if (!$capa->canProceedToStep(7)) {
            $currentStepName = $capa->getWorkflowStepName() ?? 'Unknown';
            return back()->with('error', "Cannot verify corrective action. Current workflow step: {$currentStepName}. CAPA must be implemented (Step 6) before verification.");
        }

        // Must be implemented before verification (or already verified to allow updates)
        if (!in_array($capa->status_name, ['Implemented', 'Verification Pending', 'Verified'])) {
            return back()->with('error', 'Cannot verify corrective action. CAPA must be implemented first.');
        }
        
        $validated = $request->validate([
            'result_id' => 'required|exists:verification_results,id',
            'result_name' => 'required|string',
            'verification_method' => 'nullable|string',
            'evidence_reviewed' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        // Get the verification result to access its configuration
        $verificationResult = \App\Models\AuditModule\VerificationResult::findOrFail($validated['result_id']);

        $verificationData = [
            'verification_number' => VerificationRecord::generateVerificationNumber(),
            'corrective_action_id' => $capa->id,
            'verification_date' => now(),
            'verified_by' => Auth::user()->name ?? Auth::user()->email ?? 'System',
            'verified_by_user_id' => Auth::id(),
            'effectiveness_result_id' => $verificationResult->id,
            'effectiveness_result_name' => $verificationResult->name,
            'verification_method' => $validated['verification_method'] ?? null,
            'evidence_reviewed' => $validated['evidence_reviewed'] ?? null,
            'comments' => $validated['comments'] ?? null,
            'closure_status_name' => 'Pending',
            'created_by' => Auth::id(),
        ];

        // Handle if requires reopen
        if ($verificationResult->requires_reopen) {
            $verificationData['requires_reopen'] = true;
        }

        // Check if verification already exists - update it instead of creating new one
        $existingVerification = $capa->latestVerification;
        if ($existingVerification) {
            // Update existing verification record (exclude verification_number as it shouldn't change)
            unset($verificationData['verification_number']);
            $existingVerification->update($verificationData);
            $verification = $existingVerification;
            AuditActivityLog::log($capa, 'Verification Updated', 'Verification record updated');
        } else {
            // Create new verification record
            $verification = VerificationRecord::create($verificationData);
            AuditActivityLog::log($capa, 'Verified', 'Corrective action verified');
        }

        // Determine next workflow step based on verification result configuration
        $nextWorkflowStep = $verificationResult->next_workflow_step;
        
        // Get the audit to update workflow if needed
        $audit = $capa->nonConformance?->audit;
        
        if ($nextWorkflowStep !== null && $audit) {
            // Find the status for the next workflow step
            $nextStatus = \App\Models\AuditModule\AuditStatus::active()
                ->forCompany()
                ->where('workflow_step', $nextWorkflowStep)
                ->ordered()
                ->first();
            
            if ($nextStatus) {
                // Update audit workflow status
                $audit->status_name = $nextStatus->name;
                $audit->save();
                
                AuditActivityLog::log($audit, 'Workflow Updated', "Workflow moved to step {$nextWorkflowStep} ({$nextStatus->name}) based on verification result: {$verificationResult->name}");
            }
        }
        
        // Update CAPA status based on verification result
        // If next workflow step is 6 (Implement), revert CAPA to Implemented status
        // Otherwise, mark as Verified
        if ($nextWorkflowStep === 6) {
            $capa->update([
                'status_name' => 'Implemented',
                'verification_date' => now(),
            ]);
        } else {
            $capa->update([
                'status_name' => 'Verified',
                'verification_date' => now(),
            ]);
        }
            
        if (!$existingVerification) {
            $resultText = $verificationResult->name;
            AuditActivityLog::log($capa, 'Verified', "Corrective action verified. Result: {$resultText}");
        }

        // Check if all CAPAs for the NC are verified
        $this->updateNCStatusIfNeeded($capa->nonConformance);

        return back()->with('success', 'Verification recorded successfully.');
    }

    private function updateNCStatusIfNeeded($nc)
    {
        $openCAPAs = $nc->correctiveActions()
            ->whereNotIn('status_name', ['Closed', 'Verified', 'Cancelled'])
            ->count();

        if ($openCAPAs === 0) {
            $verifiedCAPAs = $nc->correctiveActions()
                ->where('status_name', 'Verified')
                ->count();

            if ($verifiedCAPAs > 0) {
                $nc->update(['status_name' => 'Verification Pending']);
            }
        }
    }

    public function generatePdf($id)
    {
        $capa = CorrectiveAction::forCompany()
            ->with([
                'nonConformance',
                'actionOwnerUser',
                'category',
                'priority',
                'latestVerification',
            ])
            ->findOrFail($id);

        $company = \App\Company::find(getUserCompany() ?? 0);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('layouts.audit.pdf.capa-report', compact('capa', 'company'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'CAPA_Report_' . preg_replace('/[\/\\\\]/', '_', $capa->capa_number) . '.pdf';

        return $pdf->stream($filename);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $capa = CorrectiveAction::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('audit/attachments', 'public');

            $attachment = AuditAttachment::create([
                'attachable_type' => CorrectiveAction::class,
                'attachable_id' => $capa->id,
                'file_name' => $file->getClientOriginalName(),
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'uploaded_by' => Auth::id(),
                'company_id' => getUserCompany() ?? 0,
            ]);

            AuditActivityLog::log($capa, 'Attachment Uploaded', "File uploaded: {$file->getClientOriginalName()}");

            return redirect()->route('audit.capa.show', $capa->id)
                ->with('success', 'Attachment uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('audit.capa.show', $capa->id)
                ->with('error', 'Failed to upload attachment: ' . $e->getMessage());
        }
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = AuditAttachment::forCompany()->findOrFail($attachmentId);
        
        // Verify the attachment belongs to a CAPA
        if ($attachment->attachable_type !== CorrectiveAction::class) {
            abort(404, 'Attachment not found.');
        }

        $capa = CorrectiveAction::forCompany()->findOrFail($attachment->attachable_id);

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
        
        // Verify the attachment belongs to a CAPA
        if ($attachment->attachable_type !== CorrectiveAction::class) {
            abort(404, 'Attachment not found.');
        }

        $capa = CorrectiveAction::forCompany()->findOrFail($attachment->attachable_id);

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $fileName = $attachment->original_name ?? $attachment->file_name;
            $attachment->delete();

            AuditActivityLog::log($capa, 'Attachment Deleted', "File deleted: {$fileName}");

            return redirect()->route('audit.capa.show', $capa->id)
                ->with('success', 'Attachment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('audit.capa.show', $capa->id)
                ->with('error', 'Failed to delete attachment: ' . $e->getMessage());
        }
    }
}
