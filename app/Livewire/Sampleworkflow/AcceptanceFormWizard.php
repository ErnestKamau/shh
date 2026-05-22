<?php

namespace App\Livewire\Sampleworkflow;

use App\Livewire\Sampleworkflow\Concerns\ManagesManagerAssignments;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SampleReceivingDisclaimerService;
use App\Services\Sampleworkflow\SampleReceivingIntegrityService;
use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AcceptanceFormWizard extends Component
{
    use ManagesManagerAssignments;

    public bool $showModal = false;

    public string $activeStep = 'sample_config';

    public ?string $submissionFormInstanceId = null;

    public ?string $submissionRequestId = null;

    public ?string $acceptanceFormId = null;

    public string $status = '';

    public string $customerName = '';

    public ?string $requestDate = null;

    public int $numberOfSamples = 1;

    public string $modeOfWork = 'Normal';

    public ?string $dateOfSampling = null;

    /** @var list<array<string, mixed>> */
    public array $lines = [];

    /** @var list<array<string, mixed>> */
    public array $sampleConfigs = [];

    public ?string $crmCustomerId = null;

    public string $managerSignerName = '';

    public string $managerSignature = '';

    public ?string $managerSignedAt = null;

    public bool $showAddLineModal = false;

    public ?string $addLineSampleTypeId = null;

    public ?string $addLineAnalysisTypeId = null;

    public ?string $addLineParameterKey = null;

    /** @var list<array{id: string, name: string}> */
    public array $addLineSampleTypes = [];

    /** @var list<array{id: string, name: string}> */
    public array $addLineAnalysisTypes = [];

    /** @var list<array<string, mixed>> */
    public array $addLineParameters = [];

    public bool $addLineAllParametersSelected = false;

    public bool $addLineCanAddWholeAnalysisType = false;

    /** @var array<string, mixed> */
    public array $receiptNotificationForm = [];

    public bool $showRaiseDisclaimerOption = false;

    public bool $raiseSampleDisclaimer = false;

    public bool $requiresDisclaimerStep = false;

    /** @var list<array{id: string, label: string}> */
    public array $incompleteChecklistItems = [];

    /** @var array<string, mixed> */
    public array $disclaimerForm = [];

    public function mount(): void
    {
        $this->managerSignerName = (string) (Auth::user()->name ?? '');
        $this->requestDate = now()->format('Y-m-d');
    }

    #[On('open-acceptance-wizard')]
    public function openWizard(?string $submissionFormInstanceId = null, ?string $submissionRequestId = null): void
    {
        $this->resetWizard();
        $this->submissionFormInstanceId = $submissionFormInstanceId ?: null;
        $this->submissionRequestId = $submissionRequestId ?: null;

        if (!$this->submissionFormInstanceId && !$this->submissionRequestId) {
            $this->dispatch('notify', type: 'error', message: 'Select exactly one request or form row before accepting.');

            return;
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
        $pricingService = app(AcceptanceFormPricingService::class);

        $prefillLines = collect($pricingService->deduplicateRedundantAnalysisTypeLines($prefill['lines']))
            ->map(function (array $line, int $index) {
                return [
                    'line_no' => $index + 1,
                    'sample_type_id' => $line['sample_type_id'] ?? null,
                    'sample_type_name' => $line['sample_type_id'] ? optional(\App\SampleType::find($line['sample_type_id']))->name : '',
                    'analysis_type_id' => $line['analysis_type_id'] ?? null,
                    'analysis_type_name' => $line['analysis_type_id'] ? optional(\App\AnalysisType::find($line['analysis_type_id']))->name : '',
                    'analysis_element_id' => $line['analysis_element_id'] ?? null,
                    'parameter_label' => $line['parameter_label'] ?? '',
                    'unit_amount' => (float) ($line['unit_amount'] ?? 0),
                    'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                    'is_approved' => (bool) ($line['is_approved'] ?? true),
                    'sort_order' => $index,
                ];
            })->values()->all();

        $instance = $this->submissionFormInstanceId
            ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
            : null;

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->sampleConfigs = $configService->buildConfigsFromPrefill($prefillLines, $instance);
        $defaultZoneId = $configService->resolveZoneIdFromInstance($instance);
        if ($defaultZoneId !== null) {
            foreach ($this->sampleConfigs as $index => $config) {
                if (empty($config['zone_id'])) {
                    $this->sampleConfigs[$index]['zone_id'] = $defaultZoneId;
                }
            }
        }
        $this->lines = [];

        $this->syncReceivingIntegrityState();

        $this->showModal = true;
        $this->activeStep = 'sample_config';
        $this->refreshReceiptNotificationFormState();
    }

    /**
     * @return list<array{key: string, label: string, icon: string}>
     */
    public function getWizardStepsProperty(): array
    {
        $steps = [
            ['key' => 'sample_config', 'label' => 'Sample configuration', 'icon' => 'mdi-flask-outline'],
            ['key' => 'request', 'label' => 'Request & pricing', 'icon' => 'mdi-clipboard-list-outline'],
        ];

        if ($this->acceptanceFormId === null) {
            $steps[] = ['key' => 'receipt', 'label' => 'Sample receipt (GCLA 01)', 'icon' => 'mdi-file-document-outline'];
        }

        if ($this->requiresDisclaimerStep || ($this->acceptanceFormId && $this->hasDisclaimerOnForm())) {
            $steps[] = ['key' => 'disclaimer', 'label' => 'Sample disclaimer', 'icon' => 'mdi-file-alert-outline'];
        }

        if ($this->acceptanceFormId !== null) {
            $steps[] = ['key' => 'customer', 'label' => 'Customer', 'icon' => 'mdi-account-check-outline'];
        }

        if ($this->acceptanceFormId !== null
            && in_array($this->status, [
                AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
                AnalysisAcceptanceForm::STATUS_COMPLETED,
            ], true)) {
            $steps[] = ['key' => 'manager', 'label' => 'Lab manager', 'icon' => 'mdi-shield-check-outline'];
        }

        return $steps;
    }

    public function updatedRaiseSampleDisclaimer(bool $value): void
    {
        if (!$value) {
            $this->requiresDisclaimerStep = false;
        }
    }

    public function refreshReceiptNotificationFormState(): void
    {
        $service = app(SampleReceiptNotificationService::class);

        if ($this->acceptanceFormId) {
            $form = AnalysisAcceptanceForm::query()->find($this->acceptanceFormId);
            if ($form !== null) {
                $this->receiptNotificationForm = $service->resolveFormStateForAcceptanceForm($form);

                return;
            }
        }

        $pending = $service->loadPendingDraft($this->submissionFormInstanceId, $this->submissionRequestId);
        $fromSelection = $service->hydrateDefaultsFromWizardSelection(
            $this->submissionFormInstanceId,
            $this->submissionRequestId,
            [
                'client_or_authority_name' => $this->customerName,
                'number_of_samples' => $this->numberOfSamples,
            ]
        );

        $this->receiptNotificationForm = $service->applyReceivingPersonFromAuth(
            $service->mergePrefillWithUserDraft($fromSelection, $pending)
        );
    }

    public function saveReceiptNotificationDraft(): void
    {
        $service = app(SampleReceiptNotificationService::class);

        if ($this->acceptanceFormId) {
            $form = AnalysisAcceptanceForm::query()->find($this->acceptanceFormId);
            if ($form !== null) {
                $service->mergePayloadIntoAcceptanceForm($form, $this->receiptNotificationForm);
                $this->dispatch('notify', type: 'success', message: 'Receipt notification draft saved.');
                return;
            }
        }

        $service->savePendingDraft(
            $this->submissionFormInstanceId,
            $this->submissionRequestId,
            $this->receiptNotificationForm
        );
        $this->dispatch('notify', type: 'success', message: 'Receipt notification draft saved.');
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->resetWizard();
    }

    public function goToStep(string $stepKey): void
    {
        $allowed = collect($this->wizardSteps)->pluck('key')->all();
        if (!in_array($stepKey, $allowed, true)) {
            return;
        }

        if ($stepKey === 'disclaimer' && $this->acceptanceFormId === null && !$this->requiresDisclaimerStep) {
            return;
        }

        if ($stepKey === 'customer' && $this->acceptanceFormId === null) {
            return;
        }

        if ($stepKey === 'manager'
            && !in_array($this->status, [
                AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
                AnalysisAcceptanceForm::STATUS_COMPLETED,
            ], true)) {
            return;
        }

        $this->activeStep = $stepKey;

        if ($stepKey === 'receipt' && $this->acceptanceFormId === null) {
            $this->refreshReceiptNotificationFormState();
            $this->dispatch('acceptance-receipt-step-opened');
        }

        if ($stepKey === 'disclaimer' && $this->acceptanceFormId) {
            $form = AnalysisAcceptanceForm::query()->find($this->acceptanceFormId);
            if ($form !== null) {
                $this->disclaimerForm = app(SampleReceivingDisclaimerService::class)
                    ->resolveFormStateForAcceptanceForm($form);
            }
        }
    }

    public function continueToRequestStep(): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->sampleConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs);
        $configService->validateConfigs($this->sampleConfigs);

        if (!$this->crmCustomerId) {
            $this->dispatch('notify', type: 'error', message: 'Customer is required for pricing.');

            return;
        }

        $this->lines = $configService->expandConfigsToLines($this->sampleConfigs, $this->crmCustomerId);
        $this->numberOfSamples = $configService->totalSampleCount($this->sampleConfigs);
        $this->reindexLines();

        $this->validate([
            'customerName' => 'required|string|max:255',
            'requestDate' => 'required|date',
            'numberOfSamples' => 'required|integer|min:1',
            'modeOfWork' => 'required|in:Normal,Express',
            'dateOfSampling' => 'nullable|date',
            'lines' => 'required|array|min:1',
        ]);

        $this->refreshReceiptNotificationFormState();
        $this->activeStep = 'request';
    }

    public function backFromRequestStep(): void
    {
        $this->activeStep = 'sample_config';
    }

    public function continueToReceiptStep(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'requestDate' => 'required|date',
            'numberOfSamples' => 'required|integer|min:1',
            'modeOfWork' => 'required|in:Normal,Express',
            'dateOfSampling' => 'nullable|date',
            'lines' => 'required|array|min:1',
        ]);

        $this->refreshReceiptNotificationFormState();
        $this->activeStep = 'receipt';
        $this->dispatch('acceptance-receipt-step-opened');
    }

    public function backFromReceiptStep(): void
    {
        $this->activeStep = 'request';
    }

    public function addSampleConfig(): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);
        $empty = $configService->emptyConfig();
        $empty['zone_id'] = $configService->resolveZoneIdFromInstance(
            $this->submissionFormInstanceId
                ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
                : null
        );
        $this->sampleConfigs[] = $empty;
    }

    public function removeSampleConfig(string $configId): void
    {
        if (count($this->sampleConfigs) <= 1) {
            $this->dispatch('notify', type: 'warning', message: 'At least one sample configuration is required.');

            return;
        }

        $this->sampleConfigs = array_values(array_filter(
            $this->sampleConfigs,
            fn (array $config) => (string) ($config['id'] ?? '') !== $configId
        ));
    }

    public function updatedSampleConfigs(mixed $value, string $key): void
    {
        if (!preg_match('/^(\d+)\.number_of_samples$/', (string) $key, $matches)) {
            return;
        }

        $this->syncSampleConfigInstances((int) $matches[1]);
    }

    public function onConfigNumberOfSamplesChanged(int $index): void
    {
        $this->syncSampleConfigInstances($index);
    }

    private function syncSampleConfigInstances(int $index): void
    {
        if (!isset($this->sampleConfigs[$index])) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $count = max(1, (int) ($this->sampleConfigs[$index]['number_of_samples'] ?? 1));
        $this->sampleConfigs[$index]['number_of_samples'] = $count;
        $this->sampleConfigs[$index]['instances'] = $configService->syncInstances(
            $this->sampleConfigs[$index]['instances'] ?? [],
            $count
        );
    }

    public function onConfigSampleTypeChanged(int $index): void
    {
        if (!isset($this->sampleConfigs[$index])) {
            return;
        }

        $this->sampleConfigs[$index]['analysis_type_id'] = null;
        $this->sampleConfigs[$index]['parameter_keys'] = [];
    }

    public function onConfigAnalysisTypeChanged(int $index): void
    {
        if (!isset($this->sampleConfigs[$index])) {
            return;
        }

        $config = $this->sampleConfigs[$index];
        if (!$this->crmCustomerId || empty($config['analysis_type_id'])) {
            $this->sampleConfigs[$index]['parameter_keys'] = [];

            return;
        }

        $parameters = app(AcceptanceFormSampleConfigService::class)->parametersForConfig(
            $this->crmCustomerId,
            $config['sample_type_id'] ?? null,
            $config['analysis_type_id']
        );

        $this->sampleConfigs[$index]['parameter_keys'] = collect($parameters)
            ->pluck('analysis_element_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function toggleConfigParameter(string $configId, string $parameterKey): void
    {
        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $keys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            if (in_array($parameterKey, $keys, true)) {
                $keys = array_values(array_filter($keys, fn ($k) => (string) $k !== $parameterKey));
            } else {
                $keys[] = $parameterKey;
            }
            $this->sampleConfigs[$index]['parameter_keys'] = $keys;

            break;
        }
    }

    public function selectAllConfigParameters(string $configId): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if ((string) ($config['id'] ?? '') !== $configId) {
                continue;
            }

            $parameters = $configService->parametersForConfig(
                $this->crmCustomerId ?? '',
                $config['sample_type_id'] ?? null,
                $config['analysis_type_id'] ?? null
            );

            $this->sampleConfigs[$index]['parameter_keys'] = collect($parameters)
                ->pluck('analysis_element_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            break;
        }
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getConfigSampleTypesProperty(): array
    {
        if (!$this->crmCustomerId) {
            return [];
        }

        return app(AcceptanceFormSampleConfigService::class)
            ->sampleTypesForCustomer($this->crmCustomerId, $this->lines);
    }

    public function getConfigZonesProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->zonesForPicker();
    }

    public function getConfigSampleConditionsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->sampleConditionsForPicker();
    }

    public function getConfigStandardsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->standardsForPicker();
    }

    public function analysisTypesForConfigIndex(int $index): array
    {
        if (!$this->crmCustomerId || !isset($this->sampleConfigs[$index])) {
            return [];
        }

        $sampleTypeId = $this->sampleConfigs[$index]['sample_type_id'] ?? null;
        if (!$sampleTypeId) {
            return [];
        }

        return app(AcceptanceFormSampleConfigService::class)
            ->analysisTypesForSampleType($this->crmCustomerId, $sampleTypeId, $this->lines);
    }

    public function parametersForConfigIndex(int $index): array
    {
        if (!$this->crmCustomerId || !isset($this->sampleConfigs[$index])) {
            return [];
        }

        $config = $this->sampleConfigs[$index];

        return app(AcceptanceFormSampleConfigService::class)->parametersForConfig(
            $this->crmCustomerId,
            $config['sample_type_id'] ?? null,
            $config['analysis_type_id'] ?? null
        );
    }

    public function filteredParametersForConfigIndex(int $index): array
    {
        $parameters = $this->parametersForConfigIndex($index);
        $search = strtolower(trim((string) ($this->sampleConfigs[$index]['parameter_search'] ?? '')));

        if ($search === '') {
            return $parameters;
        }

        return array_values(array_filter(
            $parameters,
            fn (array $param) => str_contains(strtolower((string) ($param['label'] ?? '')), $search)
                || str_contains(strtolower((string) ($param['analysis_type_name'] ?? '')), $search)
        ));
    }

    public function submitStep1(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'requestDate' => 'required|date',
            'numberOfSamples' => 'required|integer|min:1',
            'modeOfWork' => 'required|in:Normal,Express',
            'dateOfSampling' => 'nullable|date',
            'lines' => 'required|array|min:1',
        ]);

        if ($this->raiseSampleDisclaimer) {
            $this->requiresDisclaimerStep = true;
            $this->disclaimerForm = app(SampleReceivingDisclaimerService::class)->hydrateDefaultsForWizard([
                'customer_name' => $this->customerName,
                'number_of_samples' => $this->numberOfSamples,
                'type_of_sample' => $this->sampleTypesSummary(),
            ]);
            $this->activeStep = 'disclaimer';
            $this->dispatch('acceptance-disclaimer-step-opened');

            return;
        }

        $this->finalizeAcceptanceCreation();
    }

    public function submitDisclaimerStep(): void
    {
        $disclaimerService = app(SampleReceivingDisclaimerService::class);
        $hasClaimantInWizard = trim((string) ($this->disclaimerForm['claimant_signature'] ?? '')) !== '';

        $this->validate($disclaimerService->wizardValidationRules($hasClaimantInWizard));

        $payload = $disclaimerService->mergeFormPayloads($this->disclaimerForm, []);
        if ($hasClaimantInWizard) {
            $payload['claimant_signature_source'] = 'lab';
            if (empty($payload['claimant_signed_at'])) {
                $payload['claimant_signed_at'] = now()->format('Y-m-d');
            }
        }

        $this->disclaimerForm = $payload;
        $this->finalizeAcceptanceCreation($payload);
    }

    public function backFromDisclaimerStep(): void
    {
        $this->activeStep = $this->acceptanceFormId === null ? 'receipt' : 'sample_config';
    }

    /**
     * @param  array<string, mixed>|null  $disclaimerPayload
     */
    private function finalizeAcceptanceCreation(?array $disclaimerPayload = null): void
    {
        $raisesDisclaimer = $disclaimerPayload !== null;

        $configService = app(AcceptanceFormSampleConfigService::class);
        $normalizedConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs);

        $form = app(AcceptanceFormService::class)->createFromStep1(
            $this->submissionFormInstanceId,
            $this->submissionRequestId,
            [
                'crm_customer_id' => $this->crmCustomerId,
                'customer_name' => $this->customerName,
                'request_date' => $this->requestDate,
                'number_of_samples' => $this->numberOfSamples,
                'mode_of_work' => $this->modeOfWork,
                'date_of_sampling' => $this->dateOfSampling,
                'sample_configuration_payload' => $normalizedConfigs,
            ],
            $this->lines,
            (string) Auth::id(),
            $raisesDisclaimer,
            $disclaimerPayload
        );

        $this->acceptanceFormId = $form->id;
        $this->status = $form->status;
        $this->requiresDisclaimerStep = $raisesDisclaimer;
        $this->activeStep = 'customer';

        app(SampleReceiptNotificationService::class)->persistAfterAcceptanceCreated(
            $form->fresh(),
            $this->receiptNotificationForm,
            $this->submissionFormInstanceId,
            $this->submissionRequestId
        );
        $this->refreshReceiptNotificationFormState();

        $this->dispatch('acceptance-form-created');
        session()->flash('success', 'Acceptance form sent to the customer for signing.');
    }

    public function submitManagerSign(): void
    {
        $this->validate(array_merge(
            $this->managerAssignmentValidationRules(),
            [
                'managerSignerName' => 'required|string|max:255',
                'managerSignature' => 'required|string',
            ]
        ), $this->managerAssignmentValidationMessages());

        $form = AnalysisAcceptanceForm::query()->findOrFail($this->acceptanceFormId);

        $completedForm = app(AcceptanceFormService::class)->recordManagerSignature(
            $form,
            $this->managerSignerName,
            $this->managerSignature,
            $this->managerSignedAt,
            $this->leadAnalystId,
            $this->technicalSignatoryId,
            $this->assignedAnalystIds,
        );

        $this->status = AnalysisAcceptanceForm::STATUS_COMPLETED;
        $this->closeWizard();

        $batchId = (string) ($completedForm->sample_header_id ?? '');
        $redirectUrl = $batchId !== ''
            ? route('view-batch-details', [
                'batch' => $batchId,
                'client' => 0,
                'portal' => 0,
                'status' => 'Samples In Lab',
            ]) . '#laboratory-acceptance-part-5'
            : route('sample-workflow', ['status' => 'Samples In Lab']);

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
        session()->flash('success', 'Acceptance form completed and batch moved to Samples In Lab.');
    }

    public function removeLine(int $index): void
    {
        if (!isset($this->lines[$index])) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->reindexLines();

        if ($this->showAddLineModal && $this->addLineAnalysisTypeId) {
            $this->refreshAddLineParameterOptions();
        }
    }

    public function openAddLineModal(): void
    {
        if (!$this->crmCustomerId) {
            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $this->addLineSampleTypes = $pricing->sampleTypesForAddLinePicker($this->crmCustomerId, $this->lines);
        $this->addLineAnalysisTypes = [];
        $this->addLineParameters = [];
        $this->addLineSampleTypeId = null;
        $this->addLineAnalysisTypeId = null;
        $this->addLineParameterKey = null;
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;
        $this->showAddLineModal = true;
    }

    public function updatedAddLineSampleTypeId(): void
    {
        if (!$this->crmCustomerId || !$this->addLineSampleTypeId) {
            $this->addLineAnalysisTypes = [];
            $this->addLineParameters = [];

            return;
        }

        $this->addLineAnalysisTypes = app(AcceptanceFormPricingService::class)
            ->analysisTypesForAddLinePicker($this->crmCustomerId, $this->addLineSampleTypeId, $this->lines);
        $this->addLineAnalysisTypeId = null;
        $this->addLineParameters = [];
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;
    }

    public function updatedAddLineAnalysisTypeId(): void
    {
        $this->refreshAddLineParameterOptions();
    }

    public function confirmAddLine(): void
    {
        if ($this->addLineAllParametersSelected) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'All parameters for this sample type and analysis type are already in the list below.'
            );

            return;
        }

        $isWholeAnalysisType = $this->addLineParameterKey === null || $this->addLineParameterKey === '';

        if ($isWholeAnalysisType) {
            if (!$this->addLineCanAddWholeAnalysisType) {
                $this->dispatch(
                    'notify',
                    type: 'warning',
                    message: 'This analysis type is already represented in the list below.'
                );

                return;
            }

            $this->appendAllParametersForAnalysisSelection();
            $this->showAddLineModal = false;

            return;
        }

        $parameter = collect($this->addLineParameters)->firstWhere('id', $this->addLineParameterKey);
        if (!$parameter) {
            return;
        }

        if ($this->isParameterAlreadyInTable(
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId,
            (string) $this->addLineParameterKey,
            $parameter['analysis_element_id'] ?? null
        )) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'That parameter is already in the list below.'
            );
            $this->refreshAddLineParameterOptions();

            return;
        }

        $this->appendLineFromParameter($parameter);
        $this->refreshAddLineParameterOptions();

        if ($this->addLineAllParametersSelected) {
            $this->showAddLineModal = false;
        }
    }

    public function getTotalAmountProperty(): float
    {
        return collect($this->lines)
            ->filter(fn (array $line) => !empty($line['is_approved']))
            ->sum(fn (array $line) => (float) ($line['unit_amount'] ?? 0) * (int) ($line['number_of_samples'] ?? 1));
    }

    /**
     * Lines grouped for display: sample type → analysis type → parameters.
     *
     * @return list<array{
     *     sample_type_id: ?string,
     *     sample_type_name: string,
     *     analysis_groups: list<array{
     *         analysis_type_id: ?string,
     *         analysis_type_name: string,
     *         items: list<array{index: int, line: array<string, mixed>}>,
     *         header_line_index: ?int
     *     }>
     * }>
     */
    public function getGroupedLinesProperty(): array
    {
        $sampleBuckets = [];
        $sampleOrder = [];
        $analysisOrder = [];

        foreach ($this->lines as $index => $line) {
            $sampleKey = (string) ($line['sample_type_id'] ?? '__ungrouped_sample__');
            $analysisKey = $sampleKey . '::' . (string) ($line['analysis_type_id'] ?? '__ungrouped_analysis__');

            if (!isset($sampleBuckets[$sampleKey])) {
                $sampleBuckets[$sampleKey] = [
                    'sample_type_id' => $line['sample_type_id'] ?? null,
                    'sample_type_name' => trim((string) ($line['sample_type_name'] ?? '')) !== ''
                        ? (string) $line['sample_type_name']
                        : 'Unspecified sample type',
                    'analysis_map' => [],
                ];
                $sampleOrder[] = $sampleKey;
                $analysisOrder[$sampleKey] = [];
            }

            if (!isset($sampleBuckets[$sampleKey]['analysis_map'][$analysisKey])) {
                $sampleBuckets[$sampleKey]['analysis_map'][$analysisKey] = [
                    'analysis_type_id' => $line['analysis_type_id'] ?? null,
                    'analysis_type_name' => trim((string) ($line['analysis_type_name'] ?? '')) !== ''
                        ? (string) $line['analysis_type_name']
                        : 'Unspecified analysis',
                    'items' => [],
                    'header_line_index' => null,
                ];
                $analysisOrder[$sampleKey][] = $analysisKey;
            }

            $item = ['index' => $index, 'line' => $line];

            if ($this->shouldDisplayLineAsParameter($line)) {
                $sampleBuckets[$sampleKey]['analysis_map'][$analysisKey]['items'][] = $item;
            } elseif ($this->isAnalysisTypeOnlyLine($line)) {
                $sampleBuckets[$sampleKey]['analysis_map'][$analysisKey]['header_line_index'] = $index;
            }
        }

        $grouped = [];
        foreach ($sampleOrder as $sampleKey) {
            $bucket = $sampleBuckets[$sampleKey];
            $analysisGroups = [];
            foreach ($analysisOrder[$sampleKey] as $analysisKey) {
                $analysisGroups[] = $bucket['analysis_map'][$analysisKey];
            }

            $grouped[] = [
                'sample_type_id' => $bucket['sample_type_id'],
                'sample_type_name' => $bucket['sample_type_name'],
                'analysis_groups' => $analysisGroups,
            ];
        }

        return $grouped;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function isAnalysisTypeOnlyLine(array $line): bool
    {
        return empty($line['analysis_element_id']);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function shouldDisplayLineAsParameter(array $line): bool
    {
        return !$this->isAnalysisTypeOnlyLine($line);
    }

    public function refreshAcceptanceStatus(): void
    {
        if (!$this->acceptanceFormId) {
            return;
        }

        $form = AnalysisAcceptanceForm::query()->find($this->acceptanceFormId);
        if (!$form) {
            return;
        }

        $this->status = $form->status;

        if ($form->status === AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            $this->activeStep = 'manager';
            $this->initializeManagerAssignmentFields($form);
        }

        if ($form->raises_sample_disclaimer) {
            $this->requiresDisclaimerStep = true;
            $this->disclaimerForm = app(SampleReceivingDisclaimerService::class)
                ->resolveFormStateForAcceptanceForm($form);
        }

        $this->refreshReceiptNotificationFormState();
    }

    public function render()
    {
        return view('livewire.sampleworkflow.acceptance-form-wizard');
    }

    private function reindexLines(): void
    {
        foreach ($this->lines as $index => &$line) {
            $line['line_no'] = $index + 1;
            $line['sort_order'] = $index;
        }
    }

    private function refreshAddLineParameterOptions(): void
    {
        $this->addLineParameters = [];
        $this->addLineParameterKey = null;
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;

        if (!$this->crmCustomerId || !$this->addLineSampleTypeId || !$this->addLineAnalysisTypeId) {
            return;
        }

        $allParameters = app(AcceptanceFormPricingService::class)->parametersForAddLineSelection(
            $this->crmCustomerId,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $this->addLineCanAddWholeAnalysisType = $this->canAddWholeAnalysisType(
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $this->addLineParameters = $this->filterParametersNotInTable(
            $allParameters,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $this->addLineAllParametersSelected = empty($this->addLineParameters)
            && !$this->addLineCanAddWholeAnalysisType;
    }

    /**
     * @param  list<array<string, mixed>>  $parameters
     * @return list<array<string, mixed>>
     */
    private function filterParametersNotInTable(
        array $parameters,
        ?string $sampleTypeId,
        ?string $analysisTypeId
    ): array {
        return array_values(array_filter(
            $parameters,
            fn (array $param) => !$this->isParameterAlreadyInTable(
                $sampleTypeId,
                $analysisTypeId,
                !empty($param['analysis_element_id']) ? (string) $param['id'] : null,
                $param['analysis_element_id'] ?? null
            )
        ));
    }

    private function canAddWholeAnalysisType(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return false;
        }

        if ($this->hasElementLinesForAnalysis($sampleTypeId, $analysisTypeId)) {
            return false;
        }

        return !$this->isWholeAnalysisTypeInTable($sampleTypeId, $analysisTypeId);
    }

    private function isWholeAnalysisTypeInTable(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        return collect($this->lines)->contains(
            fn (array $line) => $this->lineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && empty($line['analysis_element_id'])
        );
    }

    private function hasElementLinesForAnalysis(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        return collect($this->lines)->contains(
            fn (array $line) => $this->lineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && !empty($line['analysis_element_id'])
        );
    }

    private function isParameterAlreadyInTable(
        ?string $sampleTypeId,
        ?string $analysisTypeId,
        ?string $parameterKey,
        ?string $analysisElementId = null
    ): bool {
        if ($parameterKey === null || $parameterKey === '') {
            return $this->isWholeAnalysisTypeInTable($sampleTypeId, $analysisTypeId)
                || $this->hasElementLinesForAnalysis($sampleTypeId, $analysisTypeId);
        }

        $elementId = $analysisElementId ?? $parameterKey;

        return collect($this->lines)->contains(
            fn (array $line) => $this->lineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && (string) ($line['analysis_element_id'] ?? '') === (string) $elementId
        );
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function lineMatchesSampleAndAnalysis(
        array $line,
        ?string $sampleTypeId,
        ?string $analysisTypeId
    ): bool {
        return (string) ($line['sample_type_id'] ?? '') === (string) ($sampleTypeId ?? '')
            && (string) ($line['analysis_type_id'] ?? '') === (string) ($analysisTypeId ?? '');
    }

    private function resetWizard(): void
    {
        $this->activeStep = 'sample_config';
        $this->submissionFormInstanceId = null;
        $this->submissionRequestId = null;
        $this->acceptanceFormId = null;
        $this->status = '';
        $this->lines = [];
        $this->sampleConfigs = [];
        $this->crmCustomerId = null;
        $this->showAddLineModal = false;
        $this->managerSignature = '';
        $this->managerSignedAt = now()->format('Y-m-d');
        $this->resetManagerAssignmentFields();
        $this->receiptNotificationForm = SampleReceiptNotificationService::emptyForm();
        $this->showRaiseDisclaimerOption = false;
        $this->raiseSampleDisclaimer = false;
        $this->requiresDisclaimerStep = false;
        $this->incompleteChecklistItems = [];
        $this->disclaimerForm = SampleReceivingDisclaimerService::emptyForm();
    }

    private function syncReceivingIntegrityState(): void
    {
        $assessment = app(SampleReceivingIntegrityService::class)
            ->assessFormInstance($this->submissionFormInstanceId);

        $this->showRaiseDisclaimerOption = $assessment['incomplete'];
        $this->incompleteChecklistItems = $assessment['items'];
        $this->raiseSampleDisclaimer = false;
        $this->requiresDisclaimerStep = false;
    }

    private function hasDisclaimerOnForm(): bool
    {
        if (!$this->acceptanceFormId) {
            return $this->requiresDisclaimerStep;
        }

        $form = AnalysisAcceptanceForm::query()->find($this->acceptanceFormId);

        return $form?->raises_sample_disclaimer ?? false;
    }

    private function sampleTypesSummary(): string
    {
        $names = collect($this->lines)
            ->pluck('sample_type_name')
            ->filter(fn ($name) => trim((string) $name) !== '')
            ->unique()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : '';
    }

    private function appendAllParametersForAnalysisSelection(): void
    {
        if (!$this->crmCustomerId || !$this->addLineSampleTypeId || !$this->addLineAnalysisTypeId) {
            return;
        }

        $parameters = app(AcceptanceFormPricingService::class)->parametersForAddLineSelection(
            $this->crmCustomerId,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $toAdd = $this->filterParametersNotInTable(
            $parameters,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        if ($toAdd === []) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'All parameters for this sample type and analysis type are already in the list below.'
            );

            return;
        }

        foreach ($toAdd as $parameter) {
            $this->appendLineFromParameter($parameter);
        }

        $this->lines = app(AcceptanceFormPricingService::class)
            ->deduplicateRedundantAnalysisTypeLines($this->lines);
        $this->reindexLines();
    }

    /**
     * @param  array<string, mixed>  $parameter
     */
    private function appendLineFromParameter(array $parameter): void
    {
        $this->lines[] = [
            'line_no' => count($this->lines) + 1,
            'sample_type_id' => $parameter['sample_type_id'] ?? $this->addLineSampleTypeId,
            'sample_type_name' => $parameter['sample_type_name'] ?? optional(\App\SampleType::find($this->addLineSampleTypeId))->name ?? '',
            'analysis_type_id' => $parameter['analysis_type_id'] ?? $this->addLineAnalysisTypeId,
            'analysis_type_name' => $parameter['analysis_type_name'] ?? optional(\App\AnalysisType::find($this->addLineAnalysisTypeId))->name ?? '',
            'analysis_element_id' => $parameter['analysis_element_id'] ?? null,
            'parameter_label' => $parameter['label'] ?? '',
            'unit_amount' => (float) ($parameter['unit_amount'] ?? 0),
            'number_of_samples' => 1,
            'is_approved' => true,
            'sort_order' => count($this->lines),
        ];

        $this->lines = app(AcceptanceFormPricingService::class)
            ->deduplicateRedundantAnalysisTypeLines($this->lines);
        $this->reindexLines();
    }
}
