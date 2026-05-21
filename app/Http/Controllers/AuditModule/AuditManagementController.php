<?php

namespace App\Http\Controllers\AuditModule;

use App\Http\Controllers\Controller;
use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditActivityLog;
use App\Models\AuditModule\AuditAttachment;
use App\Models\AuditModule\AuditModuleFinding;
use App\Models\AuditModule\AuditStatus;
use App\Models\AuditModule\AuditTeamMember;
use App\Models\AuditModule\AuditTeamRole;
use App\Models\AuditModule\AuditWorkflowApprover;
use App\Models\AuditModule\AuditWorkflowApproval;
use App\Services\AuditModule\AuditNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditManagementController extends Controller
{
    protected $notificationService;

    public function __construct(AuditNotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'All Audit');
        
        return view('layouts.audit.audits.index', compact('status'));
    }

    public function create()
    {
        return view('layouts.audit.audits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'audit_type_id' => 'required|exists:audit_types,id',
            'scheduled_date' => 'required|date',
            'scope' => 'nullable|string',
            'criteria' => 'nullable|string',
            'objectives' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'lead_auditor_id' => 'nullable|exists:users,id',
            'lead_auditor_name' => 'nullable|string|max:255',
            'auditee_name' => 'nullable|string|max:255',
            'auditee_department_id' => 'nullable|integer|exists:inventory_departments,id',
        ]);

        $validated['audit_number'] = Audit::generateAuditNumber();
        $validated['status_name'] = 'Scheduled';
        $validated['created_by'] = Auth::id();
        $validated['company_id'] = getUserCompany() ?? 0;

        $audit = Audit::create($validated);

        // Log creation
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

        // Send notification if scheduled date is within 7 days
        if ($audit->scheduled_date && now()->diffInDays(Carbon::parse($audit->scheduled_date)) <= 7) {
            try {
                $this->notificationService->sendUpcomingAuditNotification($audit, now()->diffInDays(Carbon::parse($audit->scheduled_date)));
            } catch (\Exception $e) {
                \Log::error('Failed to send audit notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('audit.audits.show', $audit->id)
            ->with('success', 'Audit created successfully.');
    }

    public function show($id)
    {
        $audit = Audit::forCompany()
            ->with([
                'auditType',
                'checklist.items',
                'checklists.items',
                'checklists.auditType',
                'leadAuditor',
                'teamMembers.user',
                'teamMembers.role',
                'findings.findingCategory',
                'findings.riskLevel',
                'findings.nonConformance',
                'nonConformances.correctiveActions',
                'attachments.uploadedByUser',
                'activityLogs.performedBy',
                'workflowApprovals.approver'
            ])
            ->findOrFail($id);

        // Get available users (excluding already assigned team members and lead auditor)
        $assignedUserIds = $audit->teamMembers->pluck('user_id')->merge([$audit->lead_auditor_id])->filter()->toArray();
        $availableUsers = \App\User::where('active', 1)
            ->whereNotIn('id', $assignedUserIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Get audit team roles
        $teamRoles = AuditTeamRole::active()
            ->orderBy('name')
            ->get();

        // Get finding categories, risk levels, users, and statuses for finding form
        $findingCategories = getActiveFindingCategories();
        $riskLevels = getActiveRiskLevels();
        $users = getAuditorUsers();
        $findingStatuses = \App\Models\AuditModule\FindingStatus::active()->ordered()->get();

        // Get next workflow status for action buttons
        $nextWorkflowStatus = $audit->getNextWorkflowStatus();
        $canProceedToNext = $audit->canProceedToNextStatus();
        
        // Get all available workflow statuses for action modal
        $availableStatuses = getActiveAuditStatuses();
        
        // Get findings that require NC but don't have one raised yet
        $findingsRequiringNC = $audit->getFindingsRequiringNC();

        return view('layouts.audit.audits.show', compact('audit', 'availableUsers', 'teamRoles', 'findingCategories', 'riskLevels', 'users', 'findingStatuses', 'nextWorkflowStatus', 'canProceedToNext', 'availableStatuses', 'findingsRequiringNC'));
    }

    public function edit($id)
    {
        $audit = Audit::forCompany()->findOrFail($id);
        return view('layouts.audit.audits.edit', compact('audit'));
    }

    public function update(Request $request, $id)
    {
        $audit = Audit::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'audit_type_id' => 'required|exists:audit_types,id',
            'scheduled_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'scope' => 'nullable|string',
            'criteria' => 'nullable|string',
            'objectives' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'lead_auditor_id' => 'nullable|exists:users,id',
            'lead_auditor_name' => 'nullable|string|max:255',
            'auditee_name' => 'nullable|string|max:255',
            'auditee_department_id' => 'nullable|integer|exists:inventory_departments,id',
            'summary' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        $audit->update($validated);

        AuditActivityLog::log($audit, 'Updated', 'Audit details updated');

        return redirect()->route('audit.audits.show', $audit->id)
            ->with('success', 'Audit updated successfully.');
    }

    public function destroy($id)
    {
        $audit = Audit::forCompany()->findOrFail($id);
        
        AuditActivityLog::log($audit, 'Deleted', 'Audit deleted');
        
        $audit->delete();

        return redirect()->route('audit.audits.index')
            ->with('success', 'Audit deleted successfully.');
    }

    public function changeStatus(Request $request, $id)
    {
        $audit = Audit::forCompany()->findOrFail($id);
        
        // Prevent status changes for closed audits
        if ($audit->status_name === 'Closed') {
            return back()->with('error', 'Cannot change status. Audit is closed and finalized.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:Scheduled,In Progress,Findings Review,Pending Closure,Closed,Cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $audit->status_name;
        $newStatus = $validated['status'];

        // Map status to workflow step for validation
        $statusToStep = [
            'Scheduled' => 1,
            'In Progress' => 2,
            'Findings Review' => 2,
            'Pending Closure' => 7,
            'Closed' => 8,
        ];

        $targetStep = $statusToStep[$newStatus] ?? null;

        // Workflow enforcement: validate step progression
        if ($targetStep && !$audit->canProceedToStep($targetStep)) {
            $currentStep = $audit->getCurrentWorkflowStep();
            $currentStepName = $audit->getWorkflowStepName() ?? 'Unknown';
            
            // Provide specific error message based on what's missing
            $errorMessage = "Cannot change status to '{$newStatus}'. Current workflow step: {$currentStepName}.";
            if ($currentStep === 2 && $audit->findings()->count() === 0) {
                $errorMessage .= " Please record at least one finding before proceeding.";
            } elseif ($targetStep === 5) {
                // Check for missing RCAs
                $ncs = $audit->nonConformances()->get();
                $missingRcas = [];
                foreach ($ncs as $nc) {
                    if (!$nc->hasRca()) {
                        $missingRcas[] = $nc->nc_number;
                    }
                }
                if (!empty($missingRcas)) {
                    $errorMessage .= " The following NCs need Root Cause Analysis: " . implode(', ', $missingRcas) . ".";
                } else {
                    $errorMessage .= " Please complete the required steps in order.";
                }
            } else {
                $errorMessage .= " Please complete the required steps in order.";
            }
            
            return back()->with('error', $errorMessage);
        }

        // Validation based on status transitions
        if ($newStatus === 'Closed' && !$audit->canProceedToStep(8)) {
            // Check which CAPAs are not verified (only check if verification record exists)
            $unverifiedCapas = [];
            foreach ($audit->nonConformances as $nc) {
                foreach ($nc->correctiveActions as $capa) {
                    if (!$capa->hasVerification()) {
                        $unverifiedCapas[] = $capa->capa_number . ' (no verification record)';
                    }
                }
            }
            if (!empty($unverifiedCapas)) {
                return back()->with('error', 'Cannot close audit. The following corrective actions must be verified: ' . implode(', ', $unverifiedCapas) . '.');
            } else {
                return back()->with('error', 'Cannot close audit. All corrective actions must be verified.');
            }
        }

        // Check if Summary, Conclusion, and Recommendations are filled before closing
        if ($newStatus === 'Closed' || $newStatus === 'Pending Closure') {
            $missingFields = [];
            if (empty($audit->executive_summary)) {
                $missingFields[] = 'Summary';
            }
            if (empty($audit->conclusions)) {
                $missingFields[] = 'Conclusion';
            }
            if (empty($audit->recommendations)) {
                $missingFields[] = 'Recommendations';
            }
            
            if (!empty($missingFields)) {
                $editUrl = route('audit.audits.edit', $audit->id);
                return back()->with('error', 'Cannot ' . strtolower($newStatus) . ' audit. The following required fields must be completed: ' . implode(', ', $missingFields) . '. <a href="' . $editUrl . '" class="alert-link">Click here to fill them</a>.');
            }
        }

        // Get the status record to update both status_id and status_name
        $targetStatus = \App\Models\AuditModule\AuditStatus::where('name', $newStatus)
            ->forCompany()
            ->first();
        
        if ($targetStatus) {
            // Update both status_id and status_name to keep them in sync
            $audit->status_id = $targetStatus->id;
            $audit->status_name = $newStatus;
        } else {
            // Fallback: just update status_name if status record not found
            $audit->status_name = $newStatus;
        }
        
        if ($newStatus === 'In Progress' && !$audit->start_date) {
            $audit->start_date = now();
        }
        
        if ($newStatus === 'Closed') {
            $audit->closure_date = now();
            $audit->closed_by = Auth::id();
            
            // Send audit completed notification
            try {
                $this->notificationService->sendAuditCompletedNotification($audit);
            } catch (\Exception $e) {
                \Log::error('Failed to send audit completed notification: ' . $e->getMessage());
            }
        }

        $audit->updated_by = Auth::id();
        $audit->save();

        // Log workflow transition for chain of custody
        $currentStep = $audit->getCurrentWorkflowStep();
        $workflowStepName = $audit->getWorkflowStepName() ?? $newStatus;
        
        if ($currentStep) {
            AuditActivityLog::logWorkflowTransition(
                $audit,
                $currentStep,
                $workflowStepName,
                $oldStatus,
                $newStatus,
                $validated['notes'] ?? null
            );
        } else {
            // Fallback to regular status change log
            AuditActivityLog::logStatusChange($audit, $oldStatus, $newStatus, $validated['notes'] ?? null);
        }

        return back()->with('success', "Audit status changed to {$newStatus}.");
    }

    public function approveToNextStep(Request $request, $id)
    {
        $audit = Audit::forCompany()->findOrFail($id);
        
        // Prevent status changes for closed audits
        if ($audit->status_name === 'Closed') {
            return back()->with('error', 'Cannot proceed. Audit is closed and finalized.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,return,hold',
            'target_status_id' => 'required|integer|exists:audit_statuses,id',
            'remarks' => 'required|string|min:10',
        ], [
            'action.required' => 'Please select an action.',
            'action.in' => 'Invalid action selected.',
            'target_status_id.required' => 'Please select a target status.',
            'target_status_id.exists' => 'Selected status does not exist.',
            'remarks.required' => 'Remarks are required for ISO compliance.',
            'remarks.min' => 'Remarks must be at least 10 characters.',
        ]);

        $targetStatus = AuditStatus::findOrFail($validated['target_status_id']);
        $oldStatus = $audit->status_name;
        $newStatus = $targetStatus->name;

        // Check if Summary, Conclusion, and Recommendations are filled before closing
        if (($newStatus === 'Closed' || $newStatus === 'Pending Closure') && $validated['action'] === 'approve') {
            $missingFields = [];
            if (empty($audit->executive_summary)) {
                $missingFields[] = 'Summary';
            }
            if (empty($audit->conclusions)) {
                $missingFields[] = 'Conclusion';
            }
            if (empty($audit->recommendations)) {
                $missingFields[] = 'Recommendations';
            }
            
            if (!empty($missingFields)) {
                $editUrl = route('audit.audits.edit', $audit->id);
                return back()->with('error', 'Cannot ' . strtolower($newStatus) . ' audit. The following required fields must be completed: ' . implode(', ', $missingFields) . '. <a href="' . $editUrl . '" class="alert-link">Click here to fill them</a>.');
            }
        }

        // Validate workflow progression based on action
        if ($validated['action'] === 'approve') {
            // For approve, check if can proceed to next status
            $nextStatus = $audit->getNextWorkflowStatus();
            if (!$nextStatus || $nextStatus->id != $targetStatus->id) {
                return back()->with('error', 'Cannot approve to this status. Please select the correct next workflow step.');
            }

            if (!$audit->canProceedToNextStatus()) {
                $currentStepName = $audit->getWorkflowStepName() ?? 'Unknown';
                $errorMessage = "Cannot proceed to '{$newStatus}'. Please complete the required actions for the current step: {$currentStepName}.";
                
                $currentStep = $audit->getCurrentWorkflowStep() ?? 1;
                if ($currentStep === 1 && $audit->status_name === 'Scheduled') {
                    $errorMessage = "Please start the audit to begin recording findings.";
                } elseif ($currentStep === 2 && $audit->findings()->count() === 0) {
                    $errorMessage = "Please record at least one finding before proceeding.";
                } elseif ($currentStep === 3) {
                    // Check for findings requiring NCs
                    $findingsRequiringNC = $audit->getFindingsRequiringNC();
                    if ($findingsRequiringNC->count() > 0) {
                        $findingNumbers = $findingsRequiringNC->pluck('finding_number')->join(', ');
                        $errorMessage = "Cannot proceed. The following findings require non-conformances to be raised: {$findingNumbers}.";
                    }
                } elseif ($currentStep === 4) {
                    // Check if all NCs have root cause analyses
                    if (!$audit->allNCsHaveRootCauseAnalyses()) {
                        $ncsWithoutRca = $audit->getNCsWithoutRootCauseAnalysis();
                        $ncNumbers = $ncsWithoutRca->pluck('nc_number')->join(', ');
                        $errorMessage = "Cannot proceed. The following non-conformances require root cause analysis: {$ncNumbers}.";
                    }
                }
                
                return back()->with('error', $errorMessage);
            }

            // Check approval requirements for current workflow step
            $currentStep = $audit->getCurrentWorkflowStep();
            if ($currentStep) {
                $requiredApprovers = AuditWorkflowApprover::forCompany()
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
                    // Approval should be based on audit status - only block if audit has already moved to target status
                    $existingApproval = AuditWorkflowApproval::forCompany()
                        ->where('approvable_type', Audit::class)
                        ->where('approvable_id', $audit->id)
                        ->where('workflow_step', $currentStep)
                        ->where('approver_id', Auth::id())
                        ->where('to_status', $targetStatus->name)
                        ->first();

                    // Only block if audit has already moved to target status
                    // If audit is still at current status, allow approval even if user approved before
                    // This handles cases where approval is needed for non-conformances or other actions within the same step
                    // The approval check is based on audit status, not just whether user approved the step
                    if ($existingApproval && $audit->status_name === $targetStatus->name) {
                        return back()->with('error', 'You have already approved this workflow step and the audit has already moved to the target status.');
                    }
                    
                    // Also check if audit has already moved to target status (regardless of approval)
                    // This prevents duplicate approvals when audit status has already changed
                    if ($audit->status_name === $targetStatus->name) {
                        return back()->with('error', 'The audit has already moved to the target status. No approval needed.');
                    }

                    // For multiple approvers, check if all required approvers have approved
                    $multipleApprovers = $requiredApprovers->where('approval_type', 'multiple')->count() > 0;
                    if ($multipleApprovers) {
                        $approvedCount = AuditWorkflowApproval::forCompany()
                            ->where('approvable_type', Audit::class)
                            ->where('approvable_id', $audit->id)
                            ->where('workflow_step', $currentStep)
                            ->whereIn('approver_id', $requiredApprovers->pluck('user_id'))
                            ->count();

                        if ($approvedCount < $requiredApprovers->count()) {
                            // Not all approvers have approved yet, but allow this approval
                            // We'll check again after creating the approval record
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
        $audit->status_name = $newStatus;
        
        // Set start date if moving from Scheduled
        if ($oldStatus === 'Scheduled' && $newStatus === 'In Progress' && !$audit->start_date) {
            $audit->start_date = now();
        }
        
        // Set closure date if closing
        if ($newStatus === 'Closed') {
            $audit->closure_date = now();
            $audit->closed_by = Auth::id();
            
            // Send audit completed notification
            try {
                $this->notificationService->sendAuditCompletedNotification($audit);
            } catch (\Exception $e) {
                \Log::error('Failed to send audit completed notification: ' . $e->getMessage());
            }
        }

        $audit->updated_by = Auth::id();
        $audit->save();

        // Create approval record if action is approve
        $currentStep = $audit->getCurrentWorkflowStep();
        if ($validated['action'] === 'approve' && $currentStep) {
            $approverConfig = AuditWorkflowApprover::forCompany()
                ->forWorkflowStep($currentStep)
                ->where('user_id', Auth::id())
                ->first();

            if ($approverConfig) {
                $user = Auth::user();
                AuditWorkflowApproval::create([
                    'approvable_type' => Audit::class,
                    'approvable_id' => $audit->id,
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
                $requiredApprovers = AuditWorkflowApprover::forCompany()
                    ->forWorkflowStep($currentStep)
                    ->required()
                    ->get();

                if ($requiredApprovers->count() > 1) {
                    $approvedCount = AuditWorkflowApproval::forCompany()
                        ->where('approvable_type', Audit::class)
                        ->where('approvable_id', $audit->id)
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
        $workflowStepName = $audit->getWorkflowStepName() ?? $newStatus;
        $actionText = ucfirst($validated['action']);
        $remarks = "[{$actionText}] {$validated['remarks']}";
        
        if ($currentStep) {
            AuditActivityLog::logWorkflowTransition(
                $audit,
                $currentStep,
                $workflowStepName,
                $oldStatus,
                $newStatus,
                $remarks
            );
        } else {
            // Fallback to regular status change log
            AuditActivityLog::logStatusChange($audit, $oldStatus, $newStatus, $remarks);
        }

        $successMessage = "Audit {$validated['action']}d and moved to: {$newStatus}.";
        return back()->with('success', $successMessage);
    }

    public function generatePdf($id)
    {
        $audit = Audit::forCompany()
            ->with([
                'auditType',
                'leadAuditor',
                'findings.category',
                'findings.riskLevel',
                'nonConformances',
            ])
            ->findOrFail($id);

        $company = \App\Company::find(getUserCompany() ?? 0);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('layouts.audit.pdf.audit-report', compact('audit', 'company'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Audit_Report_' . preg_replace('/[\/\\\\]/', '_', $audit->audit_number) . '.pdf';

        return $pdf->stream($filename);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $audit = Audit::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('audit-attachments/' . date('Y/m'), 'public');
            
            $attachment = AuditAttachment::create([
                'attachable_type' => Audit::class,
                'attachable_id' => $audit->id,
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

            AuditActivityLog::log($audit, 'Attachment Uploaded', "File uploaded: {$file->getClientOriginalName()}");

            return redirect()->route('audit.audits.show', $audit->id)
                ->with('success', 'Attachment uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('audit.audits.show', $audit->id)
                ->with('error', 'Failed to upload attachment: ' . $e->getMessage());
        }
    }

    public function downloadAttachment($attachmentId)
    {
        $attachment = AuditAttachment::forCompany()->findOrFail($attachmentId);
        
        // Verify the attachment belongs to an audit
        if ($attachment->attachable_type !== Audit::class) {
            abort(404, 'Attachment not found.');
        }

        $audit = Audit::forCompany()->findOrFail($attachment->attachable_id);

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
        
        // Verify the attachment belongs to an audit
        if ($attachment->attachable_type !== Audit::class) {
            abort(404, 'Attachment not found.');
        }

        $audit = Audit::forCompany()->findOrFail($attachment->attachable_id);

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $fileName = $attachment->original_name ?? $attachment->file_name;
            $attachment->delete();

            AuditActivityLog::log($audit, 'Attachment Deleted', "File deleted: {$fileName}");

            return redirect()->route('audit.audits.show', $audit->id)
                ->with('success', 'Attachment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('audit.audits.show', $audit->id)
                ->with('error', 'Failed to delete attachment: ' . $e->getMessage());
        }
    }

    /**
     * Add a team member to an audit
     */
    public function addTeamMember(Request $request, $id)
    {
        $audit = Audit::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'nullable|exists:audit_team_roles,id',
            'responsibilities' => 'nullable|string|max:1000',
        ]);

        // Check if user is already a team member
        $existingMember = AuditTeamMember::where('audit_id', $audit->id)
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingMember) {
            return back()->with('error', 'This user is already a member of the audit team.');
        }

        // Get role name if role_id is provided
        $roleName = null;
        if ($validated['role_id']) {
            $role = AuditTeamRole::find($validated['role_id']);
            $roleName = $role ? $role->name : null;
        }

        $teamMember = AuditTeamMember::create([
            'audit_id' => $audit->id,
            'user_id' => $validated['user_id'],
            'role_id' => $validated['role_id'] ?? null,
            'role_name' => $roleName,
            'responsibilities' => $validated['responsibilities'] ?? null,
        ]);

        $user = \App\User::find($validated['user_id']);
        AuditActivityLog::log($audit, 'Team Member Added', "Team member added: {$user->name} ({$roleName})");

        return back()->with('success', 'Team member added successfully.');
    }

    /**
     * Update a team member
     */
    public function updateTeamMember(Request $request, $teamMemberId)
    {
        $teamMember = AuditTeamMember::findOrFail($teamMemberId);
        $audit = Audit::forCompany()->findOrFail($teamMember->audit_id);

        $validated = $request->validate([
            'role_id' => 'nullable|exists:audit_team_roles,id',
            'responsibilities' => 'nullable|string|max:1000',
        ]);

        // Get role name if role_id is provided
        $roleName = null;
        if ($validated['role_id']) {
            $role = AuditTeamRole::find($validated['role_id']);
            $roleName = $role ? $role->name : null;
        }

        $teamMember->update([
            'role_id' => $validated['role_id'] ?? null,
            'role_name' => $roleName,
            'responsibilities' => $validated['responsibilities'] ?? null,
        ]);

        $user = $teamMember->user;
        AuditActivityLog::log($audit, 'Team Member Updated', "Team member updated: {$user->name}");

        return back()->with('success', 'Team member updated successfully.');
    }

    /**
     * Remove a team member from an audit
     */
    public function removeTeamMember($teamMemberId)
    {
        $teamMember = AuditTeamMember::findOrFail($teamMemberId);
        $audit = Audit::forCompany()->findOrFail($teamMember->audit_id);

        $user = $teamMember->user;
        $userName = $user->name;

        $teamMember->delete();

        AuditActivityLog::log($audit, 'Team Member Removed', "Team member removed: {$userName}");

        return back()->with('success', 'Team member removed successfully.');
    }

    /**
     * Store a new finding for an audit
     */
    public function storeFinding(Request $request, $id)
    {
        $audit = Audit::forCompany()->findOrFail($id);

        // Workflow enforcement: Can only add findings at step 2
        $currentStep = $audit->getCurrentWorkflowStep();
        if ($currentStep < 2) {
            // Allow if scheduled, but change status to In Progress
            if ($audit->status_name === 'Scheduled') {
                $audit->status_name = 'In Progress';
                $audit->start_date = now();
                $audit->save();
            } else {
                return back()->with('error', 'Cannot add findings. Audit must be in progress (Step 2).');
            }
        }

        $validated = $request->validate([
            'finding_category_id' => 'required|exists:finding_categories,id',
            'observation' => 'required|string',
            'requirement' => 'nullable|string',
            'objective_evidence' => 'nullable|string',
            'iso_clause' => 'nullable|string|max:255',
            'sop_reference' => 'nullable|string|max:255',
            'risk_level_id' => 'nullable|exists:risk_levels,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'responsible_person' => 'nullable|string|max:255',
            'response_due_date' => 'nullable|date|after_or_equal:today',
        ]);

        // Get finding category and risk level names
        $findingCategory = \App\Models\AuditModule\FindingCategory::find($validated['finding_category_id']);
        $riskLevel = $validated['risk_level_id'] ? \App\Models\AuditModule\RiskLevel::find($validated['risk_level_id']) : null;

        // Get default status (Open)
        $defaultStatus = \App\Models\AuditModule\FindingStatus::where('code', 'OPEN')->first();
        if (!$defaultStatus) {
            $defaultStatus = \App\Models\AuditModule\FindingStatus::active()->ordered()->first();
        }

        // Generate finding number
        $findingNumber = AuditModuleFinding::generateFindingNumber($audit->id);

        // Get order index
        $lastFinding = AuditModuleFinding::where('audit_id', $audit->id)
            ->orderBy('order_index', 'desc')
            ->first();
        $orderIndex = $lastFinding ? $lastFinding->order_index + 1 : 1;

        $finding = AuditModuleFinding::create([
            'finding_number' => $findingNumber,
            'audit_id' => $audit->id,
            'finding_category_id' => $validated['finding_category_id'],
            'finding_category_name' => $findingCategory->name ?? null,
            'observation' => $validated['observation'],
            'requirement' => $validated['requirement'] ?? null,
            'objective_evidence' => $validated['objective_evidence'] ?? null,
            'iso_clause' => $validated['iso_clause'] ?? null,
            'sop_reference' => $validated['sop_reference'] ?? null,
            'risk_level_id' => $validated['risk_level_id'] ?? null,
            'risk_level_name' => $riskLevel->name ?? null,
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'responsible_person' => $validated['responsible_person'] ?? null,
            'response_due_date' => $validated['response_due_date'] ?? null,
            'status_id' => $defaultStatus->id ?? null,
            'status_name' => $defaultStatus->name ?? 'Open',
            'order_index' => $orderIndex,
            'created_by' => Auth::id(),
        ]);

        AuditActivityLog::log($audit, 'Finding Added', "Finding added: {$findingNumber} - {$findingCategory->name}");

        return back()->with('success', 'Finding added successfully.');
    }

    /**
     * Update an existing finding
     */
    public function updateFinding(Request $request, $id, $findingId)
    {
        $audit = Audit::forCompany()->findOrFail($id);
        $finding = AuditModuleFinding::where('audit_id', $audit->id)->findOrFail($findingId);

        // Workflow enforcement: Can only edit findings at step 2 or later
        $currentStep = $audit->getCurrentWorkflowStep();
        if ($currentStep < 2) {
            return back()->with('error', 'Cannot edit findings. Audit must be in progress (Step 2).');
        }

        $validated = $request->validate([
            'finding_category_id' => 'required|exists:finding_categories,id',
            'observation' => 'required|string',
            'requirement' => 'nullable|string',
            'objective_evidence' => 'nullable|string',
            'iso_clause' => 'nullable|string|max:255',
            'sop_reference' => 'nullable|string|max:255',
            'risk_level_id' => 'nullable|exists:risk_levels,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'responsible_person' => 'nullable|string|max:255',
            'response_due_date' => 'nullable|date',
            'status_id' => 'nullable|exists:finding_statuses,id',
        ]);

        // Get finding category and risk level names
        $findingCategory = \App\Models\AuditModule\FindingCategory::find($validated['finding_category_id']);
        $riskLevel = $validated['risk_level_id'] ? \App\Models\AuditModule\RiskLevel::find($validated['risk_level_id']) : null;
        $status = $validated['status_id'] ? \App\Models\AuditModule\FindingStatus::find($validated['status_id']) : null;

        $finding->update([
            'finding_category_id' => $validated['finding_category_id'],
            'finding_category_name' => $findingCategory->name ?? null,
            'observation' => $validated['observation'],
            'requirement' => $validated['requirement'] ?? null,
            'objective_evidence' => $validated['objective_evidence'] ?? null,
            'iso_clause' => $validated['iso_clause'] ?? null,
            'sop_reference' => $validated['sop_reference'] ?? null,
            'risk_level_id' => $validated['risk_level_id'] ?? null,
            'risk_level_name' => $riskLevel->name ?? null,
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'responsible_person' => $validated['responsible_person'] ?? null,
            'response_due_date' => $validated['response_due_date'] ?? null,
            'status_id' => $status->id ?? $finding->status_id,
            'status_name' => $status->name ?? $finding->status_name,
        ]);

        AuditActivityLog::log($audit, 'Finding Updated', "Finding updated: {$finding->finding_number} - {$findingCategory->name}");

        return back()->with('success', 'Finding updated successfully.');
    }
}
