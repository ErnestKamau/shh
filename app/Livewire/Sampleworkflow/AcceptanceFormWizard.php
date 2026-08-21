<?php

namespace App\Livewire\Sampleworkflow;

use App\Livewire\Sampleworkflow\Concerns\ManagesSampleConfigurationWizard;
use App\Models\CRM\CustomerContact;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\CRM\ContactSignatureService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\CustomerAnalysisTypeStandardService;
use App\Services\Sampleworkflow\CustomerContactVerificationService;
use App\Services\Sampleworkflow\SampleReceivingCheckInService;
use App\Services\Sampleworkflow\TrfLabUseFieldsService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AcceptanceFormWizard extends Component
{
    use ManagesSampleConfigurationWizard;
    use WithFileUploads;

    public bool $showModal = false;

    public string $activeStep = 'sample_config';

    public ?string $submissionFormInstanceId = null;

    public ?string $submissionRequestId = null;

    public string $customerName = '';

    public string $trfNumber = '';

    public string $requestNumber = '';

    public ?string $requestDate = null;

    public int $numberOfSamples = 1;

    public string $modeOfWork = 'Normal';

    public ?string $dateOfSampling = null;

    /** @var list<array<string, mixed>> */
    public array $lines = [];

    public string $receivingPersonName = '';

    public string $receivingPersonSignature = '';

    public ?string $receivedAt = null;

    public string $selectedCustomerContactId = '';

    public string $customerSignerName = '';

    public string $customerSignature = '';

    public ?string $customerSignedAt = null;

    public bool $labCapable = true;

    public bool $clientInstructionClear = true;

    /** @var list<array{id: string, label: string}> */
    public array $customerContactOptions = [];

    /** @var array<string, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null> */
    public array $instancePhotoUploads = [];

    public bool $showLabSectionOnConfig = false;

    public bool $showMainStandardOnConfig = true;

    public bool $showSecondaryStandardOnConfig = false;

    public bool $showLabIdOnConfig = true;

    public bool $readOnlyLabIdOnConfig = true;

    public bool $showAssignedUserOnConfig = false;

    public bool $compactConfigTable = true;

    public bool $showSampleConditionOnConfig = true;

    public bool $showSampleDetailsOnConfig = true;

    public bool $showQuantityOnConfig = false;

    public bool $showParametersOnConfig = true;

    /** Lab section + analyst assignment happens on Sample Integrity Check, not here. */
    public bool $showParameterLabSectionsOnConfig = false;

    public bool $showSectionAnalystsOnConfig = false;

    public bool $showInstancePhotoOnConfig = true;

    public bool $showInstanceDisposalOnConfig = true;

    public bool $allowAddRemoveConfig = false;

    public bool $readOnlyConfigTypes = true;

    public bool $defaultExpandParameters = true;

    public bool $defaultExpandSampleDetails = false;

    /** receive_only = Ready for Reception handoff; accept_register = Integrity Accept creates job/samples. */
    public string $wizardMode = 'accept_register';

    public function mount(): void
    {
        $this->receivingPersonName = (string) (Auth::user()->name ?? '');
        $this->requestDate = now()->format('Y-m-d');
        $this->receivedAt = now()->format('Y-m-d\TH:i');
        $this->customerSignedAt = now()->format('Y-m-d');
    }

    public function isReceiveOnlyMode(): bool
    {
        return $this->wizardMode === 'receive_only';
    }

    /** @return list<array{key: string, label: string}> */
    public function getWizardStepsProperty(): array
    {
        if ($this->isReceiveOnlyMode()) {
            return [
                ['key' => 'sample_config', 'label' => 'Sample configuration'],
            ];
        }

        return [
            ['key' => 'sample_config', 'label' => 'Sample configuration'],
            ['key' => 'signatures', 'label' => 'Sign & accept'],
        ];
    }

    #[On('open-acceptance-wizard')]
    public function openWizard(
        ?string $submissionFormInstanceId = null,
        ?string $submissionRequestId = null,
        string $mode = 'accept_register',
    ): void {
        $this->resetWizard();
        $this->wizardMode = in_array($mode, ['receive_only', 'accept_register'], true)
            ? $mode
            : 'accept_register';
        $this->configureConfigVisibilityForMode();
        $this->submissionFormInstanceId = $submissionFormInstanceId ?: null;
        $this->submissionRequestId = $submissionRequestId ?: null;

        if (! $this->submissionFormInstanceId && ! $this->submissionRequestId) {
            $this->dispatch('notify', type: 'error', message: 'Select exactly one request or form row before continuing.');

            return;
        }

        $instance = $this->submissionFormInstanceId
            ? SubmissionFormInstance::query()->with('sampleSubmissionRequest')->find($this->submissionFormInstanceId)
            : null;

        $enquiry = $instance?->sampleSubmissionRequest;
        if ($enquiry === null && $this->submissionRequestId) {
            $enquiry = SampleSubmissionRequest::query()->find($this->submissionRequestId);
        }

        if ($instance !== null && app(CommercialEnquiryFromFormService::class)->isCommercialTestRequestForm($instance)) {
            $readiness = app(EnquiryReceptionReadinessService::class);

            if ($enquiry === null) {
                $this->dispatch('notify', type: 'error', message: 'No commercial enquiry is linked to this test request.');

                return;
            }

            $eligible = $this->isReceiveOnlyMode()
                ? $readiness->isEligibleForReceiveHandoff($enquiry, $instance)
                : $readiness->isEligibleForSampleAcceptance($enquiry, $instance);

            if (! $eligible) {
                $message = match (true) {
                    in_array((string) $enquiry->status, [
                        SampleSubmissionRequest::STATUS_REQUESTED,
                        SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
                    ], true) => 'Complete enquiry processing and send the quotation before continuing.',
                    (string) $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_SENT => 'Record customer acceptance on the request view page first.',
                    (string) $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW => 'Quotation is under review with the customer.',
                    (string) $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED => 'Record the customer PO on the request view page before continuing.',
                    $this->isReceiveOnlyMode() => 'This request is not ready to receive samples yet.',
                    default => 'This request is not ready for sample acceptance yet.',
                };
                $this->dispatch('notify', type: 'error', message: $message);

                return;
            }
        }

        $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
            $this->submissionRequestId,
            $this->submissionFormInstanceId
        );

        $this->crmCustomerId = $prefill['customer_id'];
        $this->customerName = $prefill['customer_name'];
        $this->requestDate = $prefill['request_date'] ?? now()->format('Y-m-d');
        $this->numberOfSamples = (int) ($prefill['number_of_samples'] ?? 1);
        $this->modeOfWork = (string) ($prefill['mode_of_work'] ?? 'Normal');
        $this->dateOfSampling = $prefill['date_of_sampling'];
        $this->hydrateReferenceNumbers($instance, $enquiry);
        $pricingService = app(AcceptanceFormPricingService::class);
        $quotationLines = $pricingService->deduplicateRedundantAnalysisTypeLines($prefill['lines']);
        $quotationLocked = (bool) ($prefill['quotation_locked'] ?? false);

        $instance = $this->submissionFormInstanceId
            ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
            : null;

        $configService = app(AcceptanceFormSampleConfigService::class);

        if ($quotationLocked && $enquiry !== null) {
            $this->sampleConfigs = $configService->prepareAcceptanceConfigsFromQuotation(
                $enquiry,
                $quotationLines,
                $instance,
            );
        } elseif (is_array($enquiry?->enquiry_sample_configuration) && $enquiry->enquiry_sample_configuration !== []) {
            $this->sampleConfigs = $configService->flattenToPerSampleConfigs($enquiry->enquiry_sample_configuration);
            $this->sampleConfigs = $configService->normalizeConfigsAnalysisTypeIds($this->sampleConfigs);
            $this->sampleConfigs = $configService->syncParameterKeysFromQuotationLines($this->sampleConfigs, $quotationLines);
            foreach ($this->sampleConfigs as $index => $config) {
                $this->sampleConfigs[$index]['parameter_keys'] = $configService->resolveElementIdsForAnalysisTypes(
                    is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [],
                    $configService->analysisTypeIdsFromConfig($config),
                );
            }
        } else {
            $prefillLines = $enquiry !== null
                ? $configService->buildPrefillLinesFromEnquiry($enquiry, collect($quotationLines)->map(function (array $line, int $index): array {
                    return array_merge($line, [
                        'row_index' => $line['row_index'] ?? $index,
                        'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                    ]);
                })->all())
                : collect($quotationLines)->map(function (array $line, int $index): array {
                    return [
                        'row_index' => $index,
                        'sample_type_id' => $line['sample_type_id'] ?? null,
                        'analysis_type_id' => $line['analysis_type_id'] ?? null,
                        'analysis_element_id' => $line['analysis_element_id'] ?? null,
                        'parameter_label' => $line['parameter_label'] ?? '',
                        'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                    ];
                })->values()->all();

            $this->sampleConfigs = $configService->buildConfigsFromPrefill($prefillLines, $instance);
            $defaultZoneId = $configService->resolveZoneIdFromInstance($instance);
            if ($defaultZoneId !== null) {
                foreach ($this->sampleConfigs as $index => $config) {
                    if (empty($config['zone_id'])) {
                        $this->sampleConfigs[$index]['zone_id'] = $defaultZoneId;
                    }
                }
            }
        }

        $this->sampleConfigs = app(CustomerAnalysisTypeStandardService::class)
            ->applyPrefillToConfigs(
                $this->sampleConfigs,
                $this->crmCustomerId !== null ? (string) $this->crmCustomerId : null,
            );

        $this->sampleConfigs = $configService->syncParameterLabSectionsForConfigs($this->sampleConfigs);
        $this->applySampleConfigAssignmentDefaults();
        $this->numberOfSamples = $configService->totalSampleCount($this->sampleConfigs);

        $this->lines = $this->mapQuotationLinesForAcceptance($quotationLines);

        $this->loadCustomerContactOptions($enquiry);

        if ($this->customerContactOptions === []) {
            $this->dispatch('notify', type: 'error', message: 'No active customer contacts found. Add a contact for this customer before accepting samples.');

            return;
        }

        $this->activeStep = 'sample_config';
        $this->showModal = true;
        $this->dispatch('acceptance-wizard-opened');
    }

    public function goToStep(string $step): void
    {
        if ($this->isReceiveOnlyMode()) {
            $this->activeStep = 'sample_config';

            return;
        }

        if (! in_array($step, ['sample_config', 'signatures'], true)) {
            return;
        }

        if ($step === 'signatures' && $this->activeStep === 'sample_config') {
            $this->saveSampleConfigAndContinue();

            return;
        }

        $this->activeStep = $step;

        if ($step === 'signatures') {
            $this->dispatch('acceptance-wizard-signatures-step');
        }
    }

    public function saveSampleConfigAndContinue(): void
    {
        if ($this->isReceiveOnlyMode()) {
            $this->submitReceiveSamples();

            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);

        try {
            $this->mergeInstancePhotoUploadsIntoConfigs();
            $this->sampleConfigs = $configService->syncParameterLabSectionsForConfigs($this->sampleConfigs);
            $configService->validateReceptionConfigs(
                $this->sampleConfigs,
                requireParameterAssignments: true,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? 'Complete all required sample configuration fields.';
            $this->dispatch('notify', type: 'error', message: $message);

            return;
        }

        $this->activeStep = 'signatures';
        $this->dispatch('acceptance-wizard-signatures-step');
    }

    /**
     * Ready for Reception: persist sample config and hand off to Integrity (no signatures / no job).
     */
    public function submitReceiveSamples(): void
    {
        if (! $this->isReceiveOnlyMode()) {
            $this->submitDualAccept();

            return;
        }

        $this->validate([
            'modeOfWork' => ['required', 'in:Normal,Express'],
            'lines' => ['required', 'array', 'min:1'],
        ]);

        $configService = app(AcceptanceFormSampleConfigService::class);

        try {
            $this->mergeInstancePhotoUploadsIntoConfigs();
            $this->sampleConfigs = $configService->syncParameterLabSectionsForConfigs($this->sampleConfigs);
            $configService->validateReceptionConfigs(
                $this->sampleConfigs,
                requireParameterAssignments: false,
                requireMainStandard: false,
            );
            $normalizedConfigs = $configService->normalizeConfigsForStorage(
                $this->sampleConfigs,
                $this->crmCustomerId !== null ? (string) $this->crmCustomerId : null,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? 'Complete all required sample configuration fields.';
            $this->dispatch('notify', type: 'error', message: $message);

            return;
        }

        $this->submitReceiveOnlyHandoff($normalizedConfigs);
    }

    public function goBackToSampleConfig(): void
    {
        $this->activeStep = 'sample_config';
    }

    public function updatedSelectedCustomerContactId(): void
    {
        $this->syncCustomerSignerNameFromContact();
        $this->hydrateCustomerSignatureFromContact();
    }

    public function clearSavedCustomerSignature(): void
    {
        $this->customerSignature = '';
        $this->dispatch('acceptance-wizard-signatures-step');
    }

    private function hydrateCustomerSignatureFromContact(): void
    {
        if ($this->selectedCustomerContactId === '') {
            return;
        }

        $dataUri = app(ContactSignatureService::class)
            ->dataUriForContact(CustomerContact::query()->find($this->selectedCustomerContactId));

        if ($dataUri !== '') {
            $this->customerSignature = $dataUri;
        }
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->resetWizard();
        $this->dispatch('acceptance-wizard-closed');
    }

    public function submitDualAccept(): void
    {
        $this->validate([
            'receivingPersonName' => ['required', 'string', 'max:255'],
            'receivingPersonSignature' => ['required', 'string'],
            'receivedAt' => ['required', 'date'],
            'modeOfWork' => ['required', 'in:Normal,Express'],
            'selectedCustomerContactId' => ['required', 'string'],
            'customerSignerName' => ['required', 'string', 'max:255'],
            'customerSignature' => ['required', 'string'],
            'customerSignedAt' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
        ], [
            'receivingPersonName.required' => 'Enter the receiving personnel name.',
            'receivingPersonSignature.required' => 'Provide the receiving personnel signature.',
            'selectedCustomerContactId.required' => 'Select the customer contact who is signing.',
            'customerSignerName.required' => 'Enter the customer contact name.',
            'customerSignature.required' => 'Provide the customer contact signature.',
        ]);

        $configService = app(AcceptanceFormSampleConfigService::class);

        $quotationPrefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
            $this->submissionRequestId,
            $this->submissionFormInstanceId,
        );
        $quotationLines = app(AcceptanceFormPricingService::class)
            ->deduplicateRedundantAnalysisTypeLines($quotationPrefill['lines'] ?? []);

        if (($quotationPrefill['quotation_locked'] ?? false) && $quotationLines !== []) {
            $this->sampleConfigs = $configService->syncParameterKeysFromQuotationLines($this->sampleConfigs, $quotationLines);
            foreach ($this->sampleConfigs as $index => $config) {
                $this->sampleConfigs[$index]['parameter_keys'] = $configService->resolveElementIdsForAnalysisTypes(
                    is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [],
                    $configService->analysisTypeIdsFromConfig($config),
                );
            }
        }

        try {
            $this->mergeInstancePhotoUploadsIntoConfigs();
            $normalizedConfigs = $configService->normalizeConfigsForStorage(
                $this->sampleConfigs,
                $this->crmCustomerId !== null ? (string) $this->crmCustomerId : null,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? 'Sample configuration is invalid.';
            $this->dispatch('notify', type: 'error', message: $message);

            return;
        }

        if ($this->isReceiveOnlyMode()) {
            $this->submitReceiveOnlyHandoff($normalizedConfigs);

            return;
        }

        $acceptanceLines = ($quotationPrefill['quotation_locked'] ?? false)
            ? $this->mapQuotationLinesForAcceptance($quotationLines)
            : $this->lines;

        $assignedAnalystIds = $configService->collectAssignedAnalystIds($normalizedConfigs);
        $analystSectionAssignments = $configService->collectAnalystLabSectionAssignments($normalizedConfigs);
        $leadAnalystId = $assignedAnalystIds[0] ?? null;

        $header = [
            'crm_customer_id' => $this->crmCustomerId,
            'customer_name' => $this->customerName,
            'request_date' => $this->requestDate,
            'number_of_samples' => $configService->totalSampleCount($normalizedConfigs),
            'mode_of_work' => $this->modeOfWork,
            'date_of_sampling' => $this->dateOfSampling,
            'sample_configuration_payload' => $normalizedConfigs,
            'lab_capable' => $this->labCapable,
            'client_instruction_clear' => $this->clientInstructionClear,
            'assigned_analyst_ids' => $assignedAnalystIds,
            'lead_analyst_id' => $leadAnalystId,
            'analyst_lab_section_assignments' => $analystSectionAssignments,
        ];

        try {
            $form = app(AcceptanceFormService::class)->acceptWithDualSignatures(
                $this->submissionFormInstanceId,
                $this->submissionRequestId,
                $header,
                $acceptanceLines,
                $this->receivingPersonName,
                $this->receivingPersonSignature,
                $this->receivedAt,
                $this->selectedCustomerContactId !== '' ? $this->selectedCustomerContactId : null,
                $this->customerSignerName !== '' ? $this->customerSignerName : null,
                $this->customerSignature !== '' ? $this->customerSignature : null,
                $this->customerSignedAt,
                auth()->id() ? (string) auth()->id() : null,
            );
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: $exception->getMessage());

            return;
        }

        app(CustomerAnalysisTypeStandardService::class)->syncPreferencesFromConfigs(
            $this->crmCustomerId !== null ? (string) $this->crmCustomerId : null,
            $normalizedConfigs,
        );

        $batchId = (string) ($form->sample_header_id ?? '');

        $this->closeWizard();

        if ($batchId === '') {
            $this->dispatch('notify', type: 'error', message: 'Samples were accepted but the job number could not be created. Check the acceptance form processing error.');

            return;
        }

        $batch = \App\SampleHeader::query()->find($batchId);
        $batchCode = (string) ($batch?->batch_code ?? '');

        $redirectUrl = route('view-batch-details', [
            'batch' => $batchId,
            'client' => 0,
            'portal' => 0,
            'status' => 'Samples In Lab',
        ]).'#samples';

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
        session()->flash('success', "Samples accepted. Job number {$batchCode} created and moved to Samples In Lab.");
    }

    /**
     * Ready for Reception → Sample Integrity Check (persist config, no job/sample creation).
     *
     * @param  list<array<string, mixed>>  $normalizedConfigs
     */
    private function submitReceiveOnlyHandoff(array $normalizedConfigs): void
    {
        $enquiry = null;
        if ($this->submissionRequestId) {
            $enquiry = SampleSubmissionRequest::query()->find($this->submissionRequestId);
        }

        if ($enquiry === null && $this->submissionFormInstanceId) {
            $enquiry = SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', $this->submissionFormInstanceId)
                ->first();
        }

        if ($enquiry === null) {
            $this->dispatch('notify', type: 'error', message: 'No commercial enquiry is linked to this request.');

            return;
        }

        $enquiry->enquiry_sample_configuration = $normalizedConfigs;
        $enquiry->save();

        $user = Auth::user();
        $instance = $this->submissionFormInstanceId
            ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
            : $enquiry->submissionFormInstance;

        if ($user instanceof User) {
            if ($instance !== null) {
                app(SampleReceivingCheckInService::class)->recordReceivingOfficer($user, $instance, $enquiry);
                app(TrfLabUseFieldsService::class)->applyAfterPhysicalReceive(
                    $instance,
                    $user,
                    $normalizedConfigs,
                );
            } else {
                $enquiry->received_by_full_name = (string) $user->name;
                $enquiry->received_by_date = now()->toDateString();
                $enquiry->received_by_time = now()->format('H:i');
                $enquiry->save();
            }
        }

        app(EnquiryReceptionReadinessService::class)->markSampleIntegrityCheck($enquiry);

        app(CustomerAnalysisTypeStandardService::class)->syncPreferencesFromConfigs(
            $this->crmCustomerId !== null ? (string) $this->crmCustomerId : null,
            $normalizedConfigs,
        );

        $this->closeWizard();

        $redirectUrl = route('sample-workflow', [
            'status' => 'Samples Receiving',
            'tab' => 'sample_integrity_check',
        ]);

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
        session()->flash('success', 'Samples received. Continue with Sample Integrity & Acceptance Check.');
    }

    private function mergeInstancePhotoUploadsIntoConfigs(): void
    {
        foreach ($this->sampleConfigs as $configIndex => $config) {
            $configId = (string) ($config['id'] ?? '');
            $photoKey = $this->instancePhotoUploadKey($configId);
            $upload = $this->instancePhotoUploads[$photoKey] ?? null;

            if ($upload !== null) {
                $path = $upload->store('sample_photos', 'public');
                $this->sampleConfigs[$configIndex]['photo_path'] = $path;
                unset($this->instancePhotoUploads[$photoKey]);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $quotationLines
     * @return list<array<string, mixed>>
     */
    private function mapQuotationLinesForAcceptance(array $quotationLines): array
    {
        $fallbackSampleTypeId = collect($this->sampleConfigs)
            ->pluck('sample_type_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->first();

        return collect($quotationLines)->map(function (array $line, int $index) use ($fallbackSampleTypeId): array {
            $sampleTypeId = $line['sample_type_id'] ?? null;
            if (($sampleTypeId === null || $sampleTypeId === '') && $fallbackSampleTypeId) {
                $sampleTypeId = $fallbackSampleTypeId;
            }

            return [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $line['sample_type_name'] ?? '',
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_type_name' => $line['analysis_type_name'] ?? '',
                'analysis_element_id' => $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? '',
                'unit_amount' => (float) ($line['unit_amount'] ?? 0),
                'number_of_samples' => 1,
                'is_approved' => (bool) ($line['is_approved'] ?? true),
                'sort_order' => $index,
            ];
        })->values()->all();
    }

    private function loadCustomerContactOptions(?SampleSubmissionRequest $enquiry): void
    {
        if (! $this->crmCustomerId) {
            $this->customerContactOptions = [];

            return;
        }

        $this->customerContactOptions = app(CustomerContactVerificationService::class)
            ->activeContactsForCustomer((string) $this->crmCustomerId);

        $defaultContactId = (string) ($enquiry?->crm_customer_contact_id ?? '');

        if ($defaultContactId !== '' && collect($this->customerContactOptions)->contains('id', $defaultContactId)) {
            $this->selectedCustomerContactId = $defaultContactId;
        } elseif ($this->customerContactOptions !== []) {
            $this->selectedCustomerContactId = $this->customerContactOptions[0]['id'];
        }

        $this->syncCustomerSignerNameFromContact();
        $this->hydrateCustomerSignatureFromContact();
    }

    private function syncCustomerSignerNameFromContact(): void
    {
        $match = collect($this->customerContactOptions)
            ->firstWhere('id', $this->selectedCustomerContactId);

        if ($match !== null) {
            $this->customerSignerName = (string) ($match['label'] ?? '');
        }
    }

    private function hydrateReferenceNumbers(
        ?SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
    ): void {
        $trfNumber = '';
        if ($instance !== null) {
            $trfNumber = trim((string) (
                $instance->getDocumentControlNumber()
                ?? $instance->canonicalFormNumber()
                ?? $instance->form_number
                ?? ''
            ));
        }

        $requestNumber = '';
        if ($enquiry !== null) {
            $requestNumber = trim((string) ($enquiry->formatted_number ?? ''));
            if ($requestNumber === '' && filled($enquiry->request_number)) {
                $requestNumber = 'REQ-'.str_pad((string) $enquiry->request_number, 4, '0', STR_PAD_LEFT);
            }
        }

        $this->trfNumber = $trfNumber;
        $this->requestNumber = $requestNumber;
    }

    private function applySampleConfigAssignmentDefaults(): void
    {
        $defaultLabId = \App\Lab::defaultLabId();
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            $this->sampleConfigs[$index]['lab_id'] = $defaultLabId;
            $this->sampleConfigs[$index] = $configService->syncParameterLabSections($this->sampleConfigs[$index]);
        }
    }

    private function resetWizard(): void
    {
        $this->activeStep = 'sample_config';
        $this->wizardMode = 'accept_register';
        $this->configureConfigVisibilityForMode();
        $this->submissionFormInstanceId = null;
        $this->submissionRequestId = null;
        $this->lines = [];
        $this->sampleConfigs = [];
        $this->instancePhotoUploads = [];
        $this->crmCustomerId = null;
        $this->customerName = '';
        $this->trfNumber = '';
        $this->requestNumber = '';
        $this->numberOfSamples = 1;
        $this->modeOfWork = 'Normal';
        $this->dateOfSampling = null;
        $this->requestDate = now()->format('Y-m-d');
        $this->receivingPersonName = (string) (Auth::user()->name ?? '');
        $this->receivingPersonSignature = '';
        $this->receivedAt = now()->format('Y-m-d\TH:i');
        $this->selectedCustomerContactId = '';
        $this->customerSignerName = '';
        $this->customerSignature = '';
        $this->customerSignedAt = now()->format('Y-m-d');
        $this->customerContactOptions = [];
        $this->labCapable = true;
        $this->clientInstructionClear = true;
    }

    private function configureConfigVisibilityForMode(): void
    {
        $isReceiveOnly = $this->isReceiveOnlyMode();

        // Receive Samples only captures condition / specification / sample details.
        // Lab-section and analyst assignment are owned by Sample Integrity Check.
        $this->showParametersOnConfig = ! $isReceiveOnly;
        $this->showParameterLabSectionsOnConfig = false;
        $this->showSectionAnalystsOnConfig = false;
        $this->defaultExpandSampleDetails = false;
        $this->defaultExpandParameters = ! $isReceiveOnly;
    }

    public function render()
    {
        return view('livewire.sampleworkflow.acceptance-form-wizard');
    }
}
