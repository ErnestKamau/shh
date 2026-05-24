<?php

namespace App\Livewire\Batch;

use App\SampleHeader;
use App\CapturedResult;
use App\Services\WorkflowService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Header extends Component
{
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
        'technical_reviewer_id' => '',
    ];

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

            // Section Approvers (for verification modal)
            $this->sectionApprovers = \App\LabSectionApproverRelationShip::whereIn('lab_section_id', array_filter(explode(',', (string)$this->batch->lab_section_ids)))->get();

            // Pre-populate verificationData with labs instead of sections
            $labs = $this->getBatchLabs();
            foreach ($labs as $lab) {
                $prevApprover = \App\BatchLabSectionApprover::where('batch_id', $this->batch->id)
                    ->where('approver_order', 2)
                    ->whereRaw("FIND_IN_SET(?, lab_section_ids) > 0", [$lab->id])
                    ->first();

                if ($prevApprover) {
                    if (empty($this->verificationData['approver_user'][$lab->id])) {
                        $this->verificationData['approver_user'][$lab->id] = $prevApprover->user_id;
                    }
                    if (empty($this->verificationData['title'][$lab->id])) {
                        $this->verificationData['title'][$lab->id] = $prevApprover->title;
                    }
                } else {
                    $managers = $this->getLabManagersForLab($lab->id);
                    $firstManager = $managers->first();
                    if ($firstManager) {
                        if (empty($this->verificationData['approver_user'][$lab->id])) {
                            $this->verificationData['approver_user'][$lab->id] = $firstManager->id;
                        }
                    }
                    if (empty($this->verificationData['title'][$lab->id])) {
                        $this->verificationData['title'][$lab->id] = 'Lab Manager';
                    }
                }
            }

            // Lab Stores
            $this->labStores = getStorageByType('lab_store');

            // Initialize technical reviewer if exists
            $this->verificationData['technical_reviewer_id'] = $this->batch->approve_user_id ?? '';
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

        $labs = $this->getBatchLabs();
        if ($labs->isEmpty()) {
            session()->flash('error', 'Kindly provide the laboratories associated with the samples at batch information section');
            return;
        }

        // We check if at least one laboratory has a manager or we have a manager assigned
        $hasManagers = false;
        foreach ($labs as $lab) {
            if ($this->getLabManagersForLab($lab->id)->isNotEmpty()) {
                $hasManagers = true;
            }
        }
        if (!$hasManagers) {
            session()->flash('error', 'Kindly provide Lab Manager configuration for the batch laboratories');
            return;
        }

        // Ensure all attachment-based results (those that expect a procedure worksheet)
        // are truly linked to an attachment before moving on in the workflow.
        $incompleteAttachmentResults = CapturedResult::where('sample_header_id', $batch->id)
            ->where('has_procedure_worksheet', true)
            ->where(function ($query) {
                $query->whereNull('batch_attachment_id')
                    ->orWhereDoesntHave('batchAttachment');
            })
            ->get();

        if ($incompleteAttachmentResults->isNotEmpty()) {
            $count = $incompleteAttachmentResults->count();
            session()->flash(
                'error',
                $count . ' captured result' . ($count > 1 ? 's are' : ' is') .
                    ' that require an attachment do not yet have a linked result attachment. ' .
                    'Please go to the Attachments tab, upload/link the result report(s), then try moving this batch to verification again.'
            );
            return;
        }

        // Populate analysts based on Captured Results logic
        // captured_results has lab_section_id (not lab_id), so resolve sections via SampleAnalysisStage
        $users = [];
        $user_approvers = [];
        foreach ($labs as $lab) {
            $sectionIds = \App\SampleAnalysisStage::where('lab_id', $lab->id)->pluck('id')->toArray();
            if (empty($sectionIds)) {
                continue;
            }
            $c_user = CapturedResult::whereIn('lab_section_id', $sectionIds)
                ->where('sample_header_id', $batch->id)
                ->orderBy('updated_at', 'DESC')
                ->first();

            if ($c_user && $c_user->operator_id) {
                array_push($users, $c_user->operator_id);
                $user_approvers[$c_user->operator_id] = $lab->id;
            }
        }
        $analysts = \App\User::whereIn('id', array_unique($users))->get();

        // Perform updates
        \App\Models\Lab\TatCaptured::where('sample_header_id', $batch->id)->update(['is_complete' => 1]);

        $level = $this->verificationData['level'];
        $status = 'Sample Verification'; // Hardcoded as per form input hidden value in legacy

        // Status Logic
        $batch->status = ($level == '0') ? $status : $batch->status;
        $batch->report_status = ($level == '0') ? (int)$level : $batch->report_status;
        $batch->prelim_report_status = ($level != '0') ? (int)$level : $batch->prelim_report_status;
        $batch->prelim_batch_status = ($level != '0') ? $status : $batch->prelim_batch_status;

        // Persist method deviation details at batch level when moving to verification
        $hasDeviation = (bool)($this->verificationData['has_method_deviation'] ?? false);
        $batch->has_method_deviation = $hasDeviation;
        $batch->method_deviation_reason = $hasDeviation
            ? ($this->verificationData['method_deviation_reason'] ?? '')
            : null;

        if (!empty($this->verificationData['technical_reviewer_id'])) {
            $batch->approve_user_id = $this->verificationData['technical_reviewer_id'];
        }

        if ($level == '0') {
            $batch->report_status = null;
            $batch->prelim_report_status = 0;
            $batch->prelim_batch_status = null;
        }

        if ($level != '2') {
            // Delete old approvers
            $level != 0
                ? \App\BatchLabSectionApprover::where('batch_id', $batch->id)->delete()
                : \App\BatchLabSectionApprover::where('batch_id', $batch->id)->where('is_prelim', 0)->delete();

            // Add Technical Reviewer
            if ($batch->approve_user_id) {
                $techApprover = new \App\BatchLabSectionApprover();
                $techApprover->status = 0;
                $techApprover->user_id = $batch->approve_user_id;
                $techApprover->title = 'Technical Signatory';
                $techApprover->lab_section_ids = '0'; // Global
                $techApprover->batch_id = $batch->id;
                $techApprover->batch_status = $status;
                $techApprover->is_prelim = ($level != '0') ? 1 : 0;
                $techApprover->show_report = 1;
                
                $techApprover->approver_order = 1;
                $techApprover->is_technical_reviewer = 1;
                $techApprover->approver_type = 'Technical Reviewer';
                $techApprover->can_send_back_to_lab = 0;
                $techApprover->save();
            } else {
                session()->flash('error', 'No Technical Signatory assigned to this batch. Please assign one first.');
                return;
            }

            // Add new approvers from Form Data
            foreach ($this->verificationData['approver_user'] as $lab_id => $user_id) {
                if (empty($user_id)) {
                    continue;
                }

                $approvers = \App\BatchLabSectionApprover::where('batch_id', $batch->id)
                    ->where('user_id', $user_id)
                    ->where('approver_order', 2)
                    ->first() ?? new \App\BatchLabSectionApprover();

                $title = $this->verificationData['title'][$lab_id] ?? 'Lab Manager';

                $approvers->status = 0;
                $approvers->user_id = $user_id;
                $approvers->title = $title;
                $approvers->lab_section_ids = ($approvers->lab_section_ids == '') ? $lab_id : $approvers->lab_section_ids . ',' . $lab_id;
                $approvers->batch_id = $batch->id;
                $approvers->batch_status = $status;
                $approvers->is_prelim = ($level != '0') ? 1 : 0;
                $approvers->show_report = 1;
                
                $approvers->approver_order = 2;
                $approvers->is_technical_reviewer = 0;
                $approvers->approver_type = 'Lab Manager';
                $approvers->can_send_back_to_lab = 1;
                $approvers->save();
            }

            // Add analysts who worked on it as approved/verifier
            foreach ($analysts as $analyst) {
                if (isset($user_approvers[$analyst->id])) {
                    $lab_id = $user_approvers[$analyst->id];

                    $approvers = new \App\BatchLabSectionApprover();
                    $approvers->status = 1; // Auto-approved?
                    $approvers->user_id = $analyst->id;
                    $approvers->title = 'Analyst';
                    $approvers->lab_section_ids = $lab_id;
                    $approvers->batch_id = $batch->id;
                    $approvers->batch_status = $status;
                    $approvers->is_prelim = ($level != '0') ? 1 : 0;
                    $approvers->approval_date = date('Y-m-d h:i:s a');
                    $approvers->show_report = 0;
                    $approvers->save();
                }
            }
        }

        $batch->save();

        session()->flash('success', 'Batch move was successful');
        $this->showVerificationModal = false;
        $this->dispatch('batchUpdated');

        // Redirect back to the workflow column we initiated from (legacy style)
        return redirect()->route('sample-workflow', ['status' => $previousWorkflow]);
    }

    public function sendForApproval()
    {
        $this->validate([
            'approvalData.user_id' => 'required',
            'approvalData.title' => 'required',
        ]);

        $batch = $this->batch;

        if ($batch->status === 'Sample Verification') {
            try {
                app(WorkflowService::class)->assertStageApprovalsCompleted((string) $batch->id, 'Sample Verification');
            } catch (ValidationException $exception) {
                $message = $exception->validator->errors()->first();
                $checklistUrl = route('sample-approval-checklist.show', [
                    'sample' => $batch->id,
                    'stage_name' => 'Sample Verification',
                ]);

                session()->flash('error', $message . ' Complete checklist here: ' . $checklistUrl);
                return;
            }
        }

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

        // Update batch status
        $batch->status = 'Sample Approval';
        $batch->save();

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
        $this->dispatch('batchUpdated');
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
}
