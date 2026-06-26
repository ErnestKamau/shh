<?php

namespace App\Livewire\Crm\Complaint;

use App\Models\CRM\Complaint;
use App\Models\CRM\CapaRecord;
use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Services\CRM\ComplaintInvestigationReportService;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;
use Illuminate\Validation\ValidationException;

class ComplaintShow extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $workflowStage;
    public $initialComplaintWorkflow;
    public $initialWorkflowName;
    public $activeTab = 'details';

    protected $queryString = [
        'activeTab' => ['except' => 'details', 'as' => 'tab'],
    ];

    public function mount($id)
    {
        $this->initialize();
        $this->checkPermission(CrmConstants::PERMISSION_COMPLAINT_VIEW);

        $this->complaintId = $id;
        $this->complaint = Complaint::findOrFail($id);
        $this->initialComplaintWorkflow = $this->complaint->complaint_workflow;
        
        $this->calculateWorkflowStage();
        $this->initialWorkflowName = $this->workflowStage ?? 'All Complaints';

        // Compatibility check for legacy 'investigation' tab bookmarks
        if (request('tab') === 'investigation') {
            $this->activeTab = 'resolution';
        }

        if ($this->activeTab === 'details' && !request()->has('tab')) {
            $this->activeTab = match((int) $this->complaint->complaint_workflow) {
                1 => 'details',
                2 => 'resolution',
                3 => 'resolution',
                4 => 'resolution',
                default => 'details',
            };
        }

        // Safety check for manually requested tabs
        if ($this->activeTab === 'capa' && (!$this->hasCarIssued || $this->complaint->complaint_workflow < 2)) {
            $this->activeTab = 'resolution';
        }
    }

    public function calculateWorkflowStage()
    {
        $this->workflowStage = null;
        $complaint_workflow = getComplaintsWorkFlowValues();
        foreach ($complaint_workflow as $x => $x_value) {
            if ($x_value == $this->complaint->complaint_workflow) {
                $this->workflowStage = $x;
                break;
            }
        }
        if ($this->workflowStage === null) {
            $this->workflowStage = 'All Complaints';
        }
    }

    #[On('complaint-workflow-updated')]
    #[On('checkpoint-completed')]
    #[On('refresh-workflow')]
    public function handleWorkflowUpdate($message = 'Task completed.')
    {
        $this->complaint->refresh();
        $this->calculateWorkflowStage();
        
        // Use alert dispatch for consistent UI feedback
        $this->dispatch('alert', ['type' => 'success', 'message' => $message]);
        
        // Only redirect back to the initial stage list if the workflow stage has actually changed
        if ($this->complaint->complaint_workflow != $this->initialComplaintWorkflow) {
            return redirect()->route('complaint-workflow', ['stage' => $this->initialWorkflowName]);
        }
    }

    #[On('resolution-saved')]
    public function refreshComplaint()
    {
        $this->complaint->refresh();
        $this->complaint->load('resolutions');
        $this->calculateWorkflowStage();
    }

    #[On('attachment-added')]
    #[On('attachment-deleted')]
    public function refreshAttachmentsCount()
    {
        // This method triggers a re-render of the parent component, 
        // updating the attachmentsCount property in the tab header.
    }

    #[On('switch-tab')]
    public function switchTab($tab)
    {
        if ($tab === 'nc' || $tab === 'capa') {
            $this->activeTab = 'resolution';
            return;
        }

        $this->activeTab = $tab;
    }

    public function getAttachmentsCountProperty(): int
    {
        return $this->complaint->attachments()->where('is_delete', '!=', 1)->count();
    }

    public function getResolutionsCountProperty(): int
    {
        return $this->complaint->resolutions()->count();
    }

    public function getHasCarIssuedProperty(): bool
    {
        return $this->complaint->resolutions()->where('car_required', true)->exists();
    }

    public function getHasNcrRequiredProperty(): bool
    {
        return $this->complaint->resolutions()->where('ncr_required', true)->exists();
    }

    public function getIsInvestigationCauseRecordedProperty()
    {
        return !empty($this->complaint->resolutions()->where('complaint_id', $this->complaint->id)->first()?->cause_of_complaint);
    }

    public function getGranularStatusProperty(): string
    {
        $mainStatus = $this->workflowStage;
        
        // If in Resolution stage, check CAPA status for more detail
        if ($this->complaint->complaint_workflow >= 2) {
            $res = $this->complaint->resolutions()->first();
            if ($res && $res->car_required) {
                $capa = $this->complaint->capaRecord;
                if ($capa) {
                    // This logic mirrors ComplaintCapaTab's deriveCapaStatus
                    $cleanDetails = trim(strip_tags((string)$capa->details_of_non_conformance));
                    $cleanRootCause = trim(strip_tags((string)$res->root_cause_analysis));
                    $cleanCorrectiveAction = trim(strip_tags((string)$res->corrective_action_taken));
                    $cleanAcceptance = trim(strip_tags((string)$res->findings));

                    if (empty($res->issued_to) || empty($res->issued_by)) return $mainStatus . ' (Draft)';
                    if (empty($cleanDetails) || empty($res->car_type)) return $mainStatus . ' (Assigned)';
                    if (empty($cleanRootCause) || empty($cleanCorrectiveAction)) return $mainStatus . ' (Analysed)';
                    if (empty($capa->effectiveness_verified_by) || empty($cleanAcceptance)) return $mainStatus . ' (Verify)';
                    return $mainStatus . ' (Completed)';
                }
            } elseif ($res && !$res->car_required) {
                 if (empty($res->cause_of_complaint)) return $mainStatus . ' (Investigation)';
                 if (empty($res->action_taken) || empty($res->corrective_action_taken)) return $mainStatus . ' (Action Pending)';
                 return $mainStatus . ' (Finalizing)';
            }
        }

        return $mainStatus;
    }

    public function getNextActionProperty(): array
    {
        $workflow = (int) $this->complaint->complaint_workflow;

        // Stage 1: Open Complaint
        if ($workflow === 1) {
            return [
                'label' => 'Approve & Advance',
                'icon' => 'mdi-check-circle-outline',
                'tab' => 'details',
                'action' => 'approve'
            ];
        }

        // Stage 2: Resolution (Internal sections)
        if ($workflow >= 2 && $workflow < 4) {
             $res = $this->complaint->resolutions()->first();
             
             // 1. Investigation Findings
             if (!$res || empty($res->cause_of_complaint)) {
                 return [
                     'label' => 'Add Investigation Findings',
                     'icon' => 'mdi-plus',
                     'tab' => 'resolution',
                     'action' => 'add_findings'
                 ];
             }

             // 2. CAR Decision
             if (!$this->isInvestigationCheckpointCompleted) {
                 return [
                     'label' => 'Record CAR Decision',
                     'icon' => 'mdi-alert-decagram-outline',
                     'tab' => 'resolution',
                     'action' => 'record_decision'
                 ];
             }

             // 3. Dependent Actions
             if ($res->car_required) {
                 $capa = $this->complaint->capaRecord;
                 
                 // If NC required and problem statement/RCA not fully done (NC step)
                 if ($res->ncr_required) {
                    $hasNc = $capa && !empty(trim(strip_tags((string)$capa->details_of_non_conformance)));
                    if (!$hasNc) {
                        return [
                            'label' => 'Add Non-Conformance',
                            'icon' => 'mdi-plus',
                            'tab' => 'resolution', // NC is a sub-tab of resolution in the UI flow
                            'action' => 'add_nc'
                        ];
                    }
                 }

                 // CAPA details
                 if (!$capa || empty($res->issued_to) || empty($res->issued_by)) {
                     return [
                         'label' => 'Add CAPA Assignment',
                         'icon' => 'mdi-plus',
                         'tab' => 'resolution',
                         'action' => 'add_capa'
                     ];
                 }

                 if (empty($res->root_cause_analysis) || empty($res->corrective_action_taken)) {
                     return [
                         'label' => 'Add Action Plan',
                         'icon' => 'mdi-plus',
                         'tab' => 'resolution',
                         'action' => 'add_plan'
                     ];
                 }

                 if (empty($capa->effectiveness_verified_by) || empty($res->findings)) {
                     return [
                         'label' => 'Add Verification',
                         'icon' => 'mdi-check-all',
                         'tab' => 'resolution',
                         'action' => 'add_verification'
                     ];
                 }
             } else {
                 // No CAR path
                 if (empty($res->action_taken) || empty($res->corrective_action_taken)) {
                     return [
                         'label' => 'Add Resolution Actions',
                         'icon' => 'mdi-plus',
                         'tab' => 'resolution',
                         'action' => 'add_actions'
                     ];
                 }
             }

        }

        // Stage 4: Approval
        if ($workflow === 4) {
            return [
                'label' => 'Final Review & Close',
                'icon' => 'mdi-lock-check-outline',
                'tab' => 'resolution', // In Resolution tab, Review & Close section
                'action' => 'close_case'
            ];
        }

        return [
            'label' => 'Edit',
            'icon' => 'mdi-pencil-outline',
            'tab' => $this->activeTab,
            'action' => 'toggle_edit'
        ];
    }

    public function triggerNextAction()
    {
        $action = $this->nextAction;
        
        // 1. Switch to the relevant tab if needed
        if ($this->activeTab !== $action['tab']) {
            $this->switchTab($action['tab']);
        }

        // 2. Dispatch the trigger to the children
        $this->dispatch('trigger-next-action', action: $action['action']);
    }


    public function getIsInvestigationActionsRecordedProperty()
    {
        $res = $this->complaint->resolutions()->where('complaint_id', $this->complaint->id)->first();
        return !empty($res?->action_taken) && !empty($res?->corrective_action_taken);
    }

    public function getIsInvestigationCheckpointCompletedProperty()
    {
        // Check if the workflow decision has been logged in the chain of custody
        return $this->complaint->chainOfCustody()
            ->where('action', 'like', 'Classification Determined%')
            ->exists();
    }

    public function getIsCapaCompletedProperty(): bool
    {
        $res = $this->complaint->resolutions()->first();
        if (!$res) return false;
        
        if ($res->car_required) {
            $capa = $this->complaint->capaRecord;
            if (!$capa) return false;

            // Strict check for Final Verification (Step 4)
            return !empty($capa->effectiveness_verified_by) && !empty($capa->effectiveness_date);
        } else {
            // No-CAR Path
            return !empty($res->findings) && !empty($res->action_taken);
        }
    }

    public function getIsStageTwoReadyForApprovalProperty(): bool
    {
        // 1. Classification Decision must be recorded
        if (!$this->isInvestigationCheckpointCompleted) {
            return false;
        }

        $res = $this->complaint->resolutions()->where('complaint_id', $this->complaint->id)->first();
        if (!$res) return false;

        // 2. Base Investigation - Cause/Nature determines the root
        if (empty(trim(strip_tags((string)$res->cause_of_complaint)))) {
            return false;
        }

        if ($res->car_required) {
            // 3. CAPA path requirements
            $capa = $this->complaint->capaRecord;
            if (!$capa) return false;

            // NCR details if NC was raised
            if ($res->ncr_required && empty(trim(strip_tags((string)$capa->details_of_non_conformance)))) {
                return false;
            }

            // Root Cause & Corrective Action in CAPA
            $whys = $capa->why_why_analysis ?? [];
            if (is_string($whys)) $whys = json_decode($whys, true) ?? [];

            $rootCause = !empty($res->root_cause_analysis) ? $res->root_cause_analysis : ($capa->root_cause ?: ($whys['root_cause_analysis'] ?? ''));
            if (empty(trim(strip_tags((string)$rootCause)))) return false;

            // Immediate Action Taken
            if (empty(trim(strip_tags((string)$res->action_taken)))) return false;

            // Corrective Action
            $correctiveAction = !empty($res->corrective_action_taken) ? $res->corrective_action_taken : ($whys['corrective_action_taken'] ?? ($whys['corrective_action'] ?? ''));
            if (empty(trim(strip_tags((string)$correctiveAction)))) return false;

            // Verification (Final Step of CAPA)
            if (empty($capa->effectiveness_verified_by) || empty($capa->effectiveness_date)) {
                return false;
            }
        } else {
            // 4. No-CAR Path requirements
            if (empty(trim(strip_tags((string)$res->action_taken)))) return false;
            if (empty(trim(strip_tags((string)$res->corrective_action_taken)))) return false;
        }

        return true;
    }

    public function getBreadcrumbItemsProperty()
    {
        return [
            [
                'link' => route('customers-list'),
                'name' => 'CRM',
                'icon' => null
            ],
            [
                'link' => route('complaint-workflow', ['stage' => $this->initialWorkflowName]),
                'name' => $this->initialWorkflowName,
                'icon' => null
            ],
            [
                'link' => '#',
                'name' => $this->complaint->complaint_id,
                'icon' => null
            ]
        ];
    }

    

    public function generateReport($id)
    {
        // 1. Fetch Complaint with all necessary relationships
        // 1. Fetch Complaint with all necessary relationships
        $complaint = Complaint::with(['client', 'resolutions'])->findOrFail($id);

        // 1.1 Fetch Chain of Custody (exclude report generation actions - not meaningful workflow steps)
        // Use created_at asc for chronological display (consistent with ComplaintClosureMail & ComplaintWorkflowTab)
        $chainOfCustody = \App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $id)
            ->whereNotIn('action', ['Closure Report Regenerated'])
            ->orderBy('created_at', 'asc')
            ->get();

        // 1.2 Fetch Public Notes and Attachments
        // Only include items explicitly marked as public, excluding auto-generated closure reports
        $publicNotes = $complaint->notes()
            ->where('is_public', 1)
            ->where('is_delete', '!=', 1)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $publicAttachments = $complaint->attachments()
            ->where('is_public', 1)
            ->where('is_delete', '!=', 1)
            ->where(function($query) {
                // Exclude attachments where type or title is 'Closure Report'
                // (type != X AND title != X) excludes rows where either field equals X
                $query->where('type', '!=', 'Closure Report')
                      ->where('title', '!=', 'Closure Report');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Generate PDF (Standardizing on Investigation Report)
        $resolution = $complaint->resolutions()->latest()->first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.investigation_report', compact('complaint', 'resolution', 'chainOfCustody'));
        
        if (function_exists('addClosureReportPageNumbers')) {
            \addClosureReportPageNumbers($pdf);
        }

        // 3. Return Stream Download
        $safeId = str_replace(['/', '\\'], '-', $complaint->complaint_id);
        return response()->streamDownload(fn () => print($pdf->output()), 'Investigation_Report_' . $safeId . '.pdf');
    }

    #[On('email-complaint-report')]
    public function emailReport($id)
    {
        $complaint = Complaint::with(['client', 'resolutions'])->findOrFail($id);
        try {
            $emails = app(ComplaintInvestigationReportService::class)
                ->sendToComplaintClient($complaint);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Customer has no valid email address.';
            $this->dispatch('alert', ['type' => 'error', 'message' => $message]);
            return;
        } catch (\Throwable $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $chain = new \App\Models\CRM\Chain_of_Custody_Complaint();
        $chain->complaint_id = $complaint->id;
        $chain->action = 'Investigation report emailed manually to customer.';
        $chain->action_taker_id = \Illuminate\Support\Facades\Auth::id();
        $chain->workflow_stage = $this->workflowStage;
        $chain->comments = 'Sent to: ' . implode(', ', $emails);
        $chain->move_out_date = getTodayDate() ?? now();
        $chain->save();

        $this->dispatch('attachment-added');
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Report sent successfully to ' . implode(', ', $emails)]);
    }

    public function downloadInvestigationReport()
    {
        $this->checkPermission(CrmConstants::PERMISSION_COMPLAINT_VIEW);
        
        $complaint = Complaint::with(['client'])->find($this->complaint->id);
        $resolution = Complaintsresolutions::where('complaint_id', $this->complaint->id)->first() ?? new Complaintsresolutions();
        $chainOfCustody = Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)->with('actionTaker')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.investigation_report', compact('complaint', 'resolution', 'chainOfCustody'));
        
        $safeId = str_replace(['/', '\\'], '-', $complaint->complaint_id);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Investigation_Report_' . $safeId . '.pdf');
    }

    public function downloadCapaReport()
    {
        $this->checkPermission(CrmConstants::PERMISSION_COMPLAINT_VIEW);
        
        $resolution = Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if (!$resolution) return;

        $capaRecord = CapaRecord::where('complaint_id', $this->complaint->id)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.capa_report', [
            'complaint' => $this->complaint,
            'resolution' => $resolution,
            'capaRecord' => $capaRecord
        ]);

        $safeCarNo = str_replace(['/', '\\'], '-', $resolution->car_no ?: 'CAR-PENDING');
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'CAPA_Report_' . $safeCarNo . '.pdf');
    }

    public function downloadNcrReport()
    {
        $this->checkPermission(CrmConstants::PERMISSION_COMPLAINT_VIEW);
        
        $capaRecord = CapaRecord::where('complaint_id', $this->complaint->id)->first();
        if (!$capaRecord) return;

        $resolution = Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.ncr_report', [
            'complaint' => $this->complaint,
            'resolution' => $resolution,
            'capaRecord' => $capaRecord
        ]);

        $safeId = str_replace(['/', '\\'], '-', ($capaRecord->lab_no ?: $this->complaint->complaint_id));
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'NCR_Report_' . $safeId . '.pdf');
    }

    public function render()
    {
        return view('livewire.crm.complaint.complaint-show', [
            'complaint' => $this->complaint,
            'workflowStage' => $this->workflowStage,
            'initialComplaintWorkflow' => $this->initialComplaintWorkflow,
            'initialWorkflowName' => $this->initialWorkflowName,
            'attachmentsCount' => $this->attachmentsCount,
        ])->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => true])
            ->section('content2');
    }
}
