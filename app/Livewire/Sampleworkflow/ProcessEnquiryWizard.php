<?php

namespace App\Livewire\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Livewire\Sampleworkflow\Concerns\ManagesSampleConfigurationWizard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Commercial\CommercialEnquiryCustomerResolver;
use App\Services\Commercial\EnquiryReviewDisplayService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class ProcessEnquiryWizard extends Component
{
    use ManagesSampleConfigurationWizard;

    public bool $showModal = false;

    public bool $useReceivingTheme = false;

    public string $activeStep = 'review';

    public ?string $enquiryId = null;

    public string $enquiryNotes = '';

    public string $customerFeedbackNotes = '';

    public string $customerName = '';

    public string $requestReference = '';

    public string $enquiryStatus = '';

    public string $sourceChannel = '';

    /** @var list<array{label: string, value: string}> */
    public array $collectionDataRows = [];

    /** @var list<array{value: string, label: string, checked: bool}> */
    public array $statementOfConformityOptions = [];

    public string $headerSampleType = '';

    /** @var list<array<string, string>> */
    public array $displaySampleRows = [];

    /** @var list<array{label: string}> */
    public array $requestedTests = [];

    public ?string $submissionFormInstanceId = null;

    public ?string $submissionFormId = null;

    /** @var list<array<string, mixed>> */
    public array $sampleLines = [];

    /** @var list<array<string, mixed>> */
    public array $lines = [];

    /** @var array<string, bool> */
    public array $lineSubcontractOverrides = [];

    public ?string $quotationHeaderId = null;

    public string $quoteNumber = '';

    public bool $sendPortal = true;

    public bool $sendEmail = false;

    public string $statusMessage = '';

    public string $statusLevel = 'info';

    public bool $statusAutoDismiss = false;

    public bool $pdfGenerated = false;

    public bool $quotationSent = false;

    public bool $quotationManuallyEdited = false;

    public bool $quotationBuilt = false;

    public bool $showBuildQuotationModal = false;

    /** Step 3 mode: build_new | use_existing */
    public string $quotationMode = 'build_new';

    /** Bumps wire:key so Step 3 remounts when quotation mode is re-applied. */
    public int $quotationModeRenderKey = 0;

    public ?string $selectedExistingQuotationId = null;

    public string $existingQuotationSearch = '';

    public bool $showExistingQuotationDropdown = false;

    /** @var list<array{id: string, label: string, quote_number: string, expiring_date: string, total: string}> */
    public array $existingQuotationOptions = [];

    /** Soft mismatch warning when using an existing quotation. */
    public string $quotationMismatchWarning = '';

    /** Hides the Condition of sample column on the sample config table (Process Enquiry does not need it). */
    public bool $showSampleConditionOnConfig = false;

    public bool $showLabSectionOnConfig = false;

    public bool $showMainStandardOnConfig = false;

    public bool $showSecondaryStandardOnConfig = false;

    public bool $showLabIdOnConfig = false;

    public bool $showSampleDetailsOnConfig = false;

    public bool $showQuantityOnConfig = false;

    public bool $showParametersOnConfig = true;

    public bool $allowAddRemoveConfig = true;

    public bool $readOnlyConfigTypes = false;

    public bool $defaultExpandParameters = true;

    /** Process Enquiry sample config uses the tags (Select2-style) parameter picker. */
    public string $parameterPickerView = 'tags';

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

    public function getWizardStepsProperty(): array
    {
        return [
            ['key' => 'review', 'label' => 'Review request'],
            ['key' => 'sample_config', 'label' => 'Sample configuration'],
            ['key' => 'pricing', 'label' => 'Parameters & pricing'],
        ];
    }

    /** @return array{sub_total: float, tax: float, total: float} */
    public function getPricingTotalsProperty(): array
    {
        $subTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($this->lines as $line) {
            $qty = $this->linePhysicalSampleCount($line);
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $taxRate = (float) ($line['tax'] ?? 0);
            $extended = $qty * $unitPrice;
            $subTotal += $extended;

            if ($taxRate > 0) {
                $taxTotal += ($taxRate / 100) * $extended;
            }
        }

        return [
            'sub_total' => $subTotal,
            'tax' => $taxTotal,
            'total' => $subTotal + $taxTotal,
        ];
    }

    public function getPhysicalSampleCountProperty(): int
    {
        return app(AcceptanceFormSampleConfigService::class)->totalSampleCount($this->sampleConfigs);
    }

    public function getTaxRateProperty(): float
    {
        return app(QuotationLineTaxResolver::class)->activeTaxRegimePercent();
    }

    public function getCurrencyDisplayProperty(): string
    {
        $header = $this->resolveQuotationHeader();
        if ($header === null) {
            return '';
        }

        $header->loadMissing('currency');

        return (string) ($header->currency?->code ?? $header->currency?->name ?? '');
    }

    #[On('process-enquiry-open')]
    public function openWizard(string $enquiryId): void
    {
        $enquiry = SampleSubmissionRequest::query()
            ->with([
                'customer',
                'contact',
                'requestedAnalyses',
                'currentQuotation.details',
                'submissionFormInstance.submissionForm',
                'submissionFormInstance.crmCustomer',
            ])
            ->find($enquiryId);

        if ($enquiry === null) {
            return;
        }

        // TRF instance is source of truth for requested tests/params until the lab
        // intentionally saves enquiry_sample_configuration with parameter selections.
        $shouldResyncFromPortalForm = $enquiry->submissionFormInstance !== null
            && ! $this->enquiryHasLabSavedParameterSelections($enquiry);

        if ($shouldResyncFromPortalForm) {
            app(\App\Services\Commercial\CommercialEnquiryFromFormService::class)
                ->resyncSampleDataFromInstance($enquiry->submissionFormInstance);
            $enquiry = SampleSubmissionRequest::query()
                ->with([
                    'customer',
                    'contact',
                    'requestedAnalyses',
                    'currentQuotation.details',
                    'submissionFormInstance.submissionForm',
                    'submissionFormInstance.crmCustomer',
                ])
                ->find($enquiryId);
        }

        if ($enquiry === null) {
            return;
        }

        $quotationService = app(QuotationFromEnquiryService::class);
        $enquiry = $quotationService->ensureEnquiryReflectsSentQuotation($enquiry);
        $header = $enquiry->currentQuotation;

        if ($header !== null) {
            $header = $quotationService->syncHeaderPricelistAndCurrency($header, $enquiry);
        }

        $this->enquiryId = $enquiry->id;
        $display = app(EnquiryReviewDisplayService::class);

        $this->enquiryNotes = $enquiry->staffCommercialNotes();
        $this->customerFeedbackNotes = $enquiry->customerFeedbackNotes();
        $this->customerName = $display->customerName($enquiry);
        $this->requestReference = (string) ($enquiry->unique_identification ?? $enquiry->getFormattedNumberAttribute());
        $this->enquiryStatus = (string) $enquiry->status;
        $this->sourceChannel = (string) ($enquiry->source_channel ?? '');
        $channel = strtolower(trim($this->sourceChannel));
        if ($channel === 'walk_in') {
            $this->sendPortal = false;
            $this->sendEmail = true;
        } else {
            $this->sendPortal = $channel === 'portal';
            $this->sendEmail = false;
        }
        $this->collectionDataRows = $display->collectionDataRows($enquiry);
        $this->statementOfConformityOptions = $display->statementOfConformityOptions($enquiry);
        $this->headerSampleType = $display->headerSampleTypeLabel($enquiry);
        $this->displaySampleRows = $display->sampleRows($enquiry);
        $this->requestedTests = $display->requestedTests($enquiry);
        $this->submissionFormInstanceId = $enquiry->submission_form_instance_id;
        $this->submissionFormId = $enquiry->submissionFormInstance?->submission_form_id;
        $this->sampleLines = app(AcceptanceFormSampleConfigService::class)
            ->resolveTrfSampleLines($enquiry);
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $this->crmCustomerId = (string) ($enquiry->crm_customer_id ?? '');
        $this->customerName = $display->customerName($enquiry);
        if ($this->customerName === '' && $this->crmCustomerId !== '') {
            $this->customerName = (string) ($enquiry->customer?->name ?? '');
        }
        $this->quotationHeaderId = $enquiry->current_quotation_header_id;
        $this->quoteNumber = (string) ($enquiry->currentQuotation?->quote_number ?? '');
        $this->statusMessage = '';
        $this->statusLevel = 'info';
        $this->statusAutoDismiss = false;
        $this->pdfGenerated = ! empty($header?->upload_url);
        $this->quotationSent = $this->enquiryQuotationWasSent($enquiry, $header);
        $this->quotationManuallyEdited = false;
        $this->showBuildQuotationModal = false;
        $this->quotationMode = 'build_new';
        $this->quotationModeRenderKey = 0;
        $this->selectedExistingQuotationId = null;
        $this->existingQuotationSearch = '';
        $this->showExistingQuotationDropdown = false;
        $this->quotationMismatchWarning = '';
        $this->refreshExistingQuotationOptions();

        if ($header !== null && $header->details->isNotEmpty()) {
            $this->lines = $quotationService->buildInlineLinesFromQuotationHeader($header);
            $this->quotationBuilt = true;
        } else {
            $this->lines = [];
            $this->quotationBuilt = false;
        }

        $this->loadSampleConfigs($enquiry);
        $this->normalizeSampleConfigs();

        if (! $this->quotationBuilt && $this->sampleConfigs !== [] && $this->crmCustomerId !== '') {
            $this->rebuildQuotationLinesFromSampleConfigs();
        }
        $this->activeStep = $this->resolveOpeningStep($enquiry, $header);
        $this->statusMessage = $this->quotationSent
            ? 'Quotation '.$this->quoteNumber.' has been sent. Saved sample configuration and pricing are shown below.'
            : '';
        $this->statusLevel = 'info';
        $this->statusAutoDismiss = $this->statusMessage !== '';
        $this->showModal = true;
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->existingQuotationSearch = '';
        $this->showExistingQuotationDropdown = false;
        $this->quotationMismatchWarning = '';
    }

    public function goToStep(string $step): void
    {
        if (! in_array($step, ['review', 'sample_config', 'pricing'], true)) {
            return;
        }

        if ($step === 'pricing') {
            $this->refreshExistingQuotationOptions();
            if ($this->quotationMode === 'use_existing') {
                if ($this->selectedExistingQuotationId) {
                    $this->applySelectedExistingQuotation();
                }
            } else {
                $this->rebuildQuotationLinesFromSampleConfigs();
                $this->quotationBuilt = false;
                if ($this->quotationHeaderId === null) {
                    try {
                        $header = $this->ensureQuotationHeader();
                        $this->pdfGenerated = ! empty($header->upload_url);
                    } catch (Throwable) {
                        // Header shell may be created on build quotation.
                    }
                } elseif ($header = $this->resolveQuotationHeader()) {
                    $this->pdfGenerated = ! empty($header->upload_url);
                }
            }
        }

        if ($step === 'sample_config') {
            $this->quotationManuallyEdited = false;
            if ($this->enquiryId !== null) {
                $enquiry = SampleSubmissionRequest::query()
                    ->with(['requestedAnalyses', 'submissionFormInstance'])
                    ->find($this->enquiryId);
                if ($enquiry !== null) {
                    if ($enquiry->submissionFormInstance !== null
                        && ! $this->enquiryHasLabSavedParameterSelections($enquiry)) {
                        app(\App\Services\Commercial\CommercialEnquiryFromFormService::class)
                            ->resyncSampleDataFromInstance($enquiry->submissionFormInstance);
                        $enquiry = SampleSubmissionRequest::query()
                            ->with(['requestedAnalyses', 'submissionFormInstance'])
                            ->find($this->enquiryId) ?? $enquiry;
                        $this->requestedTests = app(EnquiryReviewDisplayService::class)
                            ->requestedTests($enquiry);
                        $this->displaySampleRows = app(EnquiryReviewDisplayService::class)
                            ->sampleRows($enquiry);
                    }
                    $this->ensureRequestedParametersSelected($enquiry);
                }
            }
            $this->reconcileSampleConfigParameterKeys();
            $this->normalizeSampleConfigs();
            $this->setStatus('info', '');
        }

        if ($step === 'review') {
            $this->setStatus('info', '');
        }

        $this->activeStep = $step;
    }

    private function normalizeSampleConfigs(): void
    {
        $this->sampleConfigs = array_values(array_map(function (array $config): array {
            $config['parameter_search'] = (string) ($config['parameter_search'] ?? '');

            return $config;
        }, $this->sampleConfigs));
    }

    public function setQuotationMode(string $value): void
    {
        if (! in_array($value, ['build_new', 'use_existing'], true)) {
            return;
        }

        $this->quotationMode = $value;
        $this->quotationModeRenderKey++;
        // updated* hooks do not run for assignments inside actions — sync explicitly.
        $this->syncQuotationModeState($value);
    }

    public function updatedQuotationMode(string $value): void
    {
        if (! in_array($value, ['build_new', 'use_existing'], true)) {
            $this->quotationMode = 'build_new';
            $this->syncQuotationModeState('build_new');

            return;
        }

        $this->syncQuotationModeState($value);
    }

    private function syncQuotationModeState(string $value): void
    {
        $this->quotationMismatchWarning = '';
        $this->existingQuotationSearch = '';
        $this->showExistingQuotationDropdown = false;

        if ($value === 'use_existing') {
            $this->refreshExistingQuotationOptions();
            if ($this->selectedExistingQuotationId) {
                $this->applySelectedExistingQuotation();
            } else {
                $this->lines = [];
                $this->quotationBuilt = false;
                $this->pdfGenerated = false;
                $this->quoteNumber = '';
                $this->quotationHeaderId = null;
            }

            return;
        }

        $this->selectedExistingQuotationId = null;
        $this->rebuildQuotationLinesFromSampleConfigs();
        $this->quotationBuilt = false;
        $this->clearStatus();
    }

    public function openExistingQuotationDropdown(): void
    {
        $this->showExistingQuotationDropdown = true;
    }

    public function closeExistingQuotationDropdown(): void
    {
        $this->showExistingQuotationDropdown = false;
    }

    public function selectExistingQuotation(string $quotationId): void
    {
        $quotationId = trim($quotationId);
        $this->selectedExistingQuotationId = $quotationId !== '' ? $quotationId : null;
        $this->existingQuotationSearch = '';
        $this->showExistingQuotationDropdown = false;

        // updatedSelectedExistingQuotationId does not run for action assignments.
        if ($this->selectedExistingQuotationId === null) {
            $this->resetExistingQuotationPreview();

            return;
        }

        $this->applySelectedExistingQuotation();
    }

    public function clearExistingQuotation(): void
    {
        $this->selectedExistingQuotationId = null;
        $this->existingQuotationSearch = '';
        $this->showExistingQuotationDropdown = false;
        $this->resetExistingQuotationPreview();
    }

    /**
     * @return list<array{id: string, label: string, quote_number: string, expiring_date: string, total: string}>
     */
    public function filteredExistingQuotationOptions(): array
    {
        $needle = mb_strtolower(trim($this->existingQuotationSearch));
        if ($needle === '') {
            return $this->existingQuotationOptions;
        }

        return array_values(array_filter(
            $this->existingQuotationOptions,
            function (array $option) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($option['quote_number'] ?? ''),
                    (string) ($option['label'] ?? ''),
                    (string) ($option['expiring_date'] ?? ''),
                    (string) ($option['total'] ?? ''),
                ]));

                return str_contains($haystack, $needle);
            }
        ));
    }

    public function existingQuotationLabel(?string $quotationId): string
    {
        if ($quotationId === null || $quotationId === '') {
            return '';
        }

        foreach ($this->existingQuotationOptions as $option) {
            if (($option['id'] ?? '') === $quotationId) {
                $number = (string) ($option['quote_number'] ?? '');

                return $number !== '' ? $number : (string) ($option['label'] ?? 'Quotation');
            }
        }

        return '';
    }

    public function updatedSelectedExistingQuotationId(?string $value): void
    {
        if ($this->quotationMode !== 'use_existing') {
            return;
        }

        if ($value === null || $value === '') {
            $this->resetExistingQuotationPreview();

            return;
        }

        $this->applySelectedExistingQuotation();
    }

    private function resetExistingQuotationPreview(): void
    {
        $this->lines = [];
        $this->quotationBuilt = false;
        $this->pdfGenerated = false;
        $this->quotationMismatchWarning = '';
        $this->quotationHeaderId = null;
        $this->quoteNumber = '';
    }

    public function refreshExistingQuotationOptions(): void
    {
        $this->existingQuotationOptions = [];

        if ($this->crmCustomerId === '') {
            return;
        }

        $quotes = app(QuotationFromEnquiryService::class)
            ->eligibleQuotationsForCustomer($this->crmCustomerId);

        foreach ($quotes as $quote) {
            $number = (string) ($quote->quote_number ?? '');
            $expires = $quote->expiring_date
                ? \Carbon\Carbon::parse($quote->expiring_date)->format('Y-m-d')
                : '—';
            $total = number_format((float) ($quote->total_amount ?? 0), 2);

            $this->existingQuotationOptions[] = [
                'id' => (string) $quote->id,
                'quote_number' => $number,
                'expiring_date' => $expires,
                'total' => $total,
                'label' => $number !== '' ? $number : 'Quotation',
            ];
        }
    }

    public function applySelectedExistingQuotation(): void
    {
        if ($this->enquiryId === null || ! $this->selectedExistingQuotationId) {
            return;
        }

        try {
            $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
            $header = QuotationHeader::query()
                ->with('details')
                ->find($this->selectedExistingQuotationId);

            if ($enquiry === null || $header === null) {
                throw new \RuntimeException('Selected quotation was not found.');
            }

            $service = app(QuotationFromEnquiryService::class);
            $header = $service->attachExistingQuotation($enquiry, $header);
            $this->quotationHeaderId = $header->id;
            $this->quoteNumber = (string) ($header->quote_number ?? '');
            $this->lines = $service->buildInlineLinesFromQuotationHeader($header);
            $this->quotationBuilt = true;
            $this->quotationManuallyEdited = false;
            $this->pdfGenerated = ! empty($header->upload_url);

            $warnings = $service->quotationMismatchWarnings($this->sampleConfigs, $header);
            $this->quotationMismatchWarning = implode(' ', $warnings);

            if ($this->quotationMismatchWarning !== '') {
                $this->clearStatus();
            } else {
                $this->setStatus('success', 'Using quotation '.$this->quoteNumber.'. Review and send when ready.');
            }
        } catch (Throwable $exception) {
            $this->selectedExistingQuotationId = null;
            $this->lines = [];
            $this->quotationBuilt = false;
            $this->quotationMismatchWarning = '';
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function syncFromCustomerPricelist(): void
    {
        if ($this->enquiryId === null || $this->crmCustomerId === '') {
            $this->setStatus('error', 'Customer is required to sync from the customer pricelist.');

            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $pricelist = $pricing->resolveCustomerAssignedPricelist($this->crmCustomerId);
        if ($pricelist === null) {
            $this->setStatus('error', 'No pricelist is assigned to this customer.');

            return;
        }

        $enquiry = SampleSubmissionRequest::query()
            ->with(['requestedAnalyses', 'submissionFormInstance'])
            ->find($this->enquiryId);
        if ($enquiry === null) {
            $this->setStatus('error', 'Enquiry not found.');

            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $trfSeeds = $configService->resolveTrfSampleLines($enquiry);
        $this->sampleConfigs = app(\App\Services\Commercial\CommercialEnquiryConfigSyncService::class)
            ->mergePricelistIntoSampleConfigs(
                $this->crmCustomerId,
                $this->sampleConfigs,
                is_array($trfSeeds) ? $trfSeeds : [],
                false,
                $pricelist,
            );
        $this->normalizeSampleConfigs();
        $this->setStatus('success', 'Sample configuration synced with TRF lines using the customer pricelist.');
    }

    /** @deprecated Use syncFromCustomerPricelist() */
    public function syncFromContractPricelist(): void
    {
        $this->syncFromCustomerPricelist();
    }

    public function getIsUsingExistingQuotationProperty(): bool
    {
        return $this->quotationMode === 'use_existing';
    }

    public function saveReviewAndContinue(): void
    {
        if ($this->enquiryId === null) {
            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry === null) {
            return;
        }

        if ($enquiry->status === SampleSubmissionRequest::STATUS_REQUESTED
            || $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW) {
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
        }

        $enquiry->save();

        $this->ensureRequestedParametersSelected($enquiry);
        $this->normalizeSampleConfigs();
        $this->activeStep = 'sample_config';
        $this->setStatus('info', '');
    }

    public function saveSampleConfigAndContinue(): void
    {
        if ($this->enquiryId === null) {
            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry === null) {
            return;
        }

        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $this->crmCustomerId = (string) ($enquiry->crm_customer_id ?? '');

        if ($this->crmCustomerId === '') {
            $this->setStatus('error', 'Customer is required for sample configuration. Ensure the walk-in TRF customer name matches a CRM customer.');

            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->reconcileSampleConfigParameterKeys();
        $this->sampleConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs, $this->crmCustomerId);

        try {
            $configService->validateConfigs($this->sampleConfigs, requireLabSection: false);
        } catch (Throwable $exception) {
            $this->setStatus('error', $exception->getMessage());

            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry !== null) {
            $this->persistSampleConfiguration($enquiry);
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            $enquiry->save();
        }

        $acceptanceLines = $configService->expandConfigsToLines($this->sampleConfigs, $this->crmCustomerId);
        $quotationService = app(QuotationFromEnquiryService::class);
        $enquiry = $enquiry?->fresh(['requestedAnalyses']) ?? SampleSubmissionRequest::query()->find($this->enquiryId);

        if ($enquiry === null) {
            return;
        }

        $existingLines = $this->lines;
        $this->lines = $quotationService->buildInlineLinesFromAcceptanceLines($enquiry, $acceptanceLines);
        $this->lines = $this->mergePreservedQuotationLineValues(
            $existingLines,
            $this->lines,
            $this->lineSubcontractOverrides
        );

        $this->quotationManuallyEdited = false;
        $this->quotationBuilt = false;

        try {
            $header = $this->ensureQuotationHeader();
            $this->quotationHeaderId = $header->id;
            $this->quoteNumber = (string) ($header->quote_number ?? '');
            $this->pdfGenerated = ! empty($header->upload_url);
            $this->activeStep = 'pricing';
            $this->clearStatus();
        } catch (Throwable $exception) {
            $this->activeStep = 'pricing';
            $this->clearStatus();
        }
    }

    public function syncPricesFromPricelist(): void
    {
        if ($this->enquiryId === null || ! $this->crmCustomerId) {
            $this->setStatus('error', 'Customer is required to sync prices from pricelist.');

            return;
        }

        if ($this->lines === []) {
            $this->rebuildQuotationLinesFromSampleConfigs();
        }

        if ($this->lines === []) {
            $this->setStatus('error', 'No pricing lines to sync. Complete sample configuration first.');

            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $preferredPricelist = $pricing->resolveCustomerAssignedPricelist($this->crmCustomerId)
            ?? $pricing->resolvePricelist($this->crmCustomerId);

        if ($preferredPricelist === null && $pricing->assignedPricelistsForCustomer($this->crmCustomerId) === []) {
            $this->setStatus('error', 'No pricelist is available for this customer.');

            return;
        }

        $this->reconcileSampleConfigParameterKeys();
        $configService = app(AcceptanceFormSampleConfigService::class);
        $acceptanceLines = $configService->expandConfigsToLines($this->sampleConfigs, $this->crmCustomerId);
        $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry === null) {
            $this->setStatus('error', 'Enquiry not found.');

            return;
        }

        $this->lines = app(QuotationFromEnquiryService::class)
            ->buildInlineLinesFromAcceptanceLines($enquiry, $acceptanceLines);
        $this->normalizeQuotationLineQuantities();

        $unmatched = 0;
        foreach ($this->lines as $line) {
            if ((float) ($line['unit_price'] ?? 0) <= 0) {
                $unmatched++;
            }
        }

        $this->quotationManuallyEdited = true;
        $this->quotationBuilt = false;
        $this->refreshLineLabMetrics();

        if ($unmatched > 0) {
            $this->setStatus('warning', "Synced prices from pricelist. {$unmatched} line(s) had no matching pricelist item.");

            return;
        }

        $this->setStatus('success', 'Unit prices synced from pricelist.');
    }

    public function openBuildQuotationModal(): void
    {
        if ($this->lines === []) {
            $this->setStatus('error', 'No quotation lines to build. Complete sample configuration and sync prices first.');

            return;
        }

        $this->showBuildQuotationModal = true;
    }

    public function closeBuildQuotationModal(): void
    {
        $this->showBuildQuotationModal = false;
    }

    public function confirmBuildQuotation(): void
    {
        if ($this->enquiryId === null || $this->lines === []) {
            $this->setStatus('error', 'No quotation lines to save.');

            return;
        }

        try {
            $this->normalizeQuotationLineQuantities();
            $this->refreshLineLabMetrics();
            $header = $this->ensureQuotationHeader();
            $header->show_unit_price_column = true;
            $header->save();

            $this->persistQuotationLines();
            $this->persistSampleConfiguration();

            $header->refresh();
            $this->quotationHeaderId = $header->id;
            $this->quoteNumber = (string) ($header->quote_number ?? '');
            $this->quotationBuilt = true;
            $this->quotationManuallyEdited = false;
            $this->showBuildQuotationModal = false;
            $this->pdfGenerated = ! empty($header->upload_url);
            $this->setStatus('success', 'Quotation saved. You can send it to the customer, or use View Quotation to preview the PDF.');
        } catch (Throwable $exception) {
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function viewQuotation(): void
    {
        if ($this->enquiryId === null || $this->lines === []) {
            $this->setStatus(
                'error',
                $this->quotationMode === 'use_existing'
                    ? 'Select an existing quotation before viewing.'
                    : 'No quotation lines to view. Complete sample configuration and sync prices first.'
            );

            return;
        }

        try {
            $quotationService = app(QuotationFromEnquiryService::class);

            if ($this->quotationMode === 'use_existing') {
                if (! $this->selectedExistingQuotationId) {
                    throw new \RuntimeException('Select an existing quotation before viewing.');
                }

                $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
                $header = QuotationHeader::query()->find($this->selectedExistingQuotationId);
                if ($enquiry === null || $header === null) {
                    throw new \RuntimeException('Selected quotation was not found.');
                }

                $header = $quotationService->attachExistingQuotation($enquiry, $header);
                if (empty($header->upload_url)) {
                    $header = $quotationService->generatePdf($header);
                }
            } else {
                $this->normalizeQuotationLineQuantities();
                $this->refreshLineLabMetrics();
                $header = $this->ensureQuotationHeader();
                $header->show_unit_price_column = true;
                $header->save();

                $this->persistQuotationLines();
                $this->persistSampleConfiguration();

                $header = $quotationService->generatePdf($header->fresh() ?? $header);
            }

            $header->refresh();

            $this->quotationHeaderId = $header->id;
            $this->quoteNumber = (string) ($header->quote_number ?? '');
            $this->quotationBuilt = true;
            $this->quotationManuallyEdited = false;
            $this->showBuildQuotationModal = false;
            $this->pdfGenerated = ! empty($header->upload_url);

            if (! $this->pdfGenerated || $this->quotationHeaderId === null) {
                throw new \RuntimeException('Quotation PDF was not generated.');
            }

            $this->dispatch(
                'open-quotation-preview',
                url: route('quotation.preview', ['id' => $this->quotationHeaderId]),
            );
            $this->setStatus('success', 'Quotation opened in a new tab.');
        } catch (Throwable $exception) {
            $this->pdfGenerated = false;
            $this->setStatus('error', $exception->getMessage());
        }
    }

    private function normalizeQuotationLineQuantities(): void
    {
        foreach ($this->lines as $index => $line) {
            $count = $this->linePhysicalSampleCount($line);
            $this->lines[$index]['physical_sample_count'] = $count;
            $this->lines[$index]['quantity'] = $count;
        }
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function linePhysicalSampleCount(array $line): int
    {
        return max(1, (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1));
    }

    public function updatedLines($value, string $name): void
    {
        if (str_ends_with($name, '.subcontracted')) {
            $lineIndex = $this->lineIndexFromBindingPath($name);
            if ($lineIndex !== null && isset($this->lines[$lineIndex])) {
                $lineKey = $this->quotationLineMergeKey($this->lines[$lineIndex]);
                if ($lineKey !== '::::') {
                    $this->lineSubcontractOverrides[$lineKey] = true;
                }
            }
        }

        $this->quotationManuallyEdited = true;
        $this->quotationBuilt = false;
        $this->refreshLineLabMetrics();
    }

    public function openAddQuotationLineModal(): void
    {
        if (! $this->crmCustomerId) {
            $this->setStatus('error', 'Customer is required before adding parameters.');

            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $this->addLineSampleTypes = $pricing->allSampleTypesForPicker();
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
        if (! $this->crmCustomerId || ! $this->addLineSampleTypeId) {
            $this->addLineAnalysisTypes = [];
            $this->addLineParameters = [];

            return;
        }

        $this->addLineAnalysisTypes = app(AcceptanceFormPricingService::class)
            ->allAnalysisTypesForPicker($this->addLineSampleTypeId);
        $this->addLineAnalysisTypeId = null;
        $this->addLineParameters = [];
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;
    }

    public function updatedAddLineAnalysisTypeId(): void
    {
        $this->refreshAddLineParameterOptions();
    }

    public function confirmAddQuotationLine(): void
    {
        if ($this->addLineAllParametersSelected) {
            $this->setStatus('error', 'All parameters for this sample type and analysis type are already in the list.');

            return;
        }

        $isWholeAnalysisType = $this->addLineParameterKey === null || $this->addLineParameterKey === '';

        if ($isWholeAnalysisType) {
            if (! $this->addLineCanAddWholeAnalysisType) {
                $this->setStatus('error', 'This analysis type is already represented in the list.');

                return;
            }

            $this->appendAllParametersForQuotationAddLine();
            $this->showAddLineModal = false;
            $this->finalizeQuotationLineMutation();

            return;
        }

        $parameter = collect($this->addLineParameters)->firstWhere('id', $this->addLineParameterKey);
        if ($parameter === null) {
            return;
        }

        if ($this->isQuotationParameterAlreadyInTable(
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId,
            (string) $this->addLineParameterKey,
            $parameter['analysis_element_id'] ?? null,
        )) {
            $this->setStatus('error', 'That parameter is already in the quotation.');
            $this->refreshAddLineParameterOptions();

            return;
        }

        $this->appendQuotationLineFromParameter($parameter);
        $this->addParameterToSampleConfigs($parameter);
        $this->refreshAddLineParameterOptions();

        if ($this->addLineAllParametersSelected) {
            $this->showAddLineModal = false;
        }

        $this->finalizeQuotationLineMutation();
    }

    public function removeQuotationLine(int $index): void
    {
        if (! isset($this->lines[$index])) {
            return;
        }

        $removedLine = $this->lines[$index];
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->reindexQuotationLines();
        $this->removeParameterFromSampleConfigs($removedLine);
        $this->finalizeQuotationLineMutation();

        if ($this->showAddLineModal && $this->addLineAnalysisTypeId) {
            $this->refreshAddLineParameterOptions();
        }
    }

    public function resetLinePriceFromPricelist(int $index): void
    {
        if (! isset($this->lines[$index]) || ! $this->crmCustomerId) {
            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $pricelist = $pricing->resolveCustomerAssignedPricelist($this->crmCustomerId);

        if ($pricelist === null) {
            $this->setStatus('error', 'No pricelist is assigned to this customer.');

            return;
        }

        $line = $this->lines[$index];
        $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
        $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');

        if (! empty($line['is_package'])) {
            $requestedElementIds = array_values(array_filter(array_map(
                'strval',
                $line['package_element_ids'] ?? []
            )));
            $match = $pricing->resolvePackageForGroup(
                $this->crmCustomerId,
                $sampleTypeId !== '' ? $sampleTypeId : null,
                $analysisTypeId,
                $requestedElementIds,
                $pricelist,
            );

            if ($match !== null) {
                $this->lines[$index]['unit_price'] = (float) $match['item']->selling_price;
                $this->lines[$index]['tax'] = $match['item']->vat
                    ? app(QuotationLineTaxResolver::class)->activeTaxRegimePercent()
                    : 0.0;
            }

            $this->quotationManuallyEdited = true;
            $this->quotationBuilt = false;
            $this->refreshLineLabMetrics();
            $this->setStatus('success', 'Package price reset from customer pricelist.');

            return;
        }

        $elementId = (string) ($line['analysis_element_id'] ?? '');

        $resolved = $pricing->resolveLinePriceWithPricelist(
            $this->crmCustomerId,
            $sampleTypeId !== '' ? $sampleTypeId : null,
            $analysisTypeId,
            $elementId !== '' ? $elementId : null,
            $pricelist,
        );

        $this->lines[$index]['unit_price'] = $resolved['price'];
        $this->lines[$index]['tax'] = app(QuotationLineTaxResolver::class)->resolveLineTaxPercent(
            $resolved['pricelist'],
            $sampleTypeId !== '' ? $sampleTypeId : null,
            $analysisTypeId,
            $elementId !== '' ? $elementId : null,
        );

        $this->quotationManuallyEdited = true;
        $this->quotationBuilt = false;
        $this->refreshLineLabMetrics();
        $this->setStatus('success', 'Line price reset from customer pricelist.');
    }

    public function sendQuotation(): void
    {
        if (strtolower($this->sourceChannel) !== 'portal') {
            $this->sendPortal = false;
        }

        if (strtolower($this->sourceChannel) === 'walk_in') {
            $this->sendPortal = false;
            if (! $this->sendEmail) {
                $this->setStatus('error', 'Email delivery is required for walk-in enquiries.');

                return;
            }
        }

        if (! $this->sendPortal && ! $this->sendEmail) {
            $this->setStatus('error', 'Select at least one delivery channel (portal or email).');

            return;
        }

        if ($this->enquiryId === null || $this->lines === []) {
            $this->setStatus(
                'error',
                $this->quotationMode === 'use_existing'
                    ? 'Select a valid existing quotation before sending.'
                    : 'No quotation lines to send. Complete sample configuration and sync prices first.'
            );

            return;
        }

        try {
            $enquiry = SampleSubmissionRequest::query()
                ->with(['customer', 'contact', 'requestedAnalyses'])
                ->find($this->enquiryId);

            if ($enquiry === null) {
                throw new \RuntimeException('Enquiry not found.');
            }

            $quotationService = app(QuotationFromEnquiryService::class);

            if ($this->quotationMode === 'use_existing') {
                if (! $this->selectedExistingQuotationId) {
                    throw new \RuntimeException('Select an existing quotation before sending.');
                }

                $header = QuotationHeader::query()
                    ->with('details')
                    ->find($this->selectedExistingQuotationId);
                if ($header === null) {
                    throw new \RuntimeException('Selected quotation was not found.');
                }

                $header = $quotationService->attachExistingQuotation($enquiry, $header);
                $warnings = $quotationService->quotationMismatchWarnings($this->sampleConfigs, $header);
                $this->quotationMismatchWarning = implode(' ', $warnings);
                $this->persistSampleConfiguration($enquiry);
            } else {
                $this->normalizeQuotationLineQuantities();
                $this->refreshLineLabMetrics();

                $header = $this->ensureQuotationHeader();
                $header->show_unit_price_column = true;
                $header->save();

                $this->persistQuotationLines();
                $this->persistSampleConfiguration($enquiry);
            }

            $header = $header->fresh() ?? $header;
            if (empty($header->upload_url)) {
                $header = $quotationService->generatePdf($header);
            }

            $this->quotationHeaderId = $header->id;
            $this->quoteNumber = (string) ($header->quote_number ?? '');
            $this->quotationBuilt = true;
            $this->quotationManuallyEdited = false;
            $this->pdfGenerated = ! empty($header->upload_url);

            if (! $this->pdfGenerated || $this->quotationHeaderId === null) {
                throw new \RuntimeException('Quotation PDF could not be generated before sending.');
            }

            $quotationService->sendToCustomer(
                $enquiry,
                $header->fresh() ?? $header,
                $this->sendPortal,
                $this->sendEmail,
            );

            $enquiry = SampleSubmissionRequest::query()
                ->with('currentQuotation')
                ->find($this->enquiryId);

            if ($enquiry !== null) {
                $enquiry = $quotationService->ensureEnquiryReflectsSentQuotation($enquiry);
                $this->enquiryStatus = (string) $enquiry->status;
                $this->quotationSent = true;
            }

            $this->dispatch('process-enquiry-completed');

            $flashMessage = strtolower($this->sourceChannel) === 'walk_in'
                ? 'Quotation sent by email. Record walk-in acceptance on the request view page, then capture the PO.'
                : 'Quotation sent to customer.';

            $this->dispatch('notify', type: 'success', message: $flashMessage);
            $this->setStatus('success', $flashMessage, true);
        } catch (Throwable $exception) {
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function recordWalkInQuotationAcceptance(): void
    {
        if ($this->enquiryId === null) {
            return;
        }

        try {
            $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
            if ($enquiry === null) {
                throw new \RuntimeException('Enquiry not found.');
            }

            app(QuotationFromEnquiryService::class)->recordWalkInAcceptance($enquiry);

            $this->closeWizard();
            $this->dispatch('process-enquiry-completed');
            session()->flash('message', 'Quotation marked as accepted. Record PO to move the request to Ready for Reception.');
        } catch (Throwable $exception) {
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sampleworkflow.process-enquiry-wizard');
    }

    private function persistSampleConfiguration(?SampleSubmissionRequest $enquiry = null): void
    {
        if ($this->enquiryId === null || $this->sampleConfigs === []) {
            return;
        }

        $enquiry ??= SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry === null) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->reconcileSampleConfigParameterKeys();
        $this->sampleConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs, $this->crmCustomerId);

        if (Schema::hasColumn('sample_submission_requests', 'enquiry_sample_configuration')) {
            $enquiry->enquiry_sample_configuration = $this->sampleConfigs;
            if (Schema::hasColumn('sample_submission_requests', 'number_of_samples')) {
                $enquiry->number_of_samples = $configService->totalSampleCount($this->sampleConfigs);
            }
            $enquiry->save();
        }

        app(\App\Services\Commercial\CommercialEnquirySampleLineSync::class)
            ->syncFromSampleConfigs($enquiry, $this->sampleConfigs);

        $enquiry = $enquiry->fresh(['requestedAnalyses']);
        if ($enquiry !== null) {
            $this->sampleLines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
            $this->requestedTests = app(\App\Services\Commercial\EnquiryReviewDisplayService::class)
                ->requestedTests($enquiry);
        }
    }

    private function enquiryQuotationWasSent(SampleSubmissionRequest $enquiry, ?QuotationHeader $header): bool
    {
        if ($header?->sent_to_customer_at !== null) {
            return true;
        }

        return in_array((string) $enquiry->status, [
            SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW,
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
        ], true);
    }

    private function resolveOpeningStep(SampleSubmissionRequest $enquiry, ?QuotationHeader $header): string
    {
        if ($header !== null && ($header->details->isNotEmpty() || $header->sent_to_customer_at !== null)) {
            return 'pricing';
        }

        $storedConfig = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];
        if ($storedConfig !== []) {
            return 'sample_config';
        }

        return 'review';
    }

    /**
     * True when the lab has intentionally saved sample configuration with parameter selections.
     * Empty configs or rows with empty parameter_keys are not treated as lab-authored — TRF wins.
     */
    private function enquiryHasLabSavedParameterSelections(SampleSubmissionRequest $enquiry): bool
    {
        $stored = is_array($enquiry->enquiry_sample_configuration)
            ? $enquiry->enquiry_sample_configuration
            : [];

        if ($stored === []) {
            return false;
        }

        foreach ($stored as $config) {
            if (! is_array($config)) {
                continue;
            }

            $keys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
                ->map(fn (mixed $key): string => trim((string) $key))
                ->filter(fn (string $key): bool => $key !== '')
                ->values()
                ->all();

            if ($keys !== []) {
                return true;
            }
        }

        return false;
    }

    private function loadSampleConfigs(SampleSubmissionRequest $enquiry): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);
        $stored = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];
        $hasLabParameterSelections = $this->enquiryHasLabSavedParameterSelections($enquiry);

        if ($stored !== []) {
            $this->sampleConfigs = $configService->flattenToPerSampleConfigs($stored);
            $this->sampleConfigs = $configService->remapConfigsToCurrentHierarchy($this->sampleConfigs, $enquiry);
            // Only fill from TRF when lab has not yet saved parameter selections.
            if (! $hasLabParameterSelections) {
                $this->sampleConfigs = $configService->applyRequestedParameterKeysFromEnquiry($this->sampleConfigs, $enquiry);
            }
            if ($this->crmCustomerId !== null && trim($this->crmCustomerId) !== '') {
                $this->sampleConfigs = $configService->alignPrefillParameterKeysForConfigs(
                    $this->sampleConfigs,
                    $this->crmCustomerId,
                );
            }
            $this->reconcileSampleConfigParameterKeys();
            $this->normalizeSampleConfigs();
            $this->backfillLabSectionIdsOnConfigs();

            return;
        }

        $instance = $enquiry->submissionFormInstance;
        $prefillLines = $configService->buildPrefillLinesFromEnquiry($enquiry, $this->lines, $instance);

        $this->sampleConfigs = $configService->buildConfigsFromPrefill($prefillLines, $instance);
        $this->sampleConfigs = $configService->remapConfigsToCurrentHierarchy($this->sampleConfigs, $enquiry);
        $this->sampleConfigs = $configService->applyRequestedParameterKeysFromEnquiry($this->sampleConfigs, $enquiry);
        if ($this->crmCustomerId !== null && trim($this->crmCustomerId) !== '') {
            $this->sampleConfigs = $configService->alignPrefillParameterKeysForConfigs(
                $this->sampleConfigs,
                $this->crmCustomerId,
            );
        }
        $this->reconcileSampleConfigParameterKeys();
        $defaultZoneId = $configService->resolveZoneIdFromInstance($instance);

        if ($defaultZoneId !== null) {
            foreach ($this->sampleConfigs as $index => $config) {
                if (empty($config['zone_id'])) {
                    $this->sampleConfigs[$index]['zone_id'] = $defaultZoneId;
                }
            }
        }

        $this->backfillLabSectionIdsOnConfigs();
    }

    private function backfillLabSectionIdsOnConfigs(): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);

        foreach ($this->sampleConfigs as $index => $config) {
            if (! empty($config['lab_section_id']) || empty($config['analysis_type_id'])) {
                continue;
            }

            $resolved = $configService->resolveLabSectionIdForAnalysisType((string) $config['analysis_type_id']);
            if ($resolved !== null) {
                $this->sampleConfigs[$index]['lab_section_id'] = $resolved;
            }
        }
    }

    private function persistQuotationLines(): void
    {
        $header = $this->resolveQuotationHeader();
        if ($header === null || $this->lines === []) {
            return;
        }

        app(QuotationFromEnquiryService::class)->persistInlineLines($header, $this->lines);
        $header->refresh();
        $this->quoteNumber = (string) $header->quote_number;
    }

    private function resolveQuotationHeader(): ?QuotationHeader
    {
        if ($this->quotationHeaderId === null) {
            return null;
        }

        return QuotationHeader::query()->find($this->quotationHeaderId);
    }

    private function ensureQuotationHeader(): QuotationHeader
    {
        if ($this->enquiryId === null) {
            throw new \RuntimeException('Enquiry not found.');
        }

        $enquiry = SampleSubmissionRequest::query()
            ->with(['requestedAnalyses', 'currentQuotation', 'customer', 'submissionFormInstance'])
            ->find($this->enquiryId);

        if ($enquiry === null) {
            throw new \RuntimeException('Enquiry not found.');
        }

        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $this->crmCustomerId = (string) ($enquiry->crm_customer_id ?? '');

        $quotationService = app(QuotationFromEnquiryService::class);
        $header = $quotationService->createOrOpen($enquiry);
        $header->loadMissing('details');
        $enquiry->refresh();

        $this->quotationHeaderId = $header->id;
        $this->quoteNumber = (string) $header->quote_number;

        if ($header->details->isNotEmpty()) {
            $this->pdfGenerated = ! empty($header->upload_url);

            return $header;
        }

        if ($this->lines === []) {
            $this->lines = $quotationService->buildInlineLines($enquiry);
        }

        $this->pdfGenerated = ! empty($header->upload_url);

        return $header;
    }

    public function clearStatus(): void
    {
        $this->statusMessage = '';
        $this->statusLevel = 'info';
        $this->statusAutoDismiss = false;
    }

    public function clearQuotationMismatchWarning(): void
    {
        $this->quotationMismatchWarning = '';
    }

    private function setStatus(string $level, string $message, bool $autoDismiss = false): void
    {
        $this->statusLevel = $level;
        $this->statusMessage = $message;
        $this->statusAutoDismiss = $autoDismiss
            || ($message !== '' && $level !== 'error');
    }

    private function finalizeQuotationLineMutation(): void
    {
        $this->quotationManuallyEdited = true;
        $this->quotationBuilt = false;

        if ($this->enquiryId !== null) {
            $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
            if ($enquiry !== null) {
                $this->lines = app(QuotationFromEnquiryService::class)
                    ->applyTaxFromAssignedPricelist($enquiry, $this->lines, false);
            }
        }

        $this->refreshLineLabMetrics();
        $this->persistSampleConfiguration();
    }

    /**
     * @param  array<string, mixed>  $parameter
     */
    private function addParameterToSampleConfigs(array $parameter): void
    {
        $elementId = trim((string) ($parameter['analysis_element_id'] ?? $parameter['id'] ?? ''));
        $sampleTypeId = trim((string) ($parameter['sample_type_id'] ?? $this->addLineSampleTypeId ?? ''));
        $analysisTypeId = trim((string) ($parameter['analysis_type_id'] ?? $this->addLineAnalysisTypeId ?? ''));

        if ($elementId === '' || $analysisTypeId === '') {
            return;
        }

        $matched = false;

        foreach ($this->sampleConfigs as $index => $config) {
            $configSampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));
            $configAnalysisTypeId = trim((string) ($config['analysis_type_id'] ?? ''));

            if ($configAnalysisTypeId !== $analysisTypeId) {
                continue;
            }

            if ($sampleTypeId !== '' && $configSampleTypeId !== '' && $configSampleTypeId !== $sampleTypeId) {
                continue;
            }

            $keys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
                ->map(fn (mixed $key): string => trim((string) $key))
                ->filter(fn (string $key): bool => $key !== '')
                ->values()
                ->all();

            if (! in_array($elementId, $keys, true)) {
                $keys[] = $elementId;
                $this->sampleConfigs[$index]['parameter_keys'] = $keys;
            }

            $matched = true;
        }

        if ($matched) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $empty = $configService->emptyConfig();
        $empty['sample_type_id'] = $sampleTypeId !== '' ? $sampleTypeId : null;
        $empty['analysis_type_id'] = $analysisTypeId;
        $empty['parameter_keys'] = [$elementId];
        $empty['lab_section_id'] = $configService->resolveLabSectionIdForAnalysisType($analysisTypeId);
        $empty['zone_id'] = $configService->resolveZoneIdFromInstance(
            $this->submissionFormInstanceId
                ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
                : null
        );

        $this->sampleConfigs[] = $empty;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function removeParameterFromSampleConfigs(array $line): void
    {
        $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
        $sampleTypeId = trim((string) ($line['sample_type_id'] ?? ''));
        $analysisTypeId = trim((string) ($line['analysis_type_id'] ?? ''));

        if ($elementId === '') {
            return;
        }

        foreach ($this->sampleConfigs as $index => $config) {
            $configSampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));
            $configAnalysisTypeId = trim((string) ($config['analysis_type_id'] ?? ''));

            if ($analysisTypeId !== '' && $configAnalysisTypeId !== '' && $configAnalysisTypeId !== $analysisTypeId) {
                continue;
            }

            if ($sampleTypeId !== '' && $configSampleTypeId !== '' && $configSampleTypeId !== $sampleTypeId) {
                continue;
            }

            $keys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
                ->map(fn (mixed $key): string => trim((string) $key))
                ->filter(fn (string $key): bool => $key !== '' && $key !== $elementId)
                ->values()
                ->all();

            $this->sampleConfigs[$index]['parameter_keys'] = $keys;
        }
    }

    private function refreshLineLabMetrics(): void
    {
        $this->lines = app(UncertaintyBudgetResolver::class)->enrichLinesWithLabMetrics($this->lines);
    }

    private function reconcileSampleConfigParameterKeys(): void
    {
        if ($this->crmCustomerId === null || trim($this->crmCustomerId) === '' || $this->sampleConfigs === []) {
            return;
        }

        $this->sampleConfigs = app(AcceptanceFormSampleConfigService::class)
            ->reconcileConfigsParameterKeys($this->sampleConfigs, $this->crmCustomerId);
    }

    private function ensureRequestedParametersSelected(SampleSubmissionRequest $enquiry): void
    {
        if ($this->sampleConfigs === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->sampleConfigs = $configService->applyRequestedParameterKeysFromEnquiry($this->sampleConfigs, $enquiry);
        $this->sampleConfigs = $configService->remapConfigsToCurrentHierarchy($this->sampleConfigs, $enquiry);

        if ($this->crmCustomerId !== null && trim($this->crmCustomerId) !== '') {
            $this->sampleConfigs = $configService->alignPrefillParameterKeysForConfigs(
                $this->sampleConfigs,
                $this->crmCustomerId,
            );
        }

        $this->reconcileSampleConfigParameterKeys();
    }

    private function rebuildQuotationLinesFromSampleConfigs(): void
    {
        if ($this->enquiryId === null || $this->crmCustomerId === null || trim($this->crmCustomerId) === '') {
            return;
        }

        $this->reconcileSampleConfigParameterKeys();

        $configService = app(AcceptanceFormSampleConfigService::class);
        $acceptanceLines = $configService->expandConfigsToLines($this->sampleConfigs, $this->crmCustomerId);
        $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry === null) {
            return;
        }

        $existingLines = $this->lines;
        $this->lines = app(QuotationFromEnquiryService::class)
            ->buildInlineLinesFromAcceptanceLines($enquiry, $acceptanceLines);
        $this->lines = $this->mergePreservedQuotationLineValues(
            $existingLines,
            $this->lines,
            $this->lineSubcontractOverrides
        );
        $this->normalizeQuotationLineQuantities();
        $this->quotationBuilt = false;
    }

    /**
     * @param  list<array<string, mixed>>  $previousLines
     * @param  list<array<string, mixed>>  $newLines
     * @param  array<string, bool>  $subcontractOverrides
     * @return list<array<string, mixed>>
     */
    private function mergePreservedQuotationLineValues(
        array $previousLines,
        array $newLines,
        array $subcontractOverrides = []
    ): array
    {
        $previousByKey = collect($previousLines)->keyBy(
            fn (array $line): string => $this->quotationLineMergeKey($line)
        );

        return array_map(function (array $line) use ($previousByKey, $subcontractOverrides): array {
            $lineKey = $this->quotationLineMergeKey($line);
            $previous = $previousByKey->get($lineKey);
            if ($previous === null) {
                return $line;
            }

            $line['unit_price'] = (float) ($previous['unit_price'] ?? $line['unit_price'] ?? 0);
            $line['tax'] = (float) ($previous['tax'] ?? $line['tax'] ?? 0);
            if (! empty($subcontractOverrides[$lineKey])) {
                $line['subcontracted'] = (bool) ($previous['subcontracted'] ?? $line['subcontracted'] ?? false);
            }

            return $line;
        }, $newLines);
    }

    private function lineIndexFromBindingPath(string $bindingPath): ?int
    {
        if (! preg_match('/^lines\\.(\\d+)\\./', $bindingPath, $matches)) {
            return null;
        }

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function quotationLineMergeKey(array $line): string
    {
        return implode('::', [
            (string) ($line['sample_type_id'] ?? ''),
            (string) ($line['analysis_type_id'] ?? ''),
            (string) ($line['analysis_element_id'] ?? ''),
        ]);
    }

    private function refreshAddLineParameterOptions(): void
    {
        if (! $this->crmCustomerId || ! $this->addLineSampleTypeId || ! $this->addLineAnalysisTypeId) {
            $this->addLineParameters = [];
            $this->addLineAllParametersSelected = false;
            $this->addLineCanAddWholeAnalysisType = false;

            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $allParameters = $pricing->parametersForAddLineSelection(
            $this->crmCustomerId,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId,
        );

        $this->addLineParameters = array_values(array_filter(
            $allParameters,
            fn (array $param): bool => ! $this->isQuotationParameterAlreadyInTable(
                $this->addLineSampleTypeId,
                $this->addLineAnalysisTypeId,
                (string) ($param['id'] ?? ''),
                $param['analysis_element_id'] ?? null,
            ),
        ));

        $this->addLineCanAddWholeAnalysisType = $this->canAddWholeAnalysisTypeToQuotation(
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId,
        );

        $this->addLineAllParametersSelected = $this->addLineParameters === []
            && ! $this->addLineCanAddWholeAnalysisType;
    }

    private function isQuotationParameterAlreadyInTable(
        ?string $sampleTypeId,
        ?string $analysisTypeId,
        string $parameterKey,
        mixed $analysisElementId,
    ): bool {
        $elementId = $analysisElementId !== null && $analysisElementId !== ''
            ? (string) $analysisElementId
            : $parameterKey;

        foreach ($this->lines as $line) {
            if ((string) ($line['sample_type_id'] ?? '') === (string) $sampleTypeId
                && (string) ($line['analysis_type_id'] ?? '') === (string) $analysisTypeId
                && (string) ($line['analysis_element_id'] ?? '') === $elementId) {
                return true;
            }
        }

        return false;
    }

    private function appendAllParametersForQuotationAddLine(): void
    {
        if (! $this->crmCustomerId || ! $this->addLineSampleTypeId || ! $this->addLineAnalysisTypeId) {
            return;
        }

        $parameters = app(AcceptanceFormPricingService::class)->parametersForAddLineSelection(
            $this->crmCustomerId,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId,
        );

        foreach ($parameters as $parameter) {
            if ($this->isQuotationParameterAlreadyInTable(
                $this->addLineSampleTypeId,
                $this->addLineAnalysisTypeId,
                (string) ($parameter['id'] ?? ''),
                $parameter['analysis_element_id'] ?? null,
            )) {
                continue;
            }

            $this->appendQuotationLineFromParameter($parameter);
            $this->addParameterToSampleConfigs($parameter);
        }
    }

    /**
     * @param  array<string, mixed>  $parameter
     */
    private function appendQuotationLineFromParameter(array $parameter): void
    {
        $sampleTypeId = (string) ($parameter['sample_type_id'] ?? $this->addLineSampleTypeId ?? '');
        $analysisTypeId = (string) ($parameter['analysis_type_id'] ?? $this->addLineAnalysisTypeId ?? '');
        $elementId = $parameter['analysis_element_id'] ?? null;
        $isSubcontracted = false;

        if (! empty($parameter['subcontracted'])) {
            $isSubcontracted = true;
        } elseif ($elementId) {
            $isSubcontracted = (bool) AnalysisElements::query()
                ->whereKey((string) $elementId)
                ->value('sub_contracted');
        }

        $this->lines[] = [
            'line_no' => count($this->lines) + 1,
            'sample_type_id' => $sampleTypeId,
            'sample_type_name' => $parameter['sample_type_name'] ?? SampleType::find($sampleTypeId)?->name ?? '',
            'analysis_type_id' => $analysisTypeId,
            'analysis_type_name' => $parameter['analysis_type_name'] ?? AnalysisType::find($analysisTypeId)?->name ?? '',
            'analysis_element_id' => $elementId,
            'parameter_label' => (string) ($parameter['label'] ?? 'Parameter'),
            'quantity' => 1,
            'unit_price' => (float) ($parameter['unit_amount'] ?? 0),
            'tax' => 0,
            'subcontracted' => $isSubcontracted,
        ];

        $this->reindexQuotationLines();
    }

    private function reindexQuotationLines(): void
    {
        foreach ($this->lines as $index => $line) {
            $this->lines[$index]['line_no'] = $index + 1;
        }
    }

    private function canAddWholeAnalysisTypeToQuotation(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        if ($analysisTypeId === null || $analysisTypeId === '' || ! $this->crmCustomerId) {
            return false;
        }

        $parameters = app(AcceptanceFormPricingService::class)->parametersForAddLineSelection(
            $this->crmCustomerId,
            (string) $sampleTypeId,
            (string) $analysisTypeId,
        );

        foreach ($parameters as $parameter) {
            if (! $this->isQuotationParameterAlreadyInTable(
                $sampleTypeId,
                $analysisTypeId,
                (string) ($parameter['id'] ?? ''),
                $parameter['analysis_element_id'] ?? null,
            )) {
                return true;
            }
        }

        return false;
    }
}
