<?php

namespace App\Livewire\Batch;

use App\Livewire\Batch\Concerns\InteractsWithCaseFileReviewForm;
use App\SampleHeader;
use App\CapturedResult;
use App\Services\Sampleworkflow\BatchVerificationReadinessService;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use App\Services\WorkflowService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Header extends Component
{
    use InteractsWithCaseFileReviewForm;
    public SampleHeader $batch;
    public $workflows = [];
    public $workflowstages = [];
    public $status;
    public $defaultClient;
    public $clientPortal;
    public $notCaptured;

    // Data Properties
    public $contacts = [];
    public $users = [];
    public $standards = [];
    public $allSamples = [];
    public $sectionApprovers = [];
    public $labStores = [];
    public $storeSlots = [];

    // Modal State
    public $showSendScheduleModal = false;
    public $showPaymentReminderModal = false;
    public $showBulkUpdateModal = false;
    public $showVerificationModal = false;
    public $showApprovalModal = false;
    public $showCaseFileModal = false;
    public $showChecklistRequiredModal = false;
    public $checklistRequiredMessage = '';
    public $checklistStageName = '';
    
    public $verificationActiveTab = 'assign_approvers';

    // Form Data
    public $selectedContact;
    public $emailBody;
    public $approvalData = [
        'user_id' => '',
        'title' => 'Authorized by',
        'comments' => '',
        'notification' => false,
        'send_message' => false,
    ];
    public $bulkData = [
        'main_standard' => '',
        'secondary_standard' => '',
        'disposal_date' => '',
        'store_id' => '',
        'store_slot_id' => '',
    ];
    public $selectedSamples = []; // For bulk update
    public $verificationData = [ // for verification modal
        'approver_user' => [], // [section_id => user_id]
        'title' => [], // [section_id => title]
        'level' => 0,
        'has_method_deviation' => false,
        'method_deviation_reason' => '',
    ];
    public $verificationSections = [];
    public $caseFormData = [];

    protected $listeners = ['batchUpdated' => '$refresh'];

    public function mount(SampleHeader $batch, $workflows = [], $workflowstages = [], $status = null, $defaultClient = false, $clientPortal = false)
    {
        $this->batch = $batch;
        $this->workflows = $workflows;
        $this->workflowstages = $workflowstages;
        $this->status = $status;
        $this->defaultClient = $defaultClient;
        $this->clientPortal = $clientPortal;

        // Get uncaptured results for this batch
        $this->notCaptured = CapturedResult::whereNull('result')
            ->where('sample_header_id', $batch->id)
            ->get();

        // Load specific data needed for actions
        $this->loadActionData();

        $this->verificationActiveTab = 'assign_approvers';
    }

    public function loadActionData()
    {
        if (isset($this->batch->id)) {
            // Contacts
            $this->contacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $this->batch->crm_customer_id)->get();

            // Standards
            if ($this->batch->is_qc_batch) {
                $this->standards = \App\Standards::where('status', 1)->where('qc_type_id', $this->batch->qc_type_id)->get();
            } else {
                $this->standards = \App\Standards::where('status', 1)->get();
            }

            // All Samples (for bulk update)
            $this->allSamples = $this->batch->all_samples();
            if ($this->allSamples instanceof \Illuminate\Support\Collection) {
                $this->selectedSamples = $this->allSamples->pluck('id')->map(fn($id) => (string)$id)->toArray();
            } else {
                // Fallback if not collection (though it likely is)
                $this->selectedSamples = collect($this->allSamples)->pluck('id')->map(fn($id) => (string)$id)->toArray();
            }

            // Users (for verification)
            $this->users = \App\User::where('is_client', 0)
                ->whereNull('supplier_id')
                ->where('active', 1)
                ->orderBy('name')
                ->get();

            $this->prepareVerificationForm();

            // Lab Stores
            $this->labStores = getStorageByType('lab_store');

            $this->initializeCaseFileFormData();
        }
    }

    public function updatedBulkDataStoreId($value)
    {
        $this->bulkData['store_slot_id'] = '';
        $this->storeSlots = [];

        if (!empty($value)) {
            // Find the selected store in labStores array
            foreach ($this->labStores as $store) {
                if ($store['id'] == $value) {
                    $this->storeSlots = $store['items'];
                    break;
                }
            }
        }
    }

    public function sendScheduleAnalysis()
    {
        $this->validate([
            'selectedContact' => 'required',
        ]);

        if ($this->batch->samples()->count() == 0) {
            session()->flash('error', 'You cannot send schedule of analysis for a batch with no sample');
            return;
        }

        $contact = \App\Models\CRM\CustomerContact::find($this->selectedContact);

        if (!$contact || empty($contact->email)) {
            session()->flash('error', 'Selected contact does not have a valid email address.');
            return;
        }

        $samples = \App\SampleDetails::where('sample_header_id', $this->batch->id)->get();
        $sampleTrs = "";

        foreach ($samples as $sample) {
            $target_date = date('Y-m-d', strtotime($sample->targetDateRelation()));
            $sampleTrs .= '
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($sample->sample_code) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars(implode(',', $sample->analyteNames())) . '</td>
                <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($target_date) . '</td>  
            </tr>';
        }

        $body = '
            <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
            <p style="font-size: 12px;">
                Dear ' . $this->batch->customer->name . ', <br><br>
                I hope this message finds you well. <br>
                We are pleased to confirm that your samples <b>' . strtoupper($this->batch->sample_type->name) . '</b> have been successfully received and assigned following Ref IDs: 
            </p>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tr>
                    <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Sample Reference No</th>
                    <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Test(s) Required</th>
                    <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ddd;">Expected Results Date</th>
                </tr>
                ' . $sampleTrs . '
            </table>

            <p style="font-size: 12px; margin-top: 15px;">
                <br>
                Once analysis is completed, you will receive an update regarding your test results.<br>
                For any inquiries, please contact us on <b>fivet.co.ke, /+12345678 </b>. <br>
                Thank you for the opportunity to serve you. <br><br>
                Kind regards, <br>
                FIVET COMPANY LIMITED
            </p>
        </div>
            ';

        notify_user($body, $contact->email, '[FIVET] Confirmation of Sample Receipt and Schedule of Analysis ' . $this->batch->batch_code, false, true, ['dannyagah13@gmail.com']);

        $this->batch->schedule_sent = 1;
        $this->batch->schedule_analysis_sent = date('Y-m-d');
        $this->batch->schedule_analysis_sender = auth()->user()->id;
        $this->batch->save();

        $schedule_str = 'Schedule Of Analysis Sendoff';
        $schedueDate = \App\SampleDate::where('sample_header_id', $this->batch->id)->where('name', $schedule_str)->first() ?? new \App\SampleDate();
        $schedueDate->name = $schedule_str;
        $schedueDate->sample_header_id = $this->batch->id;
        $schedueDate->date = date('Y-m-d');
        $schedueDate->save();

        $this->showSendScheduleModal = false;
        session()->flash('success', 'Schedule of analysis sent out successfully');
        $this->dispatch('batchUpdated');
    }

    public function sendPaymentReminder()
    {
        $this->validate([
            'selectedContact' => 'required',
            'emailBody' => 'required',
        ]);

        $contact = \App\Models\CRM\CustomerContact::find($this->selectedContact);

        if (!$contact || empty($contact->email)) {
            session()->flash('error', 'Selected contact does not have a valid email address.');
            return;
        }

        notify_user($this->emailBody, $contact->email, '[FIVET LIMS] Payment Reminder ' . $this->batch->batch_code);

        $this->showPaymentReminderModal = false;
        session()->flash('success', 'Payment reminder sent out successfully');
    }

    public function updatedSelectedContact($value)
    {
        // When contact is selected for payment reminder, we might want to pre-fill body if empty?
        // The legacy used `getPaymentReminderBody($customer->name)`. 
        // I should check if I need to implement that. 
        if ($this->showPaymentReminderModal && empty($this->emailBody)) {
            // Assuming this helper exists as per legacy view
            // {!! getPaymentReminderBody($customer->name) !!}
            if (function_exists('getPaymentReminderBody')) {
                $this->emailBody = getPaymentReminderBody($this->batch->customer->name);
            }
        }
    }

    public function bulkUpdateSampleData()
    {
        $this->validate([
            'selectedSamples' => 'required|array|min:1',
            // 'selectedSamples.*' => 'exists:sample_details,id', // Livewire validation might be tricky with array keys/values depending on how it's bound
        ]);

        try {
            // Build update data array - only include non-empty values
            $updateData = [];

            if (!empty($this->bulkData['main_standard'])) {
                $updateData['main_standard'] = $this->bulkData['main_standard'];
            }

            if (!empty($this->bulkData['secondary_standard'])) {
                $updateData['secondary_standard'] = $this->bulkData['secondary_standard'];
            }

            // Store/Slot logic if I add those fields to the modal. 
            // Legacy has store_id and store_slot_id but my proposed implementation plan didn't explicitly separate them in bulkData init.
            // I'll stick to what I have or add them if needed. 
            // For now, let's include disposal_date as per plan.

            if (!empty($this->bulkData['disposal_date'])) {
                $updateData['disposal_date'] = $this->bulkData['disposal_date'];
            }

            if (!empty($this->bulkData['store_id'])) {
                $updateData['store_id'] = $this->bulkData['store_id'];
            }

            if (!empty($this->bulkData['store_slot_id'])) {
                $updateData['store_slot_id'] = $this->bulkData['store_slot_id'];
            }

            // Only proceed if there's data to update
            if (empty($updateData)) {
                session()->flash('error', 'No fields were provided for update.');
                return;
            }

            // Update selected samples
            \App\SampleDetails::whereIn('id', $this->selectedSamples)
                ->where('sample_header_id', $this->batch->id)
                ->update($updateData);

            $updatedCount = count($this->selectedSamples);

            // Recalculate Batch Target Date
            $this->recalculateBatchTargetDate();

            $updatedFields = implode(', ', array_keys($updateData));

            session()->flash('success', "Successfully updated {$updatedCount} sample(s). Updated fields: {$updatedFields}");
            $this->showBulkUpdateModal = false;
            $this->dispatch('batchUpdated');
            // Reset bulk data
            $this->bulkData = [
                'main_standard' => '',
                'secondary_standard' => '',
                'disposal_date' => '',
                'store_id' => '',
                'store_slot_id' => '',
            ];
            $this->selectedSamples = [];
        } catch (\Exception $e) {
            \Log::error('Bulk update sample data error', [
                'error' => $e->getMessage(),
                'data' => $this->bulkData
            ]);

            session()->flash('error', 'Error updating samples: ' . $e->getMessage());
        }
    }

    public function recalculateBatchTargetDate()
    {
        $batchId = $this->batch->id;
        // Get all analysis types for this batch
        $batchAnalysisTypeIds = \App\SampleAnalysisTypeRelation::where('batch_id', $batchId)
            ->pluck('analysis_type_id')
            ->unique()
            ->toArray();

        if (!empty($batchAnalysisTypeIds)) {
            // Calculate max reporting time
            $analysisMaxReportingTime = \App\AnalysisType::whereIn('id', $batchAnalysisTypeIds)->max('reporting_time') ?? 0;
            $elementsMaxReportingTime = \App\AnalysisElements::whereIn('analysis_type_id', $batchAnalysisTypeIds)->max('reporting_time') ?? 0;

            $maxReportingTime = max($analysisMaxReportingTime, $elementsMaxReportingTime);

            // Update Target Date
            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $batchId)
                ->where('name', $targetDateStr)
                ->first() ?? new \App\SampleDate();

            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $batchId;
            // Use receipt_date or fallback to now
            $baseDate = $this->batch->receipt_date ? \Carbon\Carbon::parse($this->batch->receipt_date) : now();
            $targetDate->date = $baseDate->addDays($maxReportingTime);
            $targetDate->save();
        }
    }

    public function moveToVerification()
    {
        $batch = $this->batch;

        // Match legacy controller behaviour: remember the workflow column we came from
        $previousWorkflow = $batch->status;

        app(\App\Services\Sampleworkflow\SampleAnalysisSetupService::class)
            ->syncBatchLabSectionIdsFromAnalysisTypes($batch);
        $batch->refresh();
        $this->batch = $batch;

        $resultsBlockReason = app(BatchVerificationReadinessService::class)->blockingReason($batch);
        if ($resultsBlockReason !== null) {
            session()->flash('error', $resultsBlockReason);

            return;
        }

        if (empty($batch->lab_section_ids)) {
            session()->flash('error', 'Kindly provide the lab sections associated with the sample at batch information section');
            return;
        }

        $sectionIds = array_values(array_filter(array_map('trim', explode(',', (string) $batch->lab_section_ids))));
        $sectionUsers = \App\LabSectionApproverRelationShip::whereIn('lab_section_id', $sectionIds)->get();
        if ($sectionUsers->count() <= 0) {
            session()->flash('error', 'Kindly provide approval configuration for the selected batch lab sections');
            return;
        }

        // Only block when attachment-based placeholder results are still pending.
        // Procedure/grouped worksheets may set has_procedure_worksheet while posting
        // substantive values (e.g. Positive/Negative) that do not need a file attachment.
        $incompleteAttachmentResults = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('has_procedure_worksheet', true)
            ->get()
            ->filter(fn (CapturedResult $result) => $result->requiresLinkedBatchAttachment());

        if ($incompleteAttachmentResults->isNotEmpty()) {
            $count = $incompleteAttachmentResults->count();
            session()->flash(
                'error',
                $count . ' captured result' . ($count > 1 ? 's' : '') .
                    ' that require an attachment do not yet have a linked result attachment. ' .
                    'Please go to the Attachments tab, upload/link the result report(s), then try moving this batch to verification again.'
            );
            return;
        }

        $missingSections = [];
        foreach ($sectionIds as $sectionId) {
            if (empty($this->verificationData['approver_user'][$sectionId] ?? null)) {
                $missingSections[] = $sectionId;
            }
        }
        if ($missingSections !== []) {
            session()->flash('error', 'Assign a technical signatory for each lab section before submitting to verification.');
            return;
        }

        // Populate analysts based on Captured Results per section (2/polucon flow)
        $users = [];
        $userApprovers = [];
        foreach ($sectionIds as $sectionId) {
            $cUser = CapturedResult::where('lab_section_id', $sectionId)
                ->where('sample_header_id', $batch->id)
                ->orderBy('updated_at', 'DESC')
                ->first();

            if ($cUser && $cUser->operator_id) {
                $users[] = $cUser->operator_id;
                $userApprovers[$cUser->operator_id] = $sectionId;
            }
        }
        $analysts = \App\User::whereIn('id', array_unique($users))->get();

        \App\Models\Lab\TatCaptured::where('sample_header_id', $batch->id)->update(['is_complete' => 1]);

        $level = $this->verificationData['level'];
        $status = 'Sample Verification';

        if ($level == '0') {
            app(BatchWorkflowStageSyncService::class)->applyWorkflowStatus(
                $batch,
                $status,
                'Moved to Sample Verification from Samples In Lab.'
            );
        }
        $batch->report_status = ($level == '0') ? (int) $level : $batch->report_status;
        $batch->prelim_report_status = ($level != '0') ? (int) $level : $batch->prelim_report_status;
        $batch->prelim_batch_status = ($level != '0') ? $status : $batch->prelim_batch_status;

        $hasDeviation = (bool) ($this->verificationData['has_method_deviation'] ?? false);
        $batch->has_method_deviation = $hasDeviation;
        $batch->method_deviation_reason = $hasDeviation
            ? ($this->verificationData['method_deviation_reason'] ?? '')
            : null;

        if ($level == '0') {
            $batch->report_status = null;
            $batch->prelim_report_status = 0;
            $batch->prelim_batch_status = null;
        }

        if ($level != '2') {
            $level != 0
                ? \App\BatchLabSectionApprover::where('batch_id', $batch->id)->delete()
                : \App\BatchLabSectionApprover::where('batch_id', $batch->id)->where('is_prelim', 0)->delete();

            foreach ($this->verificationData['approver_user'] as $sectionId => $userId) {
                if (empty($userId) || ! in_array((string) $sectionId, array_map('strval', $sectionIds), true)) {
                    continue;
                }

                $approvers = \App\BatchLabSectionApprover::where('batch_id', $batch->id)
                    ->where('user_id', $userId)
                    ->first() ?? new \App\BatchLabSectionApprover();

                $title = $this->verificationData['title'][$sectionId] ?? 'Technical Signatory';

                $approvers->status = 0;
                $approvers->user_id = $userId;
                $approvers->title = $title;
                $approvers->lab_section_ids = empty($approvers->lab_section_ids)
                    ? (string) $sectionId
                    : $approvers->lab_section_ids . ',' . $sectionId;
                $approvers->batch_id = $batch->id;
                $approvers->batch_status = $status;
                $approvers->is_prelim = ($level != '0') ? 1 : 0;
                $approvers->show_report = 1;
                $approvers->approver_order = 1;
                $approvers->is_technical_reviewer = 1;
                $approvers->approver_type = 'Technical Signatory';
                $approvers->can_send_back_to_lab = 0;
                $approvers->save();
            }

            foreach ($analysts as $analyst) {
                if (! isset($userApprovers[$analyst->id])) {
                    continue;
                }

                $sectionId = $userApprovers[$analyst->id];
                $approvers = new \App\BatchLabSectionApprover();
                $approvers->status = 1;
                $approvers->user_id = $analyst->id;
                $approvers->title = 'Analyst';
                $approvers->lab_section_ids = $sectionId;
                $approvers->batch_id = $batch->id;
                $approvers->batch_status = $status;
                $approvers->is_prelim = ($level != '0') ? 1 : 0;
                $approvers->approval_date = date('Y-m-d h:i:s a');
                $approvers->show_report = 0;
                $approvers->save();
            }
        }

        $batch->save();
        $this->batch->refresh();

        if ($status === 'Sample Verification') {
            $batch->set_date('Processing Date', date('Y-m-d'));
        }

        session()->flash('success', 'Batch move was successful');
        $this->showVerificationModal = false;
        $this->verificationActiveTab = 'assign_approvers';
        $this->dispatch('batchUpdated');

        return redirect()->route('sample-workflow', ['status' => $previousWorkflow]);
    }

    public function openVerificationModal()
    {
        $resultsBlockReason = app(BatchVerificationReadinessService::class)->blockingReason($this->batch);
        if ($resultsBlockReason !== null) {
            session()->flash('error', $resultsBlockReason);

            return;
        }

        $this->prepareVerificationForm();
        $this->verificationActiveTab = 'assign_approvers';
        $this->showVerificationModal = true;
    }

    public function getCanSendToVerificationProperty(): bool
    {
        return app(BatchVerificationReadinessService::class)->canMoveToVerification($this->batch);
    }

    public function getVerificationResultsBlockReasonProperty(): ?string
    {
        return app(BatchVerificationReadinessService::class)->blockingReason($this->batch);
    }

    /**
     * Prefill verification signatories from Verifier Configuration per batch lab section.
     */
    public function prepareVerificationForm(): void
    {
        if (! isset($this->batch->id)) {
            $this->sectionApprovers = collect();
            $this->verificationSections = collect();

            return;
        }

        app(\App\Services\Sampleworkflow\SampleAnalysisSetupService::class)
            ->syncBatchLabSectionIdsFromAnalysisTypes($this->batch);
        $this->batch->refresh();

        $sectionIds = array_values(array_filter(array_map('trim', explode(',', (string) $this->batch->lab_section_ids))));

        $this->sectionApprovers = \App\LabSectionApproverRelationShip::whereIn('lab_section_id', $sectionIds)->get();
        $this->verificationSections = \App\SampleAnalysisStage::whereIn('id', $sectionIds)
            ->orderBy('name')
            ->get();

        foreach ($sectionIds as $sectionId) {
            $prevApprover = \App\BatchLabSectionApprover::where('batch_id', $this->batch->id)
                ->where('batch_status', 'Sample Verification')
                ->where('show_report', 1)
                ->where('lab_section_ids', 'like', '%' . $sectionId . '%')
                ->orderByDesc('updated_at')
                ->first();

            if ($prevApprover) {
                if (empty($this->verificationData['approver_user'][$sectionId])) {
                    $this->verificationData['approver_user'][$sectionId] = $prevApprover->user_id;
                }
                if (empty($this->verificationData['title'][$sectionId])) {
                    $this->verificationData['title'][$sectionId] = $prevApprover->title;
                }

                continue;
            }

            $config = $this->sectionApprovers->firstWhere('lab_section_id', $sectionId);
            if ($config) {
                if (empty($this->verificationData['approver_user'][$sectionId])) {
                    $this->verificationData['approver_user'][$sectionId] = $config->user_id;
                }
                if (empty($this->verificationData['title'][$sectionId])) {
                    $this->verificationData['title'][$sectionId] = $config->title ?: 'Technical Signatory';
                }
            } elseif (empty($this->verificationData['title'][$sectionId])) {
                $this->verificationData['title'][$sectionId] = 'Technical Signatory';
            }
        }
    }

    /**
     * Users configured as verifiers for a lab section (falls back to all active users).
     */
    public function getSectionVerifierUsers($sectionId)
    {
        $userIds = $this->sectionApprovers instanceof \Illuminate\Support\Collection
            ? $this->sectionApprovers->where('lab_section_id', $sectionId)->pluck('user_id')->filter()->unique()->all()
            : collect($this->sectionApprovers)->where('lab_section_id', $sectionId)->pluck('user_id')->filter()->unique()->all();

        if ($userIds === []) {
            return $this->users;
        }

        return \App\User::whereIn('id', $userIds)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function openApprovalModal()
    {
        $batch = $this->batch;

        if ($batch->status === 'Sample Verification') {
            try {
                app(WorkflowService::class)->assertStageApprovalsCompleted((string) $batch->id, 'Sample Verification');
            } catch (ValidationException $exception) {
                $this->checklistRequiredMessage = $exception->validator->errors()->first()
                    ?: 'Complete and approve the checklist before sending this batch for approval.';
                $this->checklistStageName = 'Sample Verification';
                $this->showChecklistRequiredModal = true;
                return;
            }
        }

        $this->showApprovalModal = true;
    }

    public function closeChecklistRequiredModal()
    {
        $this->showChecklistRequiredModal = false;
        $this->checklistStageName = '';
    }

    public function sendForApproval()
    {
        $this->validate([
            'approvalData.user_id' => 'required',
            'approvalData.title' => 'required',
        ]);

        $batch = $this->batch;
        $previousWorkflow = $this->status ?: $batch->status;

        // Lab section check removed per user request
        
        // Check if user is already an approver in the Sample Approval stage
        $approvers_user_ids = \App\BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Approval')
            ->pluck('user_id')
            ->toArray();

        if (in_array($this->approvalData['user_id'], $approvers_user_ids)) {
            session()->flash('approval_error', 'System cannot assign the specified user as an approver since the user is already an approver in this stage');
            return;
        }

        // Delete any existing approvers with lab_section_ids = 0 (general approvers)
        \App\BatchLabSectionApprover::where('batch_id', $batch->id)
            ->where('lab_section_ids', 0)
            ->delete();

        // Create new approver
        $approver = new \App\BatchLabSectionApprover();
        $approver->status = 0; // Pending approval
        $approver->user_id = $this->approvalData['user_id'];
        $approver->title = $this->approvalData['title'];
        $approver->lab_section_ids = 0; // General approver (not section-specific)
        $approver->batch_id = $batch->id;
        $approver->batch_status = 'Sample Approval';
        $approver->show_report = 1;
        $approver->save();

        // Update batch status, tracking stage, and chain of custody
        app(BatchWorkflowStageSyncService::class)->applyWorkflowStatus(
            $batch,
            'Sample Approval',
            $this->approvalData['comments'] ?: 'Moved to Sample Approval from Sample Verification.'
        );
        $batch->save();
        $this->batch->refresh();

        // Send notifications if requested
        $user = \App\User::find($this->approvalData['user_id']);

        if ($this->approvalData['notification'] && $user && $user->email) {
            $message = 'Hi ' . $user->name . ', <br>' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. <br> Comments : ' . ($this->approvalData['comments'] ?? '');
            notify_user($message, $user->email, '[FIVET LIMS] ' . $batch->batch_code . ' Batch Approval Notification');
        }

        if ($this->approvalData['send_message'] && $user && $user->phone) {
            $sms_message = 'Hi ' . $user->name . ', ' . $batch->batch_code . ' COA needs your approval at ' . $batch->status . '. Comments : ' . ($this->approvalData['comments'] ?? '');
            if (function_exists('sendTextMessage')) {
                sendTextMessage($user->phone, $sms_message);
            }
        }

        // Reset form
        $this->approvalData = [
            'user_id' => '',
            'title' => 'Authorized by',
            'comments' => '',
            'notification' => false,
            'send_message' => false,
        ];

        $this->showApprovalModal = false;
        session()->flash('success', 'Batch sent for approval successfully');

        return redirect()->route('sample-workflow', ['status' => $previousWorkflow]);
    }

    public function getBatchLabs()
    {
        if (!isset($this->batch->id)) {
            return collect();
        }

        $labIds = [];
        
        // 1. From sample_details.lab_id
        $directLabIds = \App\SampleDetails::where('sample_header_id', $this->batch->id)
            ->whereNotNull('lab_id')
            ->pluck('lab_id')
            ->toArray();
        $labIds = array_merge($labIds, $directLabIds);
        
        // 2. From analysis types of the samples
        $samples = \App\SampleDetails::where('sample_header_id', $this->batch->id)->get();
        foreach ($samples as $sample) {
            $analysisIDs = array_filter(explode(",", (string)$sample->analysis_type_id));
            if (!empty($analysisIDs)) {
                $typeLabIds = \App\AnalysisType::whereIn('id', $analysisIDs)
                    ->whereNotNull('lab_id')
                    ->pluck('lab_id')
                    ->toArray();
                $labIds = array_merge($labIds, $typeLabIds);
            }
        }
        
        $uniqueLabIds = array_unique(array_filter($labIds));
        if (empty($uniqueLabIds)) {
            return collect();
        }
        
        return \App\Lab::whereIn('id', $uniqueLabIds)->get();
    }

    public function getLabManagersForLab($labId)
    {
        $lab = \App\Lab::find($labId);
        if (!$lab) {
            return collect();
        }
        
        // Get users associated with this lab in user_lab_relation
        $associatedUserIds = \DB::table('user_lab_relation')
            ->where('lab_id', $labId)
            ->pluck('user_id')
            ->toArray();
            
        if ($lab->manager_id) {
            $associatedUserIds[] = $lab->manager_id;
        }
        
        $associatedUserIds = array_unique(array_filter($associatedUserIds));
        
        if (empty($associatedUserIds)) {
            return collect();
        }
        
        $users = \App\User::whereIn('id', $associatedUserIds)
            ->where('active', 1)
            ->get();
            
        return $users->filter(function($u) use ($lab) {
            return $u->hasRole('Lab Manager') || $u->id === $lab->manager_id;
        });
    }

    public function getAvailableApprovalUsersProperty()
    {
        $labs = $this->getBatchLabs();
        $userIds = [];
        foreach ($labs as $lab) {
            $managers = $this->getLabManagersForLab($lab->id);
            $userIds = array_merge($userIds, $managers->pluck('id')->toArray());
        }
        $userIds = array_unique(array_filter($userIds));
        
        if (empty($userIds)) {
            return collect();
        }
        
        return \App\User::whereIn('id', $userIds)->get();
    }

    public function getApproversUserIdsProperty()
    {
        return \App\BatchLabSectionApprover::where('batch_id', $this->batch->id)
            ->pluck('user_id')
            ->toArray();
    }

    public function getVerificationApprovalStatusProperty()
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            return \App\BatchLabSectionApprover::where('batch_id', $this->batch->id)
                ->where('batch_status', 'Sample Verification')
                ->where(function($q) {
                    $q->where('status', false)->orWhereNull('status');
                })
                ->count();
        }
        return \App\BatchLabSectionApprover::where('batch_id', $this->batch->id)
            ->where('batch_status', 'Sample Verification')
            ->whereIn('status', [2, 0])
            ->count();
    }

    public function saveStandaloneCaseFile()
    {
        if (!$this->batch->hasDnaLab()) {
            session()->flash('error', 'Case File Review Form is only available for DNA laboratories.');
            return;
        }

        $this->validate([
            'caseFormData.lab_no' => 'required',
        ]);

        $data = $this->caseFormData;
        $data['batch_id'] = $this->batch->id;
        
        $caseFile = \App\Models\CaseFileReviewForm::updateOrCreate(
            ['batch_id' => $this->batch->id],
            $data
        );

        $this->showCaseFileModal = false;
        
        $this->dispatch('batchUpdated');
        
        $this->dispatch('open-new-tab', url: route('view-case-file-pdf', $caseFile->id));
    }

    protected function assignCaseFileFormArray(array $data): void
    {
        $this->caseFormData = $data;
    }

    protected function caseFileFormArray(): array
    {
        return $this->caseFormData;
    }
}

