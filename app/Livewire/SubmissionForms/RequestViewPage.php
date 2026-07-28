<?php

namespace App\Livewire\SubmissionForms;

use App\ChainOfCustody;
use App\BatchAttachment;
use App\Livewire\Sampleworkflow\AcceptanceFormWizard;
use App\Livewire\Sampleworkflow\ProcessEnquiryWizard;
use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Livewire\Sampleworkflow\SampleRejectionWizard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceAttachment;
use App\Models\SubmissionFormInstanceNote;
use App\Services\SampleCreationService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use App\Services\SubmissionFormBatchSyncService;
use App\Services\Commercial\AmSpecTrfPdfService;
use App\Services\Commercial\EnquiryAccountSettingsService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\Planner\SamplingScheduleTrfSync;
use App\Services\SubmissionForm\RequestViewPagePresenter;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use App\Services\SubmissionForm\SubmissionFormInstanceNoteService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

class RequestViewPage extends Component
{
    use WithFileUploads;

    public $newAttachment;

    public bool $showAttachmentModal = false;

    public string $newAttachmentTitle = '';

    public string $newAttachmentType = '';

    public string $newCustomAttachmentType = '';

    public string $newAttachmentDescription = '';

    /** @var list<string> */
    public array $availableAttachmentTypes = [];

    /** @var list<string> */
    protected array $defaultAttachmentTypes = [
        'Permit',
        'Invoice',
        'Packing List',
        'Report',
        'Certificate',
        'Authorization Letter',
    ];

    public string $submissionFormId;

    public string $instanceId;

    public string $activeTab = 'tests';

    public string $noteBody = '';

    public string $noteVisibility = SubmissionFormInstanceNote::VISIBILITY_INTERNAL;

    public SubmissionFormInstance $instance;

    public SubmissionForm $submissionForm;

    public bool $linkedBatchesOutOfSyncWithForm = false;

    public ?SampleSubmissionRequest $commercialEnquiry = null;

    public ?string $trfPdfUrl = null;

    public bool $sendTrfEmail = true;

    public bool $showPoCaptureModal = false;

    public bool $showQuotationAcceptanceModal = false;

    public string $quotationAcceptanceSignerName = '';

    public string $quotationAcceptanceSignature = '';

    public ?string $quotationAcceptanceContactId = null;

    /** @var list<array{id: string, label: string}> */
    public array $quotationAcceptanceContactOptions = [];

    public string $clientPoNumber = '';

    public bool $poSkipped = false;

    public string $advancePaymentReference = '';

    public string $poRuleType = 'walk_in';

    public bool $poRequiresPo = false;

    public bool $poRequiresAdvanceReference = false;

    public bool $poAllowsSkip = true;

    public string $poRuleMessage = '';

    public function mount(
        string $submissionFormId,
        string $instanceId,
        SubmissionFormBatchSyncService $batchSyncService
    ): void {
        $this->submissionFormId = $submissionFormId;
        $this->instanceId = $instanceId;
        $this->newAttachments = [
            ['file' => null, 'type' => '', 'heading' => ''],
        ];

        $this->submissionForm = SubmissionForm::query()->findOrFail($submissionFormId);
        $this->instance = SubmissionFormInstance::query()
            ->with([
                'sampleSubmissionRequest.currentQuotation',
                'sampleSubmissionRequest.contact',
                'sampleSubmissionRequest.customer',
                'submissionForm',
                'submittedBy',
                'reviewedBy',
                'crmCustomer',
                'batches',
                'notes.author',
                'analysisAcceptanceForms',
                'attachmentInstances.submissionForm',
            ])
            ->where('submission_form_id', $submissionFormId)
            ->findOrFail($instanceId);

        $this->commercialEnquiry = $this->instance->sampleSubmissionRequest
            ?? SampleSubmissionRequest::query()
                ->with(['currentQuotation', 'contact', 'customer'])
                ->where('submission_form_instance_id', $this->instance->id)
                ->first();

        // Scheduled TRFs created before schedule→form hydration may have empty
        // collection/sample fields; fill only blank values from the linked schedule.
        if ($this->instance->sampling_schedule_id) {
            app(SamplingScheduleTrfSync::class)->fillEmptyInstanceValues($this->instance);
        }

        if ($this->instance->batches()->exists()) {
            $this->linkedBatchesOutOfSyncWithForm = $batchSyncService
                ->linkedBatchesOutOfSyncWithForm($this->instance);
        }

        $this->loadAvailableAttachmentTypes();
    }

    /**
     * Sample collection label is shown only on the Samples Receiving board
     * "Ready for Reception" tab (before physical check-in).
     */
    public function shouldShowSampleCollectionLabel(): bool
    {
        return $this->isInSamplesReceivingReadyForReceptionTab();
    }

    protected function isInSamplesReceivingReadyForReceptionTab(): bool
    {
        if (! in_array((string) $this->instance->status, ['submitted', 'Submitted'], true)) {
            return false;
        }

        return $this->commercialEnquiry !== null
            && $this->commercialEnquiry->status === SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION;
    }

    public function recordWalkInQuotationAcceptance(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        if (strtolower((string) ($this->commercialEnquiry->source_channel ?? '')) === 'portal') {
            session()->flash('request_view_message', 'Portal requests are accepted by the customer in the portal. Staff quotation approval applies to non-portal sources only.');

            return;
        }

        $enquiry = $this->commercialEnquiry->loadMissing(['contact']);
        $this->quotationAcceptanceSignerName = trim(implode(' ', array_filter([
            $enquiry->contact?->first_name,
            $enquiry->contact?->last_name,
        ])));
        $this->quotationAcceptanceSignature = '';
        $this->quotationAcceptanceContactId = $enquiry->crm_customer_contact_id
            ? (string) $enquiry->crm_customer_contact_id
            : null;
        $this->quotationAcceptanceContactOptions = app(\App\Services\Sampleworkflow\CustomerContactVerificationService::class)
            ->activeContactsForCustomer((string) $enquiry->crm_customer_id);
        $this->showQuotationAcceptanceModal = true;
        $this->dispatch('quotation-acceptance-modal-opened');
    }

    public function closeQuotationAcceptanceModal(): void
    {
        $this->showQuotationAcceptanceModal = false;
        $this->quotationAcceptanceSignerName = '';
        $this->quotationAcceptanceSignature = '';
        $this->quotationAcceptanceContactId = null;
        $this->quotationAcceptanceContactOptions = [];
    }

    public function submitQuotationAcceptanceSignature(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        $this->validate([
            'quotationAcceptanceSignerName' => ['required', 'string', 'max:255'],
            'quotationAcceptanceSignature' => ['required', 'string'],
        ], [
            'quotationAcceptanceSignerName.required' => 'Enter the customer signer name.',
            'quotationAcceptanceSignature.required' => 'Provide the customer signature.',
        ]);

        try {
            app(QuotationFromEnquiryService::class)->recordWalkInAcceptance(
                $this->commercialEnquiry,
                null,
                false,
                [
                    'signature' => $this->quotationAcceptanceSignature,
                    'signer_name' => $this->quotationAcceptanceSignerName,
                    'contact_id' => $this->quotationAcceptanceContactId,
                ],
            );
            $this->commercialEnquiry = $this->commercialEnquiry->fresh(['currentQuotation']);
            $this->closeQuotationAcceptanceModal();
            $this->openPoCaptureModal();
            session()->flash('request_view_message', 'Quotation accepted. Record the customer PO below to move this request to Ready for Reception.');
        } catch (\Throwable $exception) {
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    public function openPoCaptureModal(): void
    {
        if ($this->commercialEnquiry === null
            || $this->commercialEnquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED) {
            return;
        }

        $rules = app(EnquiryAccountSettingsService::class)
            ->poRulesForCustomer($this->commercialEnquiry->customer);
        $this->poRuleType = (string) ($rules['type'] ?? 'walk_in');
        $this->poRequiresPo = (bool) ($rules['requires_po'] ?? false);
        $this->poRequiresAdvanceReference = (bool) ($rules['requires_advance_reference'] ?? false);
        $this->poAllowsSkip = (bool) ($rules['allows_po_skip'] ?? true);
        $this->poRuleMessage = $this->resolvePoRuleMessage();
        $this->clientPoNumber = (string) ($this->commercialEnquiry->client_po_number ?? '');
        $this->poSkipped = (bool) ($this->commercialEnquiry->po_skipped && $this->poAllowsSkip);
        $this->advancePaymentReference = (string) ($this->commercialEnquiry->advance_payment_reference ?? '');
        $this->showPoCaptureModal = true;
    }

    public function closePoCaptureModal(): void
    {
        $this->showPoCaptureModal = false;
        $this->clientPoNumber = '';
        $this->poSkipped = false;
        $this->advancePaymentReference = '';
        $this->poRuleType = 'walk_in';
        $this->poRequiresPo = false;
        $this->poRequiresAdvanceReference = false;
        $this->poAllowsSkip = true;
        $this->poRuleMessage = '';
    }

    public function submitPoAndReadyForReception(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        try {
            app(EnquiryAccountSettingsService::class)->validateAcceptPayload(
                $this->commercialEnquiry->customer,
                [
                    'client_po_number' => $this->clientPoNumber,
                    'po_skipped' => $this->poSkipped,
                    'advance_payment_reference' => $this->advancePaymentReference,
                ],
            );

            $this->commercialEnquiry = app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $this->commercialEnquiry,
                (string) ($this->commercialEnquiry->accepted_quotation_header_id ?? $this->commercialEnquiry->current_quotation_header_id ?? ''),
                [
                    'client_po_number' => $this->clientPoNumber,
                    'po_skipped' => $this->poSkipped,
                    'advance_payment_reference' => $this->advancePaymentReference,
                ],
            );

            $this->closePoCaptureModal();
            session()->flash('request_view_message', 'PO recorded. This request is ready for physical reception on the Samples Receiving board.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
        } catch (\Throwable $exception) {
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    protected function resolvePoRuleMessage(): string
    {
        if ($this->poRequiresPo) {
            return 'This customer account requires a purchase order number before the request can be marked ready.';
        }

        if ($this->poRequiresAdvanceReference) {
            return 'This customer account requires an advance payment reference before the request can be marked ready.';
        }

        if ($this->poAllowsSkip) {
            return 'This customer may proceed without a PO number.';
        }

        return '';
    }

    public function openPhysicalReceiveModal(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null
            || $this->commercialEnquiry->status !== SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION) {
            session()->flash('request_view_message', 'Physical reception is only available after the quotation is accepted and PO is recorded.');

            return;
        }

        $label = (string) ($this->instance->getDocumentControlNumber() ?? $this->instance->form_number ?? $this->instance->id);
        $customer = (string) ($this->instance->crmCustomer?->name ?? '');

        $this->dispatch(
            'receive-modal-open',
            instanceIds: [(string) $this->instance->id],
            summaries: [[
                'id' => (string) $this->instance->id,
                'label' => $label,
                'customer' => $customer,
            ]],
        )->to(ReceiveSampleRequest::class);
    }

    #[On('receive-completed')]
    public function onReceiveCompleted(): void
    {
        $this->instance = $this->instance->fresh([
            'submissionForm',
            'crmCustomer',
            'batches',
            'analysisAcceptanceForms',
            'sampleSubmissionRequest',
            'values.element',
        ]) ?? $this->instance;
        $this->commercialEnquiry = $this->instance->sampleSubmissionRequest;
    }

    #[Renderless]
    public function openAcceptSampleWizard(): void
    {
        $this->authorizeFormAccess(auth()->user());

        $enquiry = $this->commercialEnquiry;
        if ($enquiry === null
            || ! app(EnquiryReceptionReadinessService::class)->isEligibleForSampleAcceptance($enquiry, $this->instance)) {
            session()->flash('request_view_message', 'Sample acceptance is only available when the request is Ready for Reception.');

            return;
        }

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: $this->instance->id,
            submissionRequestId: $enquiry->id,
        )->to(AcceptanceFormWizard::class);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['tests', 'notes', 'attachments', 'custody'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function generateTestRequestFormReport(): void
    {
        $this->authorizeFormAccess(auth()->user());

        $instance = $this->instance->fresh(['values.element', 'submissionForm']);

        try {
            app(TestRequestFormPdfService::class)->generateAndStore($instance);
            app(SubmissionFormInstanceDocumentAttachmentService::class)->attachTestRequestForm(
                $instance,
                auth()->id(),
                regenerate: false,
            );
            session()->flash('request_view_message', 'Test Request Form generated successfully.');
            $this->dispatch('open-test-request-pdf', url: route('test-request-form.pdf', $instance->id));
        } catch (\Throwable $exception) {
            session()->flash('request_view_message', 'Failed to generate Test Request Form. Please try again.');
        }
    }

    public function addNote(SubmissionFormInstanceNoteService $noteService): void
    {
        $user = auth()->user();
        $this->authorizeFormAccess($user);

        $this->validate([
            'noteBody' => ['required', 'string', 'max:10000'],
            'noteVisibility' => ['required', 'in:internal,public'],
        ]);

        $noteService->addNote(
            $this->instance,
            $user,
            $this->noteBody,
            $this->noteVisibility
        );

        $this->reset(['noteBody']);
        $this->noteVisibility = SubmissionFormInstanceNote::VISIBILITY_INTERNAL;

        $this->instance->load(['notes.author']);

        session()->flash('request_view_message', 'Note saved successfully.');
    }

    public function openAttachmentModal(): void
    {
        $this->reset([
            'newAttachment',
            'newAttachmentTitle',
            'newAttachmentType',
            'newCustomAttachmentType',
            'newAttachmentDescription',
        ]);
        $this->resetValidation();
        $this->loadAvailableAttachmentTypes();
        $this->showAttachmentModal = true;
    }

    public function closeAttachmentModal(): void
    {
        $this->showAttachmentModal = false;
        $this->reset([
            'newAttachment',
            'newAttachmentTitle',
            'newAttachmentType',
            'newCustomAttachmentType',
            'newAttachmentDescription',
        ]);
        $this->resetValidation();
    }

    public function loadAvailableAttachmentTypes(): void
    {
        $dbTypes = SubmissionFormInstanceAttachment::query()
            ->whereNotNull('attachment_type')
            ->where('attachment_type', '!=', '')
            ->distinct()
            ->pluck('attachment_type')
            ->map(fn ($type) => $this->formatAttachmentTypeLabel((string) $type))
            ->all();

        $systemTypes = \App\Models\System\SystemConfiguration::query()
            ->where('key', 'attachment_type')
            ->pluck('value')
            ->map(fn ($type) => trim((string) $type))
            ->filter()
            ->all();

        $this->availableAttachmentTypes = collect(array_merge(
            $this->defaultAttachmentTypes,
            $dbTypes,
            $systemTypes
        ))
            ->map(fn ($type) => trim((string) $type))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function uploadAttachment(): void
    {
        $user = auth()->user();
        $this->authorizeFormAccess($user);

        $this->validate([
            'newAttachment' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp,txt'],
            'newAttachmentTitle' => ['required', 'string', 'max:255'],
            'newAttachmentType' => ['required', 'string', 'max:100'],
            'newCustomAttachmentType' => ['required_if:newAttachmentType,Other', 'nullable', 'string', 'max:100'],
            'newAttachmentDescription' => ['nullable', 'string', 'max:5000'],
        ], [
            'newAttachment.required' => 'Please attach a file.',
            'newAttachment.max' => 'The attachment must not be greater than 10MB.',
            'newAttachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, webp, txt.',
            'newAttachmentTitle.required' => 'Please enter a title.',
            'newAttachmentType.required' => 'Please select an attachment type.',
            'newCustomAttachmentType.required_if' => 'Please enter the attachment type.',
        ]);

        $resolvedType = $this->newAttachmentType === 'Other'
            ? trim($this->newCustomAttachmentType)
            : $this->newAttachmentType;

        $originalName = $this->newAttachment->getClientOriginalName();
        $path = $this->newAttachment->store('request-attachments', 'public');

        $this->instance->customAttachments()->create([
            'file_path' => $path,
            'original_name' => $originalName,
            'uploaded_by' => $user->id,
            'attachment_type' => $resolvedType ?: null,
            'attachment_heading' => $this->newAttachmentTitle,
            'description' => $this->newAttachmentDescription ?: null,
        ]);

        $this->closeAttachmentModal();
        $this->loadAvailableAttachmentTypes();
        session()->flash('request_view_message', 'Attachment uploaded successfully.');
    }

    private function formatAttachmentTypeLabel(string $type): string
    {
        $normalized = str_replace('_', ' ', trim($type));

        return ucwords($normalized);
    }

    private function authorizeFormAccess(?\App\User $user): void
    {
        if (! $user) {
            abort(403, 'You are not authorized to access this form instance.');
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return;
        }

        if ($user->can('laboratory.components.rft form.view') || $user->can('laboratory.permission')) {
            return;
        }

        abort(403, 'You are not authorized to access this form instance.');
    }

    /**
     * @return list<array{
     *     row_index: int,
     *     customer_sample_id: ?string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_id: ?string,
     *     analysis_type_name: ?string,
     *     analysis_element_id: ?string,
     *     parameter_label: ?string
     * }>
     */
    public function getSampleLinesProperty(): array
    {
        return app(SubmissionRequestSampleLineService::class)->linesForInstance($this->instance);
    }

    /**
     * @return Collection<int, object{
     *     source: string,
     *     title: string,
     *     subtitle: ?string,
     *     user_name: ?string,
     *     occurred_at: \Carbon\Carbon,
     *     badge: ?string,
     *     comment: ?string
     * }>
     */
    public function getCustodyTimelineProperty(): Collection
    {
        $events = collect();
        $preLabCutoff = $this->resolvePreLabCustodyCutoff();

        $auditLogs = $this->instance->auditLogs()->with('user')->latest()->get();
        foreach ($auditLogs as $log) {
            $userName = $log->user ? $log->user->name : 'Customer (Portal)';
            $title = method_exists($log, 'getActionDisplayName') ? $log->getActionDisplayName() : ucfirst((string) ($log->action ?? $log->event ?? ''));
            $fieldChanges = is_array($log->field_changes ?? null) ? $log->field_changes : (is_array($log->new_values ?? null) ? $log->new_values : []);
            $statusTo = data_get($fieldChanges, 'status.to', data_get($fieldChanges, 'status', $this->instance->status));

            if (($log->action ?? $log->event ?? null) === 'submitted'
                || (($log->action ?? $log->event ?? null) === 'updated' && $statusTo === 'submitted')) {
                $title = 'Submitted Requests';
            } elseif (($log->action ?? $log->event ?? null) === 'created') {
                $title = 'Request Drafted';
            } elseif (($log->action ?? $log->event ?? null) === 'received') {
                $title = 'Received Request';
            }

            $occurredAt = \Carbon\Carbon::parse($log->created_at);
            if ($this->shouldExcludeCustodyEvent($occurredAt, $preLabCutoff)) {
                continue;
            }

            $badge = method_exists($log, 'getActionBadgeColor') ? $log->getActionBadgeColor() : (($log->action ?? $log->event ?? null) === 'created' ? 'info' : 'success');

            $events->push((object) [
                'source' => 'audit',
                'title' => $title,
                'subtitle' => 'Status: '.(is_string($statusTo) ? $statusTo : (string) $this->instance->status),
                'user_name' => $userName,
                'occurred_at' => $occurredAt,
                'badge' => $badge,
                'comment' => $log->notes ?? null,
            ]);
        }

        $intrays = $this->instance->intrays()
            ->with(['fromUser', 'toUser', 'assignedBy', 'completedByUser'])
            ->orderByDesc('created_at')
            ->get();

        foreach ($intrays as $intray) {
            $userName = $intray->assignedBy ? $intray->assignedBy->name : 'System';
            $title = $intray->status === 'completed' ? 'Intray completed' : 'Intray assigned';
            $subtitle = '';
            if ($intray->toUser) {
                $subtitle = $intray->fromUser
                    ? 'From '.$intray->fromUser->name.' → '.$intray->toUser->name
                    : 'Assigned to '.$intray->toUser->name;
            }

            $occurredAt = \Carbon\Carbon::parse($intray->completed_at ?? $intray->created_at);
            if ($this->shouldExcludeCustodyEvent($occurredAt, $preLabCutoff)) {
                continue;
            }

            $events->push((object) [
                'source' => 'intray',
                'title' => $title,
                'subtitle' => $subtitle,
                'user_name' => $userName,
                'occurred_at' => $occurredAt,
                'badge' => $intray->status === 'completed' ? 'success' : 'warning',
                'comment' => $intray->comment,
            ]);
        }

        return $events
            ->filter(fn ($event) => $event->occurred_at !== null)
            ->sortByDesc(fn ($event) => $event->occurred_at)
            ->values();
    }

    public function getCustodyEnteredLabProperty(): bool
    {
        return $this->resolvePreLabCustodyCutoff() !== null;
    }

    private function resolvePreLabCustodyCutoff(): ?\Carbon\Carbon
    {
        $labStatuses = [
            'Samples In Lab',
            'Sample Verification',
            'Sample Approval',
            'Reports for Collection',
            'Completed',
        ];

        $labBatch = $this->instance->batches()
            ->whereIn('status', $labStatuses)
            ->orderBy('updated_at')
            ->first();

        if ($labBatch === null) {
            return null;
        }

        $labCustody = ChainOfCustody::query()
            ->where('sample_header_id', $labBatch->id)
            ->where('workflow_stage', 'Samples In Lab')
            ->orderBy('created_at')
            ->first();

        if ($labCustody?->created_at !== null) {
            return \Carbon\Carbon::parse($labCustody->created_at);
        }

        return $labBatch->updated_at ? \Carbon\Carbon::parse($labBatch->updated_at) : null;
    }

    private function shouldExcludeCustodyEvent(\Carbon\Carbon $occurredAt, ?\Carbon\Carbon $preLabCutoff): bool
    {
        if ($preLabCutoff === null) {
            return false;
        }

        return $occurredAt->greaterThan($preLabCutoff);
    }

    public function openProcessEnquiry(): void
    {
        if ($this->commercialEnquiry === null) {
            return;
        }

        $this->dispatch('process-enquiry-open', enquiryId: $this->commercialEnquiry->id)
            ->to(ProcessEnquiryWizard::class);
    }

    public function openRejectWizard(): void
    {
        $this->authorizeFormAccess(auth()->user());

        $status = strtolower((string) $this->instance->status);
        if (in_array($status, ['rejected', 'cancelled', 'approved'], true)) {
            session()->flash('request_view_message', 'This submission can no longer be rejected.');

            return;
        }

        $this->dispatch(
            'open-rejection-wizard',
            submissionFormInstanceId: $this->instance->id,
            submissionRequestId: $this->commercialEnquiry?->id,
        )->to(SampleRejectionWizard::class);
    }

    public function isTrfForm(): bool
    {
        $code = strtoupper((string) ($this->submissionForm->document_code ?? ''));

        return str_starts_with($code, 'TRF-');
    }

    public function generateTrfPdf(AmSpecTrfPdfService $service): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->isTrfForm()) {
            session()->flash('request_view_message', 'This form is not a test request form.');

            return;
        }

        $this->trfPdfUrl = $service->generateAndStore($this->instance);
        session()->flash('request_view_message', 'TRF generated successfully.');
    }

    public function downloadTrfPdf(): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->trfPdfUrl === null || $this->trfPdfUrl === '') {
            session()->flash('request_view_message', 'Generate the TRF first.');

            return null;
        }

        $fullPath = storage_path('app'.$this->trfPdfUrl);
        if (! is_file($fullPath)) {
            session()->flash('request_view_message', 'TRF file was not found. Generate the TRF again.');
            $this->trfPdfUrl = null;

            return null;
        }

        $filename = basename($fullPath);

        return response()->download($fullPath, $filename);
    }

    public function sendTrfPdfToCustomer(): void
    {
        $user = auth()->user();
        $this->authorizeFormAccess($user);

        if (! $this->isTrfForm()) {
            session()->flash('request_view_message', 'This form is not a test request form.');

            return;
        }

        if ($this->trfPdfUrl === null || $this->trfPdfUrl === '') {
            session()->flash('request_view_message', 'Generate the TRF first.');

            return;
        }

        $relativePath = $this->trfPdfUrl;

        $contact = $this->commercialEnquiry?->contact;
        if ($contact === null || empty($contact->email)) {
            session()->flash('request_view_message', 'No customer contact email is available for this request.');

            return;
        }

        $file = storage_path('app'.$relativePath);
        if (! is_file($file)) {
            session()->flash('request_view_message', 'TRF PDF file was not found. Generate the PDF first.');

            return;
        }

        $formNumber = $this->instance->getDocumentControlNumber() ?? $this->instance->form_number ?? 'TRF';
        $subject = 'Test Request Form '.$formNumber;
        $body = 'Please find attached your test request form '.$formNumber.'.';

        notify_user($body, $contact->email, $subject, $file);

        session()->flash('request_view_message', 'TRF PDF sent to '.$contact->email.'.');
    }

    public function workflowBoardStatus(): string
    {
        return 'Samples Receiving';
    }

    public function workflowBoardTab(): string
    {
        $batch = $this->instance->batches->first();
        if (($batch && $batch->status === 'Samples Request Review')
            || $this->instance->analysisAcceptanceForms->isNotEmpty()) {
            return 'accepted';
        }

        if ($this->commercialEnquiry !== null
            && in_array((string) $this->commercialEnquiry->status, [
                SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
                SampleSubmissionRequest::STATUS_IN_REVIEW,
            ], true)) {
            return 'ready_for_reception';
        }

        return 'submitted';
    }

    public function render(): View
    {
        $formData = $this->instance->getFormDataForDisplay();

        $attachmentInstances = $this->instance->attachmentInstances()
            ->with('submissionForm')
            ->whereIn('status', ['submitted', 'in_review', 'approved', 'rejected', 'draft'])
            ->latest()
            ->get();

        $acceptanceForm = $this->instance->analysisAcceptanceForms->first();

        $canCreateSamples = $acceptanceForm !== null
            && $acceptanceForm->status === \App\Models\Sampleworkflow\AnalysisAcceptanceForm::STATUS_COMPLETED
            && $this->instance->batches->isEmpty();

        $sampleStatus = app(SampleCreationService::class)->getSampleCreationStatus($this->instance);

        $batchIds = $this->instance->batches->pluck('id');
        $batchAttachments = BatchAttachment::query()
            ->whereIn('batch_id', $batchIds)
            ->where(function ($q) {
                $q->where('title', 'like', '%Laboratory Analysis Acceptance%')
                  ->orWhere('title', 'like', '%Sample Receipt Notification%');
            })
            ->latest()
            ->get();

        $customAttachments = $this->instance->customAttachments()->with('uploader')->get();

        $formMediaAttachments = [];
        $values = collect($formData['sections'] ?? [])
            ->flatMap(fn($s) => $s['element_holders'] ?? [])
            ->filter(fn($h) => $h['holder_type'] === 'field')
            ->flatMap(fn($h) => $h['elements'] ?? [])
            ->filter(fn($e) => in_array($e['element_type'], ['file', 'camera_photo', 'image_upload']))
            ->values();

        foreach ($values as $element) {
            $savedValue = $element['saved_values'][0] ?? null;
            if ($savedValue && !empty($savedValue['value']) && $savedValue['value'] !== 'N/A') {
                $candidate = trim((string) $savedValue['value']);
                $decoded = json_decode($candidate, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $first = $decoded[0] ?? null;
                    if (is_string($first)) {
                        $candidate = trim($first);
                    } elseif (is_array($first)) {
                        foreach (['url', 'path', 'file_path', 'value'] as $key) {
                            if (!empty($first[$key]) && is_string($first[$key])) {
                                $candidate = trim($first[$key]);
                                break;
                            }
                        }
                    }
                }
                if ($candidate && $candidate !== 'N/A') {
                    $mediaUrl = $this->instance->resolveUploadedMediaUrl($candidate);
                    if ($mediaUrl) {
                        $formMediaAttachments[] = (object) [
                            'original_name' => $element['label'],
                            'file_url' => $mediaUrl,
                            'created_at' => $this->instance->created_at,
                        ];
                    }
                }
            }
        }

        $presenter = new RequestViewPagePresenter(
            instance: $this->instance,
            submissionForm: $this->submissionForm,
            commercialEnquiry: $this->commercialEnquiry,
            trfPdfUrl: $this->trfPdfUrl,
            canCreateSamples: $canCreateSamples,
            linkedBatchesOutOfSyncWithForm: $this->linkedBatchesOutOfSyncWithForm,
            showSampleCollectionLabel: $this->shouldShowSampleCollectionLabel(),
            isTrfForm: $this->isTrfForm(),
        );

        $sectionCards = $presenter->sectionCards($formData);
        $sampleLines = $this->sampleLines;
        $boardStatus = $this->workflowBoardStatus();

        return view('livewire.submission-forms.request-view-page', [
            'formData' => $formData,
            'attachmentInstances' => $attachmentInstances,
            'batchAttachments' => $batchAttachments,
            'customAttachments' => $customAttachments,
            'formMediaAttachments' => collect($formMediaAttachments),
            'canCreateSamples' => $canCreateSamples,
            'sampleStatus' => $sampleStatus,
            'acceptanceForm' => $acceptanceForm,
            'workflowForms' => $this->instance->workflowForms()->get(),
            'viewHeader' => $presenter->header(),
            'requestInfoCard' => $presenter->requestInfoCard($formData, $sampleLines),
            'customerCard' => $sectionCards['customer'],
            'sectionCards' => $sectionCards['sections'],
            'testSamplesCard' => $presenter->testSamplesCard($sampleLines),
            'nextStepActions' => $presenter->nextStepActions($boardStatus),
            'boardStatus' => $boardStatus,
            'boardTab' => $this->workflowBoardTab(),
        ]);
    }
}
