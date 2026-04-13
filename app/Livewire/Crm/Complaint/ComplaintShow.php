<?php

namespace App\Livewire\Crm\Complaint;

use App\Models\CRM\Complaint;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;

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

        if ($this->activeTab === 'details' && !request()->has('tab')) {
            $this->activeTab = match((int) $this->complaint->complaint_workflow) {
                1 => 'details',
                2 => 'investigation',
                3 => 'capa',
                4 => 'closure',
                default => 'details',
            };
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
    public function handleWorkflowUpdate($message = 'Task completed.')
    {
        $this->complaint->refresh();
        $this->calculateWorkflowStage();
        
        // Use alert dispatch for consistent UI feedback without redirection
        $this->dispatch('alert', ['type' => 'success', 'message' => $message]);
        
        // Remove redirection to keep user on the same page
        // return redirect()->route('complaint-workflow', ['stage' => $this->workflowStage]);
    }

    #[On('resolution-saved')]
    public function refreshComplaint()
    {
        $this->complaint->refresh();
        $this->complaint->load('resolutions');
        $this->calculateWorkflowStage();
    }

    public function switchTab($tab)
    {
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

    public function getBreadcrumbItemsProperty()
    {
        return [
            [
                'link' => route('customers-list'),
                'name' => 'CRM',
                'icon' => null
            ],
            [
                'link' => route('complaint-workflow', ['stage' => $this->workflowStage]),
                'name' => $this->workflowStage,
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

        // 2. Generate PDF (closure_report uses getActiveCompany() for logo/address from database)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.closure_report', compact('complaint', 'chainOfCustody', 'publicNotes', 'publicAttachments'));
        \addClosureReportPageNumbers($pdf);

        // 3. Return Stream Download
        $safeId = str_replace(['/', '\\'], '-', $complaint->complaint_id);
        return response()->streamDownload(fn () => print($pdf->output()), 'Complaint_' . $safeId . '_Closure_Report.pdf');
    }

    #[On('email-complaint-report')]
    public function emailReport($id)
    {
        // 1. Fetch Complaint
        // 1. Fetch Complaint
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

        // 2. Generate PDF Content (closure_report uses getActiveCompany() for logo/address from database)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.closure_report', compact('complaint', 'chainOfCustody', 'publicNotes', 'publicAttachments'));
        \addClosureReportPageNumbers($pdf);
        $pdfContent = $pdf->output();

        // 3. Validate Email
        if (empty($complaint->client->email)) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Customer has no email address.']);
            return;
        }

        // 4. Send Email
        // Note: ComplaintClosureMail will be created in Step 4
        \Illuminate\Support\Facades\Mail::to($complaint->client->email)
            ->send(new \App\Mail\ComplaintClosureMail($complaint, $pdfContent));

        // 5. Feedback
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Report sent successfully to ' . $complaint->client->email]);
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