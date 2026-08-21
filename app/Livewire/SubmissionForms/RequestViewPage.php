<?php

namespace App\Livewire\SubmissionForms;

use App\ChainOfCustody;
use App\BatchAttachment;
use App\Livewire\Sampleworkflow\AcceptanceFormWizard;
use App\Livewire\Sampleworkflow\ProcessEnquiryWizard;
use App\Livewire\Sampleworkflow\ReceiveSampleRequest;
use App\Livewire\Sampleworkflow\SampleRejectionWizard;
use App\Models\CRM\CustomerContact;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceAttachment;
use App\Models\SubmissionFormInstanceNote;
use App\Services\SampleCreationService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use App\Services\SubmissionFormBatchSyncService;
use App\Services\Commercial\EnquiryAccountSettingsService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Commercial\QuotationApprovalService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\Planner\SamplingScheduleTrfSync;
use App\Services\SubmissionForm\PortalDynamicOptionsService;
use App\Services\SubmissionForm\RequestViewPagePresenter;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use App\Services\SubmissionForm\SubmissionFormInstanceNoteService;
use App\Services\SubmissionForm\SubmissionFormInstanceSampleRowUpdateService;
use App\Services\SubmissionForm\SubmissionFormInstanceTrfEditService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use App\Services\SubmissionForm\TrfDocumentCodeForSampleType;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
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

    public bool $quotationPendingApproval = false;

    public bool $quotationApprovedReadyToSend = false;

    public bool $canApproveQuotation = false;

    public string $approvalDecisionComments = '';

    public bool $showApproveQuotationModal = false;

    public bool $showSendQuotationModal = false;

    /** Optional customer email when approving/sending from LIMS (portal send is automatic when eligible). */
    public bool $approveSendEmail = true;

    /** @var list<string> */
    public array $approveRecipientContactIds = [];

    /** @var list<array{id: string, name: string, email: string, company_unit: string, sampling_location: string, receive_quotations: bool, can_login: bool}> */
    public array $approveRecipientOptions = [];

    public bool $showChangeLabManagerForm = false;

    public ?string $reassignLabManagerId = null;

    public bool $reassignNotifyEmail = true;

    /** @var list<array{id: string, name: string}> */
    public array $labManagerOptions = [];

    public bool $sendPortal = true;

    public bool $sendEmail = false;

    public SubmissionFormInstance $instance;

    public SubmissionForm $submissionForm;

    public bool $linkedBatchesOutOfSyncWithForm = false;

    public ?SampleSubmissionRequest $commercialEnquiry = null;

    public ?string $trfPdfUrl = null;

    public bool $sendTrfEmail = true;

    public bool $showTrfOrientationModal = false;

    public string $trfPdfOrientation = 'landscape';

    public string $defaultTrfPdfOrientation = 'landscape';

    public bool $showQuotationAcceptanceModal = false;

    /** When true, modal only captures PO for an already-accepted quotation. */
    public bool $quotationAcceptancePoOnly = false;

    public string $quotationAcceptanceSignerName = '';

    public string $quotationAcceptanceSignature = '';

    public ?string $quotationAcceptanceContactId = null;

    public string $quotationAcceptanceAttachmentType = '';

    public $quotationAcceptanceAttachment = null;

    /** Contact the currently held signature belongs to, so re-sent updates don't reset the pad. */
    #[Locked]
    public string $quotationAcceptanceAppliedContactId = '';

    /** @var list<array{id: string, label: string}> */
    public array $quotationAcceptanceContactOptions = [];

    public string $clientPoNumber = '';

    public string $poRuleType = 'walk_in';

    public bool $poRequiresPo = false;

    public string $poRuleMessage = '';

    public bool $showSampleRowEditModal = false;

    public ?int $editingRowIndex = null;

    /** @var array<string, mixed> */
    public array $editingRowFields = [];

    /** @var list<array{id: string, name: string, label: string, element_type: string, required: bool, options: array<int|string, mixed>}> */
    public array $editingRowFieldDefinitions = [];

    /** @var array<string, list<array{value: mixed, label: string}>> */
    public array $editingRowSelectOptions = [];

    public string $editingRowCollectionSamplingLocation = '';

    public bool $showTrfEditModal = false;
    public bool $showTrfViewModal = false;

    /** @var 'customer'|'collection'|'samples' */
    public string $trfEditSlide = 'customer';

    public ?int $trfEditExpandedSampleIndex = null;

    public string $trfEditClientName = '';

    public string $trfEditCompanyUnitId = '';

    public string $trfEditContactId = '';

    public string $trfEditContactName = '';

    public string $trfEditContactEmail = '';

    public string $trfEditContactPhone = '';

    public ?string $trfEditCustomerId = null;

    /** @var list<array{id: string, text: string}> */
    public array $trfEditUnitOptions = [];

    /** @var list<array{id: string, text: string, email: string, phone: string}> */
    public array $trfEditContactOptions = [];

    /** @var list<array{value: string, label: string}> */
    public array $trfEditSamplePointOptions = [];

    /** @var list<array{id: string, name: string, label: string, element_type: string, required: bool, options: array<int|string, mixed>}> */
    public array $trfEditCollectionDefinitions = [];

    /** @var array<string, mixed> */
    public array $trfEditCollectionFields = [];

    /** @var list<array{index: int, number: int, summary: string}> */
    public array $trfEditSampleSummaries = [];

    /** @var array<int, array<string, mixed>> */
    public array $trfEditSampleDrafts = [];

    /** @var list<array{id: string, name: string, label: string, element_type: string, required: bool, options: array<int|string, mixed>}> */
    public array $trfEditSampleDefinitions = [];

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
                'sampleSubmissionRequest.currentQuotation.approvedByUser',
                'sampleSubmissionRequest.contact',
                'sampleSubmissionRequest.customer',
                'submissionForm',
                'submittedBy',
                'reviewedBy',
                'crmCustomer',
                'batches.samples',
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

        $this->syncQuotationApprovalUiState();

        $requestedTab = (string) request()->query('tab', '');
        if (in_array($requestedTab, ['tests', 'sample_collection', 'notes', 'attachments', 'custody'], true)) {
            $this->activeTab = $requestedTab;
        } elseif ($requestedTab === 'quotation_approvals') {
            $this->activeTab = 'tests';
        } else {
            $presenter = new RequestViewPagePresenter(
                instance: $this->instance,
                submissionForm: $this->submissionForm,
                commercialEnquiry: $this->commercialEnquiry,
                trfPdfUrl: $this->trfPdfUrl,
                isTrfForm: $this->isTrfForm(),
            );
            $this->activeTab = $presenter->defaultCanvasTab();
        }
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

        if ($this->commercialEnquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_SENT) {
            session()->flash('request_view_message', 'Quotation can only be accepted when the enquiry is Quotation Sent.');

            return;
        }

        $enquiry = $this->commercialEnquiry->loadMissing(['customer', 'contact']);
        $customerId = trim((string) ($enquiry->crm_customer_id ?? ''));
        if ($customerId === '') {
            session()->flash('request_view_message', 'This enquiry has no CRM customer linked. Link a customer before recording quotation acceptance.');

            return;
        }

        $this->quotationAcceptancePoOnly = false;
        $this->quotationAcceptanceContactId = $enquiry->crm_customer_contact_id
            ? (string) $enquiry->crm_customer_contact_id
            : null;
        $this->quotationAcceptanceContactOptions = app(\App\Services\Sampleworkflow\CustomerContactVerificationService::class)
            ->activeContactsForCustomer($customerId);
        $this->hydrateQuotationAcceptancePoRules($enquiry);
        $this->applyQuotationAcceptanceContact($this->quotationAcceptanceContactId);
        $this->showQuotationAcceptanceModal = true;
        $this->dispatch(
            'quotation-acceptance-modal-opened',
            signature: $this->quotationAcceptanceSignature,
        );
    }

    public function closeQuotationAcceptanceModal(): void
    {
        $this->showQuotationAcceptanceModal = false;
        $this->quotationAcceptancePoOnly = false;
        $this->quotationAcceptanceSignerName = '';
        $this->quotationAcceptanceSignature = '';
        $this->quotationAcceptanceContactId = null;
        $this->quotationAcceptanceAppliedContactId = '';
        $this->quotationAcceptanceContactOptions = [];
        $this->quotationAcceptanceAttachmentType = '';
        $this->quotationAcceptanceAttachment = null;
        $this->clientPoNumber = '';
        $this->poRuleType = 'walk_in';
        $this->poRequiresPo = false;
        $this->poRuleMessage = '';
    }

    public function updatedQuotationAcceptanceContactId(?string $contactId): void
    {
        if ((string) $contactId === $this->quotationAcceptanceAppliedContactId) {
            return;
        }

        $this->applyQuotationAcceptanceContact($contactId);
        $this->dispatch(
            'quotation-acceptance-signature-changed',
            signature: $this->quotationAcceptanceSignature,
        );
    }

    public function submitQuotationAcceptanceSignature(?string $signature = null): void
    {
        if ($signature !== null && str_starts_with($signature, 'data:image/')) {
            $this->quotationAcceptanceSignature = $signature;
        }

        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        if ($this->quotationAcceptancePoOnly) {
            $this->submitPoAndReadyForReception();

            return;
        }

        $this->validate([
            'quotationAcceptanceContactId' => ['required', 'string'],
            'quotationAcceptanceSignature' => ['required', 'string'],
            'quotationAcceptanceAttachment' => ['nullable', 'file', 'max:10240'],
            'quotationAcceptanceAttachmentType' => ['nullable', 'string', 'max:120'],
        ], [
            'quotationAcceptanceContactId.required' => 'Select the customer contact.',
            'quotationAcceptanceSignature.required' => 'Provide the customer signature.',
        ]);

        $signerName = $this->resolveQuotationAcceptanceSignerName($this->quotationAcceptanceContactId);
        if ($signerName === '') {
            $this->addError('quotationAcceptanceContactId', 'Select a valid customer contact.');

            return;
        }
        $this->quotationAcceptanceSignerName = $signerName;

        try {
            app(EnquiryAccountSettingsService::class)->validateAcceptPayload(
                $this->commercialEnquiry->customer,
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            $accepted = app(QuotationFromEnquiryService::class)->recordWalkInAcceptance(
                $this->commercialEnquiry,
                null,
                false,
                [
                    'signature' => $this->quotationAcceptanceSignature,
                    'signer_name' => $this->quotationAcceptanceSignerName,
                    'contact_id' => $this->quotationAcceptanceContactId,
                ],
            );

            if ($this->quotationAcceptanceAttachment !== null) {
                $path = $this->quotationAcceptanceAttachment->store('request-attachments', 'public');
                $type = trim($this->quotationAcceptanceAttachmentType) !== ''
                    ? trim($this->quotationAcceptanceAttachmentType)
                    : 'Purchase Order';

                $this->instance->customAttachments()->create([
                    'file_path' => $path,
                    'original_name' => $this->quotationAcceptanceAttachment->getClientOriginalName(),
                    'uploaded_by' => auth()->id(),
                    'attachment_type' => $type,
                    'attachment_heading' => $type,
                    'description' => 'Uploaded during quotation acceptance',
                ]);
            }

            $this->commercialEnquiry = app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $accepted,
                (string) ($accepted->accepted_quotation_header_id ?? $accepted->current_quotation_header_id ?? ''),
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            $this->closeQuotationAcceptanceModal();
            $this->dispatch('imara-toast', [
                'type' => 'success',
                'title' => 'Quotation accepted',
                'message' => 'This request is ready for physical reception.',
                'durationMs' => 7000,
            ]);
            session()->flash('request_view_message', 'Quotation accepted. This request is ready for physical reception on the Samples Receiving board.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
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

        $enquiry = $this->commercialEnquiry->loadMissing(['customer']);
        $this->quotationAcceptancePoOnly = true;
        $this->quotationAcceptanceSignerName = '';
        $this->quotationAcceptanceSignature = '';
        $this->quotationAcceptanceContactId = null;
        $this->quotationAcceptanceAppliedContactId = '';
        $this->quotationAcceptanceContactOptions = [];
        $this->hydrateQuotationAcceptancePoRules($enquiry);
        $this->showQuotationAcceptanceModal = true;
        $this->dispatch('quotation-acceptance-modal-opened', signature: '');
    }

    public function closePoCaptureModal(): void
    {
        $this->closeQuotationAcceptanceModal();
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
                ],
            );

            $this->commercialEnquiry = app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $this->commercialEnquiry,
                (string) ($this->commercialEnquiry->accepted_quotation_header_id ?? $this->commercialEnquiry->current_quotation_header_id ?? ''),
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            $this->closeQuotationAcceptanceModal();
            session()->flash('request_view_message', 'PO recorded. This request is ready for physical reception on the Samples Receiving board.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
        } catch (\Throwable $exception) {
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    protected function hydrateQuotationAcceptancePoRules(SampleSubmissionRequest $enquiry): void
    {
        $rules = app(EnquiryAccountSettingsService::class)->poRulesForCustomer($enquiry->customer);
        $this->poRuleType = (string) ($rules['type'] ?? 'walk_in');
        $this->poRequiresPo = (bool) ($rules['requires_po'] ?? false);
        $this->poRuleMessage = $this->resolvePoRuleMessage();
        $this->clientPoNumber = (string) ($enquiry->client_po_number ?? '');
    }

    protected function applyQuotationAcceptanceContact(?string $contactId): void
    {
        $contactId = filled($contactId) ? (string) $contactId : null;
        $this->quotationAcceptanceContactId = $contactId;
        $this->quotationAcceptanceAppliedContactId = (string) $contactId;
        $this->quotationAcceptanceSignerName = $this->resolveQuotationAcceptanceSignerName($contactId);
        $this->quotationAcceptanceSignature = '';

        if ($contactId === null) {
            return;
        }

        $contact = CustomerContact::query()->find($contactId);
        if ($contact !== null && $contact->hasSignatureImage()) {
            $this->quotationAcceptanceSignature = $contact->signatureDataUri();
        }
    }

    protected function resolveQuotationAcceptanceSignerName(?string $contactId): string
    {
        if (! filled($contactId)) {
            return '';
        }

        $contact = CustomerContact::query()->find((string) $contactId);
        if ($contact === null) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $contact->first_name,
            $contact->middle_name,
            $contact->last_name,
        ]))) ?: (string) ($contact->email ?? '');
    }

    protected function resolvePoRuleMessage(): string
    {
        if ($this->poRequiresPo) {
            return 'This customer account requires a purchase order number before the request can be marked ready.';
        }

        return 'This customer may proceed without a PO number.';
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
            'batches.samples',
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
        if ($enquiry === null) {
            session()->flash('request_view_message', 'No commercial enquiry is linked to this request.');

            return;
        }

        $readiness = app(EnquiryReceptionReadinessService::class);
        $mode = (string) $enquiry->status === SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION
            ? 'receive_only'
            : 'accept_register';

        $eligible = $mode === 'receive_only'
            ? $readiness->isEligibleForReceiveHandoff($enquiry, $this->instance)
            : $readiness->isEligibleForSampleAcceptance($enquiry, $this->instance);

        if (! $eligible) {
            session()->flash(
                'request_view_message',
                $mode === 'receive_only'
                    ? 'Receive Samples is only available when the request is Ready for Reception.'
                    : 'Sample acceptance is only available during Sample Integrity & Acceptance Check (after Receive Samples).'
            );

            return;
        }

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: $this->instance->id,
            submissionRequestId: $enquiry->id,
            mode: $mode,
        )->to(AcceptanceFormWizard::class);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['tests', 'sample_collection', 'notes', 'attachments', 'custody'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function openApproveQuotationModal(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->canApproveQuotation || $this->commercialEnquiry === null) {
            return;
        }

        $this->approveRecipientOptions = $this->buildApproveRecipientOptions();
        // Default-check contacts with receive_quotations (or primary enquiry contact).
        $this->approveRecipientContactIds = collect($this->approveRecipientOptions)
            ->filter(static fn (array $row): bool => (bool) ($row['receive_quotations'] ?? false))
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->values()
            ->all();
        if ($this->approveRecipientContactIds === [] && $this->approveRecipientOptions !== []) {
            $this->approveRecipientContactIds = [(string) $this->approveRecipientOptions[0]['id']];
        }
        $this->approveSendEmail = true;
        $this->showApproveQuotationModal = true;
    }

    public function closeApproveQuotationModal(): void
    {
        $this->showApproveQuotationModal = false;
        $this->approveRecipientContactIds = [];
        $this->approveRecipientOptions = [];
    }

    public function openSendApprovedQuotationModal(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->quotationApprovedReadyToSend || $this->commercialEnquiry === null) {
            return;
        }

        $this->approveRecipientOptions = $this->buildApproveRecipientOptions();
        $this->approveRecipientContactIds = collect($this->approveRecipientOptions)
            ->filter(static fn (array $row): bool => (bool) ($row['receive_quotations'] ?? false))
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->values()
            ->all();
        if ($this->approveRecipientContactIds === [] && $this->approveRecipientOptions !== []) {
            $this->approveRecipientContactIds = [(string) $this->approveRecipientOptions[0]['id']];
        }
        $this->approveSendEmail = true;
        $this->showSendQuotationModal = true;
    }

    public function closeSendApprovedQuotationModal(): void
    {
        $this->showSendQuotationModal = false;
        $this->approveRecipientContactIds = [];
        $this->approveRecipientOptions = [];
    }

    public function toggleApproveRecipient(string $contactId): void
    {
        $contactId = (string) $contactId;
        if (in_array($contactId, $this->approveRecipientContactIds, true)) {
            $this->approveRecipientContactIds = array_values(array_filter(
                $this->approveRecipientContactIds,
                static fn (string $id): bool => $id !== $contactId,
            ));

            return;
        }

        $this->approveRecipientContactIds[] = $contactId;
    }

    public function confirmApproveQuotationAndSend(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        try {
            $enquiry = $this->commercialEnquiry->loadMissing(['currentQuotation', 'customer', 'contact']);
            $header = $enquiry->currentQuotation;
            if ($header === null) {
                throw new \RuntimeException('No quotation is linked to this request.');
            }

            // In-app approval: lab manager picks CRM contacts; portal send is automatic when eligible;
            // email send is optional. Customer email is PDF-only (no Accept/View). Acceptance via portal or LIMS.
            $enquiry = app(QuotationApprovalService::class)->approve(
                $enquiry,
                $header,
                $this->approvalDecisionComments !== '' ? $this->approvalDecisionComments : null,
                notifyRequester: false,
            );

            $header = $enquiry->currentQuotation ?? $header;
            [$sendPortal, $sendEmail] = $this->resolveApproveSendChannels($enquiry);

            app(QuotationFromEnquiryService::class)->sendToCustomer(
                $enquiry,
                $header,
                sendPortal: $sendPortal,
                sendEmail: $sendEmail,
                recipientContactIds: $this->approveRecipientContactIds,
            );

            $this->commercialEnquiry = $enquiry->fresh(['currentQuotation.approvedByUser', 'customer', 'contact']) ?? $enquiry;
            $this->approvalDecisionComments = '';
            $this->canApproveQuotation = false;
            $this->syncQuotationApprovalUiState();
            $this->closeApproveQuotationModal();

            $this->dispatch('imara-toast', [
                'type' => 'success',
                'title' => 'Quotation approved and sent',
                'message' => 'The quotation was approved and sent to the customer.',
                'durationMs' => 7000,
            ]);
            session()->flash('request_view_message', 'Quotation approved and sent to the customer.');
        } catch (\Throwable $exception) {
            $this->dispatch('imara-toast', [
                'type' => 'error',
                'title' => 'Could not approve quotation',
                'message' => $exception->getMessage(),
                'durationMs' => 7000,
            ]);
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    /**
     * @return list<array{id: string, name: string, email: string, company_unit: string, sampling_location: string, receive_quotations: bool, can_login: bool}>
     */
    private function buildApproveRecipientOptions(): array
    {
        $enquiry = $this->commercialEnquiry;
        if ($enquiry === null) {
            return [];
        }

        $customerId = (string) ($enquiry->crm_customer_id ?? '');
        if ($customerId === '') {
            return [];
        }

        $contacts = CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->get();

        $unitIds = $contacts->pluck('crm_company_unit_id')->filter()->unique()->values()->all();
        $unitNamesById = $unitIds === []
            ? collect()
            : \App\Models\CRM\CRMCompanyUnit::query()
                ->whereIn('id', $unitIds)
                ->pluck('name', 'id');

        $samplePointsByContact = \App\Models\CRM\SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->whereNotNull('contact_id')
            ->whereIn('contact_id', $contacts->pluck('id')->all())
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($point) => (string) $point->contact_id);

        $primaryContactId = (string) ($enquiry->crm_customer_contact_id ?? '');
        $options = [];

        foreach ($contacts as $contact) {
            $email = trim((string) ($contact->email ?? ''));
            if ($email === '' && ! (bool) ($contact->can_login ?? false)) {
                continue;
            }

            $receivesQuotes = (bool) ($contact->receive_quotations ?? false)
                || (string) $contact->id === $primaryContactId
                || (bool) ($contact->is_main_customer_contact ?? false);

            $unitLabel = '—';
            $unitId = (string) ($contact->crm_company_unit_id ?? '');
            if ($unitId !== '' && $unitNamesById->has($unitId)) {
                $unitLabel = (string) $unitNamesById->get($unitId);
            } elseif (trim((string) ($contact->unit_name ?? '')) !== '') {
                $unitLabel = (string) $contact->unit_name;
            }

            $locations = $samplePointsByContact->get((string) $contact->id, collect())
                ->map(fn ($point) => trim((string) ($point->display_name ?? $point->name ?? '')))
                ->filter()
                ->unique()
                ->values();
            $samplingLocation = $locations->isNotEmpty() ? $locations->implode(', ') : '—';

            $name = trim(implode(' ', array_filter([
                (string) ($contact->first_name ?? ''),
                (string) ($contact->middle_name ?? ''),
                (string) ($contact->last_name ?? ''),
            ])));

            $options[] = [
                'id' => (string) $contact->id,
                'name' => $name !== '' ? $name : 'Contact',
                'email' => $email,
                'company_unit' => $unitLabel,
                'sampling_location' => $samplingLocation,
                'receive_quotations' => $receivesQuotes,
                'can_login' => (bool) ($contact->can_login ?? false),
            ];
        }

        return $options;
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function resolveApproveSendChannels(SampleSubmissionRequest $enquiry): array
    {
        $channel = strtolower((string) ($enquiry->source_channel ?? ''));
        $selectedCanLogin = collect($this->approveRecipientOptions)
            ->filter(fn (array $row): bool => in_array((string) $row['id'], $this->approveRecipientContactIds, true))
            ->contains(fn (array $row): bool => (bool) ($row['can_login'] ?? false));

        $sendPortal = $channel === 'portal' || $selectedCanLogin || $this->enquiryHasPortalRecipients($enquiry);
        $sendEmail = $this->approveSendEmail;

        if ($channel === 'walk_in') {
            $sendPortal = false;
        }

        if (! $sendPortal && ! $sendEmail) {
            throw new \RuntimeException('Enable “Email quotation PDF” or select a portal-capable contact so the quotation can be delivered.');
        }

        return [$sendPortal, $sendEmail];
    }

    private function enquiryHasPortalRecipients(SampleSubmissionRequest $enquiry): bool
    {
        $customerId = (string) ($enquiry->crm_customer_id ?? '');
        if ($customerId === '') {
            return false;
        }

        return CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->where('can_login', true)
            ->exists();
    }

    public function approveEnquiryQuotation(): void
    {
        $this->openApproveQuotationModal();
    }

    public function rejectEnquiryQuotation(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        $this->validate([
            'approvalDecisionComments' => ['required', 'string', 'max:5000'],
        ], [
            'approvalDecisionComments.required' => 'Provide a rejection comment.',
        ]);

        try {
            $enquiry = $this->commercialEnquiry->loadMissing('currentQuotation');
            $header = $enquiry->currentQuotation;
            if ($header === null) {
                throw new \RuntimeException('No quotation is linked to this request.');
            }

            $enquiry = app(QuotationApprovalService::class)->reject(
                $enquiry,
                $header,
                $this->approvalDecisionComments,
            );

            $this->commercialEnquiry = $enquiry;
            $this->approvalDecisionComments = '';
            $this->syncQuotationApprovalUiState();
            session()->flash('request_view_message', 'Quotation returned for revision.');
        } catch (\Throwable $exception) {
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    public function sendApprovedQuotationToCustomer(): void
    {
        $this->openSendApprovedQuotationModal();
    }

    public function confirmSendApprovedQuotationToCustomer(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        try {
            $enquiry = $this->commercialEnquiry->loadMissing(['currentQuotation', 'contact', 'customer']);
            $header = $enquiry->currentQuotation;
            if ($header === null) {
                throw new \RuntimeException('No quotation is linked to this request.');
            }

            [$sendPortal, $sendEmail] = $this->resolveApproveSendChannels($enquiry);

            $enquiry = app(QuotationFromEnquiryService::class)->sendToCustomer(
                $enquiry,
                $header,
                $sendPortal,
                $sendEmail,
                $this->approveRecipientContactIds,
            );

            $channel = strtolower((string) ($enquiry->source_channel ?? ''));
            $this->commercialEnquiry = $enquiry;
            $this->syncQuotationApprovalUiState();
            $this->closeSendApprovedQuotationModal();

            $message = $channel === 'walk_in'
                ? 'Quotation sent by email. Record quotation acceptance, then capture the PO.'
                : 'Quotation sent to customer.';
            $this->dispatch('imara-toast', [
                'type' => 'success',
                'title' => 'Quotation sent',
                'message' => $message,
                'durationMs' => 7000,
            ]);
            session()->flash('request_view_message', $message);
        } catch (\Throwable $exception) {
            $this->dispatch('imara-toast', [
                'type' => 'error',
                'title' => 'Could not send quotation',
                'message' => $exception->getMessage(),
                'durationMs' => 7000,
            ]);
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    public function openChangeLabManagerForm(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->quotationPendingApproval) {
            return;
        }

        $this->labManagerOptions = app(QuotationApprovalService::class)
            ->eligibleLabManagers()
            ->map(fn ($user): array => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
            ])
            ->values()
            ->all();

        if ($this->labManagerOptions === []) {
            session()->flash('request_view_message', 'No active Lab Manager users are available for approval.');

            return;
        }

        $currentId = $this->commercialEnquiry?->currentQuotation?->approved_by;
        $this->reassignLabManagerId = $currentId ? (string) $currentId : ($this->labManagerOptions[0]['id'] ?? null);
        $this->reassignNotifyEmail = true;
        $this->showChangeLabManagerForm = true;
    }

    public function closeChangeLabManagerForm(): void
    {
        $this->showChangeLabManagerForm = false;
        $this->reassignLabManagerId = null;
    }

    public function updateQuotationLabManager(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if ($this->commercialEnquiry === null) {
            return;
        }

        $this->validate([
            'reassignLabManagerId' => ['required', 'uuid'],
        ], [
            'reassignLabManagerId.required' => 'Select a lab manager to approve this quotation.',
        ]);

        try {
            $enquiry = $this->commercialEnquiry->loadMissing('currentQuotation');
            $header = $enquiry->currentQuotation;
            if ($header === null) {
                throw new \RuntimeException('No quotation is linked to this request.');
            }

            $enquiry = app(QuotationApprovalService::class)->reassignLabManager(
                $enquiry,
                $header,
                (string) $this->reassignLabManagerId,
                $this->reassignNotifyEmail,
            );

            $this->commercialEnquiry = $enquiry->fresh(['currentQuotation.approvedByUser', 'customer', 'contact']) ?? $enquiry;
            $this->showChangeLabManagerForm = false;
            $this->reassignLabManagerId = null;
            $this->canApproveQuotation = false;
            $this->syncQuotationApprovalUiState();

            $managerName = trim((string) ($this->commercialEnquiry->currentQuotation?->approvedByUser?->name ?? ''));
            session()->flash(
                'request_view_message',
                $managerName !== ''
                    ? 'Lab manager updated to '.$managerName.'. Quotation is awaiting their approval.'
                    : 'Lab manager updated. Quotation is awaiting approval.'
            );
        } catch (\Throwable $exception) {
            session()->flash('request_view_message', $exception->getMessage());
        }
    }

    private function syncQuotationApprovalUiState(): void
    {
        $enquiry = $this->commercialEnquiry;
        if ($enquiry === null) {
            $this->quotationPendingApproval = false;
            $this->quotationApprovedReadyToSend = false;
            $this->canApproveQuotation = false;
            $this->showChangeLabManagerForm = false;

            return;
        }

        $enquiry->loadMissing('currentQuotation');
        $header = $enquiry->currentQuotation;
        $approvalService = app(QuotationApprovalService::class);

        $this->quotationPendingApproval = $approvalService->isPendingApproval($enquiry, $header);
        $this->quotationApprovedReadyToSend = $approvalService->isApprovedReadyToSend($enquiry, $header);
        $this->canApproveQuotation = $header !== null && $approvalService->canCurrentUserApprove($header);

        if (! $this->quotationPendingApproval) {
            $this->showChangeLabManagerForm = false;
        }

        $channel = strtolower((string) ($enquiry->source_channel ?? ''));
        if ($channel === 'walk_in') {
            $this->sendPortal = false;
            $this->sendEmail = true;
        } elseif ($channel === 'portal') {
            $this->sendPortal = true;
        } else {
            $this->sendPortal = false;
        }
    }

    public function generateTestRequestFormReport(): void
    {
        $this->openGenerateTrfOrientationModal();
    }

    public function openGenerateTrfOrientationModal(): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->isTrfForm()) {
            session()->flash('request_view_message', 'This form is not a test request form.');

            return;
        }

        $pdfService = app(TestRequestFormPdfService::class);
        $variantData = $pdfService->buildViewData($this->instance, false);
        $this->trfPdfOrientation = $pdfService->resolveOrientation(
            null,
            (string) $variantData['variant'],
            $this->instance,
        );
        $this->defaultTrfPdfOrientation = $pdfService->defaultOrientationForVariant((string) $variantData['variant']);
        $this->showTrfOrientationModal = true;
    }

    public function closeTrfOrientationModal(): void
    {
        $this->showTrfOrientationModal = false;
    }

    public function confirmGenerateTestRequestFormReport(): void
    {
        $this->authorizeFormAccess(auth()->user());

        $this->validate([
            'trfPdfOrientation' => ['required', 'in:landscape,portrait'],
        ], [
            'trfPdfOrientation.required' => 'Choose landscape or portrait.',
            'trfPdfOrientation.in' => 'Choose landscape or portrait.',
        ]);

        $instance = $this->instance->fresh([
            'values.element',
            'submissionForm',
            'batches.samples',
        ]);

        try {
            app(TestRequestFormPdfService::class)->generateAndStore(
                $instance,
                $this->trfPdfOrientation,
            );
            app(SubmissionFormInstanceDocumentAttachmentService::class)->attachTestRequestForm(
                $instance,
                auth()->id(),
                regenerate: false,
            );
            $this->instance = $instance->fresh(['values.element', 'submissionForm', 'submittedBy', 'batches']) ?? $instance;
            $this->showTrfOrientationModal = false;
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

    public function openSampleRowEditor(int $rowIndex): void
    {
        $this->openTrfEditor('samples', $rowIndex);
    }

    /**
     * @param  'customer'|'collection'|'samples'  $slide
     */
    public function openTrfEditor(string $slide = 'customer', ?int $sampleRowIndex = null): void
    {
        $this->authorizeSampleRowEdit(auth()->user());

        $presenter = $this->requestViewPresenter();
        if (! $presenter->canEditSampleRows()) {
            session()->flash('request_view_message', 'Request details can no longer be edited after reception.');

            return;
        }

        if (! in_array($slide, ['customer', 'collection', 'samples'], true)) {
            $slide = 'customer';
        }

        try {
            $editService = app(SubmissionFormInstanceTrfEditService::class);
            $rowService = app(SubmissionFormInstanceSampleRowUpdateService::class);

            $customer = $editService->customerDraft($this->instance);
            $this->trfEditClientName = $customer['client_name'];
            $this->trfEditCompanyUnitId = $customer['company_unit_id'];
            $this->trfEditContactId = $customer['contact_id'];
            $this->trfEditContactName = $customer['contact_name'];
            $this->trfEditContactEmail = $customer['contact_email'];
            $this->trfEditContactPhone = $customer['contact_phone'];
            $this->trfEditCustomerId = $customer['customer_id'];
            $this->trfEditUnitOptions = $editService->unitOptions($this->trfEditCustomerId);
            $this->refreshTrfEditContactOptions($editService);
            $this->trfEditSamplePointOptions = $editService->samplePointOptions($this->trfEditCompanyUnitId);

            $this->trfEditCollectionDefinitions = $editService->collectionFieldDefinitions($this->submissionForm);
            $this->trfEditCollectionFields = $editService->collectionDraft(
                $this->instance,
                $this->trfEditCollectionDefinitions,
            );

            foreach ($this->trfEditCollectionDefinitions as $field) {
                $name = (string) ($field['name'] ?? '');
                $type = (string) ($field['element_type'] ?? '');
                if ($name === '') {
                    continue;
                }
                if ($type === 'checkbox' || in_array($name, ['sampling_apparatus', 'method_of_sampling'], true)) {
                    $this->trfEditCollectionFields[$name] = \App\Services\SubmissionForm\SubmissionFormSchemaHelper::checkboxGroupValueMap(
                        $this->trfEditCollectionFields[$name] ?? null,
                    );
                    $this->ensureCollectionCheckboxOptionKeys($name, $field['options'] ?? []);
                }
            }

            $this->trfEditSampleDefinitions = $this->prepareSampleRowEditDefinitions(
                $rowService->rowFieldDefinitions($this->submissionForm),
            );

            $this->trfEditSampleSummaries = [];
            $this->trfEditSampleDrafts = [];
            foreach ($this->sampleLines as $line) {
                $index = (int) ($line['row_index'] ?? 0);
                $number = (int) ($line['number'] ?? ($index + 1));
                $summaryParts = array_filter([
                    $line['sample_type'] ?? null,
                    $line['sample_description'] ?? null,
                ], fn ($part): bool => is_string($part) && trim(strip_tags($part)) !== '' && trim(strip_tags($part)) !== '—');
                $summary = $summaryParts === []
                    ? 'Sample '.$number
                    : 'Sample '.$number.' · '.mb_strimwidth(trim(strip_tags(implode(' · ', $summaryParts))), 0, 72, '…');

                $this->trfEditSampleSummaries[] = [
                    'index' => $index,
                    'number' => $number,
                    'summary' => $summary,
                ];

                $draft = $rowService->rowValues($this->instance, $index);
                foreach ($this->trfEditSampleDefinitions as $field) {
                    $name = (string) ($field['name'] ?? '');
                    $type = (string) ($field['element_type'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    if ($type === 'checkbox' || in_array($name, ['test_requirements', 'test_category'], true)) {
                        $draft[$name] = \App\Services\SubmissionForm\SubmissionFormSchemaHelper::checkboxGroupValueMap(
                            $draft[$name] ?? null,
                        );
                    }
                    if (in_array($name, ['sample_type_id', 'analysis_type_id'], true)
                        || in_array($type, ['sample_type_select', 'analysis_type_select'], true)) {
                        $draft[$name] = $this->normalizeRowSelectValues($draft[$name] ?? null);
                    }
                    if ($type === 'analysis_elements_select' || $name === 'parameters') {
                        $current = $draft[$name] ?? '';
                        if (is_string($current) && str_contains($current, ',')) {
                            $draft[$name] = array_values(array_filter(array_map('trim', explode(',', $current))));
                        } elseif (is_string($current) && $current !== '') {
                            $draft[$name] = [$current];
                        } elseif (! is_array($current)) {
                            $draft[$name] = [];
                        }
                    }
                }
                $this->trfEditSampleDrafts[$index] = $draft;
            }

            $this->trfEditSlide = $slide;
            $this->trfEditExpandedSampleIndex = null;
            $this->editingRowIndex = null;
            $this->editingRowFields = [];
            $this->editingRowFieldDefinitions = $this->trfEditSampleDefinitions;
            $this->editingRowSelectOptions = [];
            $this->showSampleRowEditModal = false;
            $this->showTrfViewModal = false;
            $this->showTrfEditModal = true;

            if ($slide === 'samples' && $sampleRowIndex !== null) {
                $this->expandTrfSample($sampleRowIndex);
            }

            $this->dispatch('trf-edit-modal-opened');
        } catch (\Throwable $exception) {
            report($exception);
            $this->closeTrfEditor();
            session()->flash(
                'request_view_message',
                app()->isProduction()
                    ? 'Unable to open the editor. Please contact support if this continues.'
                    : 'Unable to open the editor: '.$exception->getMessage(),
            );
        }
    }

    /**
     * Read-only TRF viewer (same slides/data as edit, no save).
     *
     * @param  'customer'|'collection'|'samples'  $slide
     */
    public function openTrfViewer(string $slide = 'customer', ?int $sampleRowIndex = null): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! in_array($slide, ['customer', 'collection', 'samples'], true)) {
            $slide = 'customer';
        }

        try {
            $editService = app(SubmissionFormInstanceTrfEditService::class);
            $rowService = app(SubmissionFormInstanceSampleRowUpdateService::class);

            $customer = $editService->customerDraft($this->instance);
            $this->trfEditClientName = $customer['client_name'];
            $this->trfEditCompanyUnitId = $customer['company_unit_id'];
            $this->trfEditContactId = $customer['contact_id'];
            $this->trfEditContactName = $customer['contact_name'];
            $this->trfEditContactEmail = $customer['contact_email'];
            $this->trfEditContactPhone = $customer['contact_phone'];
            $this->trfEditCustomerId = $customer['customer_id'];
            $this->trfEditUnitOptions = $editService->unitOptions($this->trfEditCustomerId);
            $this->refreshTrfEditContactOptions($editService);
            $this->trfEditSamplePointOptions = $editService->samplePointOptions($this->trfEditCompanyUnitId);

            $this->trfEditCollectionDefinitions = $editService->collectionFieldDefinitions($this->submissionForm);
            $this->trfEditCollectionFields = $editService->collectionDraft(
                $this->instance,
                $this->trfEditCollectionDefinitions,
            );

            foreach ($this->trfEditCollectionDefinitions as $field) {
                $name = (string) ($field['name'] ?? '');
                $type = (string) ($field['element_type'] ?? '');
                if ($name === '') {
                    continue;
                }
                if ($type === 'checkbox' || in_array($name, ['sampling_apparatus', 'method_of_sampling'], true)) {
                    $this->trfEditCollectionFields[$name] = \App\Services\SubmissionForm\SubmissionFormSchemaHelper::checkboxGroupValueMap(
                        $this->trfEditCollectionFields[$name] ?? null,
                    );
                }
            }

            $this->trfEditSampleDefinitions = $this->prepareSampleRowEditDefinitions(
                $rowService->rowFieldDefinitions($this->submissionForm),
            );

            $this->trfEditSampleSummaries = [];
            $this->trfEditSampleDrafts = [];
            foreach ($this->sampleLines as $line) {
                $index = (int) ($line['row_index'] ?? 0);
                $number = (int) ($line['number'] ?? ($index + 1));
                $summaryParts = array_filter([
                    $line['sample_type'] ?? null,
                    $line['sample_description'] ?? null,
                ], fn ($part): bool => is_string($part) && trim(strip_tags($part)) !== '' && trim(strip_tags($part)) !== '—');
                $summary = $summaryParts === []
                    ? 'Sample '.$number
                    : 'Sample '.$number.' · '.mb_strimwidth(trim(strip_tags(implode(' · ', $summaryParts))), 0, 72, '…');

                $this->trfEditSampleSummaries[] = [
                    'index' => $index,
                    'number' => $number,
                    'summary' => $summary,
                ];

                $draft = $rowService->rowValues($this->instance, $index);
                foreach ($this->trfEditSampleDefinitions as $field) {
                    $name = (string) ($field['name'] ?? '');
                    $type = (string) ($field['element_type'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    if ($type === 'checkbox' || in_array($name, ['test_requirements', 'test_category'], true)) {
                        $draft[$name] = \App\Services\SubmissionForm\SubmissionFormSchemaHelper::checkboxGroupValueMap(
                            $draft[$name] ?? null,
                        );
                    }
                    if (in_array($name, ['sample_type_id', 'analysis_type_id'], true)
                        || in_array($type, ['sample_type_select', 'analysis_type_select'], true)) {
                        $draft[$name] = $this->normalizeRowSelectValues($draft[$name] ?? null);
                    }
                    if ($type === 'analysis_elements_select' || $name === 'parameters') {
                        $current = $draft[$name] ?? '';
                        if (is_string($current) && str_contains($current, ',')) {
                            $draft[$name] = array_values(array_filter(array_map('trim', explode(',', $current))));
                        } elseif (is_string($current) && $current !== '') {
                            $draft[$name] = [$current];
                        } elseif (! is_array($current)) {
                            $draft[$name] = [];
                        }
                    }
                }
                $this->trfEditSampleDrafts[$index] = $draft;
            }

            $this->trfEditSlide = $slide;
            $this->trfEditExpandedSampleIndex = null;
            $this->editingRowIndex = null;
            $this->editingRowFields = [];
            $this->editingRowFieldDefinitions = $this->trfEditSampleDefinitions;
            $this->editingRowSelectOptions = [];
            $this->showSampleRowEditModal = false;
            $this->showTrfEditModal = false;
            $this->showTrfViewModal = true;

            if ($slide === 'samples' && $sampleRowIndex !== null) {
                $this->expandTrfSample($sampleRowIndex);
            }

            $this->dispatch('trf-view-modal-opened');
        } catch (\Throwable $exception) {
            report($exception);
            $this->closeTrfViewer();
            session()->flash(
                'request_view_message',
                app()->isProduction()
                    ? 'Unable to open the request view. Please contact support if this continues.'
                    : 'Unable to open the request view: '.$exception->getMessage(),
            );
        }
    }

    public function closeTrfViewer(): void
    {
        $this->dispatch('trf-view-modal-closed');
        $this->showTrfViewModal = false;
        $this->trfEditSlide = 'customer';
        $this->trfEditExpandedSampleIndex = null;
        $this->editingRowIndex = null;
        $this->editingRowFields = [];
        $this->editingRowSelectOptions = [];
    }

    public function setTrfViewSlide(string $slide): void
    {
        if (! in_array($slide, ['customer', 'collection', 'samples'], true)) {
            return;
        }

        $this->trfEditSlide = $slide;
        $this->dispatch('trf-view-modal-opened');
    }

    public function setTrfEditSlide(string $slide): void
    {
        if (! in_array($slide, ['customer', 'collection', 'samples'], true)) {
            return;
        }

        $this->persistExpandedSampleDraft();
        $this->trfEditSlide = $slide;
        $this->dispatch('trf-edit-modal-opened');
    }

    public function updatedTrfEditCompanyUnitId(?string $unitId): void
    {
        $editService = app(SubmissionFormInstanceTrfEditService::class);
        $this->trfEditSamplePointOptions = $editService->samplePointOptions($unitId);
        $currentLocation = (string) ($this->trfEditCollectionFields['sampling_location'] ?? '');
        $validIds = collect($this->trfEditSamplePointOptions)->pluck('value')->all();
        if ($currentLocation !== '' && ! in_array($currentLocation, $validIds, true)) {
            $this->trfEditCollectionFields['sampling_location'] = '';
        }

        $this->refreshTrfEditContactOptions($editService);
        $validContactIds = collect($this->trfEditContactOptions)->pluck('id')->all();
        if ($this->trfEditContactId !== '' && ! in_array($this->trfEditContactId, $validContactIds, true)) {
            $this->trfEditContactId = '';
            $this->trfEditContactName = '';
            $this->trfEditContactEmail = '';
            $this->trfEditContactPhone = '';
        }

        $this->dispatch('trf-edit-modal-opened');
    }

    public function updatedTrfEditContactId(?string $contactId): void
    {
        $editService = app(SubmissionFormInstanceTrfEditService::class);
        $option = collect($this->trfEditContactOptions)->firstWhere('id', (string) ($contactId ?? ''))
            ?? $editService->contactOptionById($contactId);

        if ($option === null) {
            $this->trfEditContactName = '';
            $this->trfEditContactEmail = '';
            $this->trfEditContactPhone = '';

            return;
        }

        $this->trfEditContactName = (string) ($option['text'] ?? '');
        $this->trfEditContactEmail = (string) ($option['email'] ?? '');
        $this->trfEditContactPhone = (string) ($option['phone'] ?? '');
    }

    public function expandTrfSample(int $rowIndex, bool $open = true): void
    {
        $this->persistExpandedSampleDraft();

        if (! array_key_exists($rowIndex, $this->trfEditSampleDrafts)) {
            return;
        }

        if (! $open) {
            if ($this->trfEditExpandedSampleIndex === $rowIndex) {
                $this->trfEditExpandedSampleIndex = null;
                $this->editingRowIndex = null;
                $this->editingRowFields = [];
                $this->editingRowSelectOptions = [];
            }

            $this->dispatch('trf-sample-card-toggled', rowIndex: $rowIndex, open: false);

            return;
        }

        if ($this->trfEditExpandedSampleIndex === $rowIndex) {
            return;
        }

        if ($this->trfEditExpandedSampleIndex !== null) {
            $this->dispatch('trf-sample-card-toggled', rowIndex: $this->trfEditExpandedSampleIndex, open: false);
        }

        $this->trfEditExpandedSampleIndex = $rowIndex;
        $this->editingRowIndex = $rowIndex;
        $this->editingRowFields = $this->trfEditSampleDrafts[$rowIndex];
        $this->editingRowFieldDefinitions = $this->trfEditSampleDefinitions;

        foreach ($this->trfEditSampleDefinitions as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }
            if (($field['element_type'] ?? '') === 'checkbox' || in_array($name, ['test_requirements', 'test_category'], true)) {
                $this->ensureCheckboxOptionKeys($name, $field['options'] ?? []);
            }
        }

        $this->editingRowSelectOptions = $this->buildSampleRowSelectOptions($this->editingRowFields);
        $this->dispatch('trf-sample-card-toggled', rowIndex: $rowIndex, open: true);
        $this->dispatch('trf-sample-card-opened', rowIndex: $rowIndex);
    }

    public function closeTrfEditor(): void
    {
        $this->dispatch('trf-edit-modal-closed');
        $this->showTrfEditModal = false;
        $this->showTrfViewModal = false;
        $this->trfEditSlide = 'customer';
        $this->trfEditExpandedSampleIndex = null;
        $this->trfEditClientName = '';
        $this->trfEditCompanyUnitId = '';
        $this->trfEditContactId = '';
        $this->trfEditContactName = '';
        $this->trfEditContactEmail = '';
        $this->trfEditContactPhone = '';
        $this->trfEditCustomerId = null;
        $this->trfEditUnitOptions = [];
        $this->trfEditContactOptions = [];
        $this->trfEditSamplePointOptions = [];
        $this->trfEditCollectionDefinitions = [];
        $this->trfEditCollectionFields = [];
        $this->trfEditSampleSummaries = [];
        $this->trfEditSampleDrafts = [];
        $this->trfEditSampleDefinitions = [];
        $this->closeSampleRowEditor();
    }

    public function saveTrfEditor(): void
    {
        $this->authorizeSampleRowEdit(auth()->user());

        $presenter = $this->requestViewPresenter();
        if (! $presenter->canEditSampleRows()) {
            session()->flash('request_view_message', 'Request details can no longer be edited after reception.');
            $this->closeTrfEditor();

            return;
        }

        $this->persistExpandedSampleDraft();

        $editService = app(SubmissionFormInstanceTrfEditService::class);
        $this->instance = $editService->saveAll(
            $this->instance,
            [
                'company_unit_id' => $this->trfEditCompanyUnitId,
                'contact_id' => $this->trfEditContactId,
                'contact_name' => $this->trfEditContactName,
                'contact_email' => $this->trfEditContactEmail,
                'contact_phone' => $this->trfEditContactPhone,
            ],
            $this->trfEditCollectionFields,
            $this->trfEditSampleDrafts,
        );

        $this->commercialEnquiry = $this->instance->sampleSubmissionRequest
            ?? SampleSubmissionRequest::query()
                ->with(['currentQuotation', 'contact', 'customer'])
                ->where('submission_form_instance_id', $this->instance->id)
                ->first();

        $this->closeTrfEditor();
        session()->flash('request_view_message', 'Request details updated successfully.');
    }

    public function closeSampleRowEditor(): void
    {
        $this->dispatch('sample-row-edit-modal-closed');
        $this->showSampleRowEditModal = false;
        $this->editingRowIndex = null;
        $this->editingRowFields = [];
        $this->editingRowFieldDefinitions = [];
        $this->editingRowSelectOptions = [];
        $this->editingRowCollectionSamplingLocation = '';
    }

    private function persistExpandedSampleDraft(): void
    {
        if ($this->trfEditExpandedSampleIndex === null) {
            return;
        }

        $this->trfEditSampleDrafts[$this->trfEditExpandedSampleIndex] = $this->editingRowFields;
    }

    private function refreshTrfEditContactOptions(SubmissionFormInstanceTrfEditService $editService): void
    {
        $this->trfEditContactOptions = $editService->contactOptions(
            $this->trfEditCustomerId,
            $this->trfEditCompanyUnitId !== '' ? $this->trfEditCompanyUnitId : null,
        );

        if ($this->trfEditContactId === '') {
            return;
        }

        $selected = collect($this->trfEditContactOptions)->firstWhere('id', $this->trfEditContactId);
        if ($selected !== null) {
            return;
        }

        $fallback = $editService->contactOptionById($this->trfEditContactId);
        if ($fallback !== null) {
            array_unshift($this->trfEditContactOptions, $fallback);
        }
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    private function ensureCollectionCheckboxOptionKeys(string $fieldName, array $options): void
    {
        if (! is_array($this->trfEditCollectionFields[$fieldName] ?? null)) {
            $this->trfEditCollectionFields[$fieldName] = [];
        }

        foreach ($options as $optionValue => $optionLabel) {
            if (is_array($optionLabel) && isset($optionLabel['value'])) {
                $optionKey = (string) $optionLabel['value'];
            } elseif (is_string($optionValue) && ! is_numeric($optionValue)) {
                $optionKey = $optionValue;
            } elseif (is_string($optionLabel)) {
                $optionKey = $optionLabel;
            } else {
                continue;
            }

            if (! array_key_exists($optionKey, $this->trfEditCollectionFields[$fieldName])) {
                $this->trfEditCollectionFields[$fieldName][$optionKey] = false;
            }
        }
    }

    public function updated($property): void
    {
        if (! in_array($property, ['editingRowFields.sample_type_id', 'editingRowFields.analysis_type_id'], true)) {
            return;
        }

        $cleared = [];

        if ($property === 'editingRowFields.sample_type_id') {
            $this->editingRowFields['analysis_type_id'] = [];
            $this->editingRowFields['parameters'] = [];
            $cleared = ['analysis_type_id', 'parameters'];
        } elseif ($property === 'editingRowFields.analysis_type_id') {
            $this->editingRowFields['parameters'] = [];
            $cleared = ['parameters'];
        }

        $this->editingRowSelectOptions = $this->buildSampleRowSelectOptions($this->editingRowFields);

        $this->dispatch(
            'sample-row-select-options-refreshed',
            options: $this->editingRowSelectOptions,
            cleared: $cleared,
        );

        $this->skipRender();
    }

    public function saveSampleRow(): void
    {
        $this->authorizeSampleRowEdit(auth()->user());

        $presenter = $this->requestViewPresenter();
        if (! $presenter->canEditSampleRows()) {
            session()->flash('request_view_message', 'Sample rows can no longer be edited after reception.');
            $this->closeSampleRowEditor();

            return;
        }

        if ($this->editingRowIndex === null) {
            return;
        }

        $service = app(SubmissionFormInstanceSampleRowUpdateService::class);
        $this->instance = $service->updateRow(
            $this->instance,
            $this->editingRowIndex,
            $this->editingRowFields,
        );

        $this->commercialEnquiry = $this->instance->sampleSubmissionRequest
            ?? SampleSubmissionRequest::query()
                ->with(['currentQuotation', 'contact', 'customer'])
                ->where('submission_form_instance_id', $this->instance->id)
                ->first();

        $this->closeSampleRowEditor();
        session()->flash('request_view_message', 'Sample row updated successfully.');
    }

    public function isWaterTrf(): bool
    {
        $documentCode = trim((string) ($this->submissionForm->document_code ?? ''));

        return $documentCode === TrfDocumentCodeForSampleType::WATER;
    }

    /**
     * @return Collection<int, \App\ReportingUnit>
     */
    public function getReportingUnitsProperty(): Collection
    {
        return \App\ReportingUnit::query()->where('active', 1)->orderBy('name')->get();
    }

    /**
     * @return array<string, string>
     */
    public function waterTestRequirementOptions(): array
    {
        return [
            'microbiology' => 'Microbiology',
            'legionella' => 'Legionella',
            'chemistry' => 'Chemical Analysis',
        ];
    }

    /**
     * @param  list<array{id: string, name: string, label: string, element_type: string, required: bool, options: array<int|string, mixed>}>  $definitions
     * @return list<array{id: string, name: string, label: string, element_type: string, required: bool, options: array<int|string, mixed>}>
     */
    private function prepareSampleRowEditDefinitions(array $definitions): array
    {
        foreach ($definitions as $index => $definition) {
            $name = (string) ($definition['name'] ?? '');
            if ($name === 'state_of_sample' && ($definition['options'] ?? []) !== []) {
                $definitions[$index]['element_type'] = 'select';
            }
        }

        if (! $this->isWaterTrf()) {
            return $definitions;
        }

        $hasTestRequirements = false;

        foreach ($definitions as $index => $definition) {
            if (($definition['name'] ?? '') !== 'test_requirements') {
                continue;
            }

            $hasTestRequirements = true;

            if (($definition['options'] ?? []) === []) {
                $definitions[$index]['options'] = $this->waterTestRequirementOptions();
            }

            $definitions[$index]['label'] = 'Test requirement';
        }

        if (! $hasTestRequirements) {
            $definitions[] = [
                'id' => '',
                'name' => 'test_requirements',
                'label' => 'Test requirement',
                'element_type' => 'checkbox',
                'required' => false,
                'options' => $this->waterTestRequirementOptions(),
            ];
        }

        return $definitions;
    }

    /**
     * @param  array<int|string, mixed>  $options
     */
    private function ensureCheckboxOptionKeys(string $fieldName, array $options): void
    {
        if (! is_array($this->editingRowFields[$fieldName] ?? null)) {
            $this->editingRowFields[$fieldName] = [];
        }

        foreach ($options as $optionValue => $optionLabel) {
            if (is_array($optionLabel) && isset($optionLabel['value'])) {
                $optionKey = (string) $optionLabel['value'];
            } elseif (is_string($optionValue) && ! is_numeric($optionValue)) {
                $optionKey = $optionValue;
            } elseif (is_string($optionLabel)) {
                $optionKey = $optionLabel;
            } else {
                continue;
            }

            if (! array_key_exists($optionKey, $this->editingRowFields[$fieldName])) {
                $this->editingRowFields[$fieldName][$optionKey] = false;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $rowFields
     * @return array<string, list<array{value: mixed, label: string}>>
     */
    private function buildSampleRowSelectOptions(array $rowFields): array
    {
        $optionsService = app(PortalDynamicOptionsService::class);
        $customerId = trim((string) ($this->instance->crm_customer_id ?? ''));
        $sampleTypeIds = $this->normalizeRowSelectValues($rowFields['sample_type_id'] ?? null);
        $analysisTypeIds = $this->normalizeRowSelectValues($rowFields['analysis_type_id'] ?? null);

        $resolve = function (string $elementType, array $extra = []) use ($optionsService, $customerId, $sampleTypeIds, $analysisTypeIds): array {
            $request = HttpRequest::create('/', 'GET', array_merge([
                'element_type' => $elementType,
                'client_id' => $customerId !== '' ? $customerId : null,
                'sample_type_id' => $sampleTypeIds !== [] ? $sampleTypeIds : null,
                'analysis_type_id' => $analysisTypeIds !== [] ? $analysisTypeIds : null,
                'submission_form_id' => $this->submissionForm->id,
            ], $extra));

            try {
                $payload = $optionsService->resolve($request, $customerId !== '' ? $customerId : null);
            } catch (\Illuminate\Validation\ValidationException) {
                return [];
            }

            return is_array($payload['options'] ?? null) ? $payload['options'] : [];
        };

        return [
            'sample_type_id' => $this->mergeSelectedSelectOptions(
                $resolve('sample_type_select'),
                $this->normalizeRowSelectValues($rowFields['sample_type_id'] ?? null),
                fn (string $id): ?string => \App\SampleType::query()->whereKey($id)->value('name'),
            ),
            'analysis_type_id' => $this->mergeSelectedSelectOptions(
                $resolve('analysis_type_select'),
                $this->normalizeRowSelectValues($rowFields['analysis_type_id'] ?? null),
                fn (string $id): ?string => \App\AnalysisType::query()->whereKey($id)->value('name'),
            ),
            'parameters' => $this->enrichParameterSelectOptions(
                $this->mergeSelectedSelectOptions(
                    $resolve('analysis_elements_select'),
                    $this->normalizeRowSelectValues($rowFields['parameters'] ?? null),
                    fn (string $id): ?string => app(\App\Services\Lab\AnalysisReferenceLabelResolver::class)->resolveToken($id),
                )
            ),
            'sampling_point' => $resolve('sample_point_select'),
        ];
    }

    /**
     * Enrich parameter options with report display label + method/lab-section meta for multi-column Select2.
     *
     * @param  list<array{value: mixed, label: string}>  $options
     * @return list<array{value: mixed, label: string, meta?: string}>
     */
    private function enrichParameterSelectOptions(array $options): array
    {
        $ids = collect($options)
            ->map(fn (array $option): string => (string) ($option['value'] ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return $options;
        }

        $elements = \App\AnalysisElements::query()
            ->with(['analyte', 'mmethod', 'ltmethod', 'labSection'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy(fn ($element) => (string) $element->id);

        return collect($options)->map(function (array $option) use ($elements): array {
            $id = (string) ($option['value'] ?? '');
            $element = $elements->get($id);
            if ($element === null) {
                return $option;
            }

            $reportDisplay = trim((string) ($element->report_display_name ?? ''));
            if ($reportDisplay === '') {
                $reportDisplay = trim((string) ($element->analyte?->plainReportDisplay() ?? $element->analyte?->code ?? ''));
            }
            if ($reportDisplay === '') {
                $reportDisplay = trim((string) ($option['label'] ?? $id));
            }

            $method = trim((string) ($element->mmethod?->name ?? $element->ltmethod?->name ?? ''));
            $labSection = trim((string) ($element->labSection?->name ?? ''));

            $option['label'] = $reportDisplay;
            $option['meta_method'] = $method;
            $option['meta_lab'] = $labSection;

            return $option;
        })->values()->all();
    }

    /**
     * @return list<string>
     */
    private function normalizeRowSelectValues(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn ($item): string => trim((string) $item),
                $value,
            ), static fn (string $token): bool => $token !== ''));
        }

        $string = trim((string) $value);
        if ($string === '') {
            return [];
        }

        if (str_contains($string, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $string))));
        }

        return [$string];
    }

    /**
     * @param  list<array{value: mixed, label: string}>  $options
     * @param  list<string>  $selectedIds
     * @param  callable(string): ?string  $labelResolver
     * @return list<array{value: mixed, label: string}>
     */
    private function mergeSelectedSelectOptions(array $options, array $selectedIds, callable $labelResolver): array
    {
        $options = array_values(array_filter($options, is_array(...)));

        if ($selectedIds === []) {
            return $options;
        }

        $existing = collect($options)
            ->mapWithKeys(fn (array $option): array => [(string) ($option['value'] ?? '') => $option]);

        foreach ($selectedIds as $selectedId) {
            if ($existing->has($selectedId)) {
                continue;
            }

            $label = trim((string) ($labelResolver($selectedId) ?? ''));
            $options[] = [
                'value' => $selectedId,
                'label' => $label !== '' ? $label : $selectedId,
            ];
        }

        return $options;
    }

    private function requestViewPresenter(): RequestViewPagePresenter
    {
        return new RequestViewPagePresenter(
            instance: $this->instance,
            submissionForm: $this->submissionForm,
            commercialEnquiry: $this->commercialEnquiry,
            trfPdfUrl: $this->trfPdfUrl,
            canCreateSamples: false,
            linkedBatchesOutOfSyncWithForm: $this->linkedBatchesOutOfSyncWithForm,
            showSampleCollectionLabel: $this->shouldShowSampleCollectionLabel(),
            isTrfForm: $this->isTrfForm(),
        );
    }

    private function authorizeSampleRowEdit(?\App\User $user): void
    {
        $this->authorizeFormAccess($user);

        if ($user === null) {
            abort(403, 'You are not authorized to edit sample rows.');
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return;
        }

        if ($user->can('submission-forms.process') || $user->can('laboratory.permission')) {
            return;
        }

        abort(403, 'You are not authorized to edit sample rows.');
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

    public function syncRequestQuotation(): void
    {
        if ($this->commercialEnquiry === null) {
            $this->dispatch('notify', type: 'error', message: 'No commercial enquiry is linked to this request.');

            return;
        }

        try {
            $enquiry = app(\App\Services\Commercial\EnquiryQuotationContentSyncService::class)
                ->sync($this->commercialEnquiry);

            $this->commercialEnquiry = $enquiry;
            $this->instance = $this->instance->fresh([
                'values.element',
                'sampleSubmissionRequest.currentQuotation',
                'sampleSubmissionRequest.requestedAnalyses',
            ]) ?? $this->instance;

            $message = 'Request tests/parameters synced from quotation '
                .((string) ($enquiry->currentQuotation?->quote_number ?? '')).'.';

            if ($enquiry->quotation_content_stale_at !== null) {
                $message .= ' Quotation content changed since send — use Process enquiry to send again if needed.';
            }

            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: $exception->getMessage());
        }
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

    public function generateTrfPdf(TestRequestFormPdfService $service): void
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->isTrfForm()) {
            session()->flash('request_view_message', 'This form is not a test request form.');

            return;
        }

        $instance = $this->instance->fresh([
            'values.element',
            'submissionForm',
            'batches.samples',
        ]) ?? $this->instance;

        $service->generateAndStore($instance);
        $this->trfPdfUrl = $service->resolvePublicUrl($instance);
        $this->instance = $instance->fresh(['values.element', 'submissionForm', 'submittedBy', 'batches']) ?? $instance;
        session()->flash('request_view_message', 'TRF generated successfully.');
    }

    public function downloadTrfPdf(TestRequestFormPdfService $service): mixed
    {
        $this->authorizeFormAccess(auth()->user());

        if (! $this->isTrfForm()) {
            session()->flash('request_view_message', 'This form is not a test request form.');

            return null;
        }

        $instance = $this->instance->fresh([
            'values.element',
            'submissionForm',
            'batches.samples',
        ]) ?? $this->instance;

        return $service->download($instance);
    }

    public function sendTrfPdfToCustomer(TestRequestFormPdfService $service): void
    {
        $user = auth()->user();
        $this->authorizeFormAccess($user);

        if (! $this->isTrfForm()) {
            session()->flash('request_view_message', 'This form is not a test request form.');

            return;
        }

        $storagePath = $service->resolveStoragePath($this->instance);
        if (! Storage::disk('public')->exists($storagePath)) {
            session()->flash('request_view_message', 'Generate the TRF first.');
            $this->trfPdfUrl = null;

            return;
        }

        $contact = $this->commercialEnquiry?->contact;
        if ($contact === null || empty($contact->email)) {
            session()->flash('request_view_message', 'No customer contact email is available for this request.');

            return;
        }

        $file = Storage::disk('public')->path($storagePath);
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

        $quotationHeader = $this->commercialEnquiry?->currentQuotation;
        if ($quotationHeader !== null) {
            $quotationHeader->loadMissing('labSections');
        }
        $approvalService = app(QuotationApprovalService::class);
        $quotationApproverName = $approvalService->resolveApproverName($quotationHeader);

        $user = auth()->user();
        $canEditSampleRows = $presenter->canEditSampleRows()
            && $user !== null
            && (
                (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())
                || $user->can('submission-forms.process')
                || $user->can('laboratory.permission')
            );

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
            'contextRail' => $presenter->contextRail($formData, $sampleLines),
            'customerCard' => $sectionCards['customer'],
            'sectionCards' => $sectionCards['sections'],
            'testSamplesCard' => $presenter->testSamplesCard($sampleLines),
            'canEditSampleRows' => $canEditSampleRows,
            'nextStepActions' => $presenter->nextStepActions($boardStatus),
            'boardStatus' => $boardStatus,
            'boardTab' => $this->workflowBoardTab(),
            'quotationHeader' => $quotationHeader,
            'quotationApproverName' => $quotationApproverName,
        ]);
    }
}
