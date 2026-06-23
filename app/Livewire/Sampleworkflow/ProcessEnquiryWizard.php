<?php

namespace App\Livewire\Sampleworkflow;

use App\AnalysisType;
use App\Livewire\Sampleworkflow\Concerns\ManagesSampleConfigurationWizard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Commercial\CommercialEnquiryConfigSyncService;
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

    /** Hides the Condition of sample column on the sample config table (Process Enquiry does not need it). */
    public bool $showSampleConditionOnConfig = false;

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

    /** @return list<array{key: string, label: string}> */
    public function getWizardStepsProperty(): array
    {
        return [
            ['key' => 'review', 'label' => 'Review enquiry'],
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
            $qty = max(1, (int) ($line['quantity'] ?? 1));
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
                'submissionFormInstance.testRequestFormInstance',
            ])
            ->find($enquiryId);

        if ($enquiry === null) {
            return;
        }

        if ($enquiry->submissionFormInstance !== null) {
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
                    'submissionFormInstance.testRequestFormInstance',
                ])
                ->find($enquiryId);
        }

        if ($enquiry === null) {
            return;
        }

        $quotationService = app(QuotationFromEnquiryService::class);
        $enquiry = $quotationService->ensureEnquiryReflectsSentQuotation($enquiry);
        $header = $enquiry->currentQuotation;

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
            $this->sendPortal = true;
            $this->sendEmail = false;
        }
        $this->collectionDataRows = $display->collectionDataRows($enquiry);
        $this->statementOfConformityOptions = $display->statementOfConformityOptions($enquiry);
        $this->displaySampleRows = $display->sampleRows($enquiry);
        $this->requestedTests = $display->requestedTests($enquiry);
        $this->submissionFormInstanceId = $enquiry->submission_form_instance_id;
        $this->submissionFormId = $enquiry->submissionFormInstance?->submission_form_id;
        $this->sampleLines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
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

        if ($header !== null && $header->details->isNotEmpty()) {
            $this->lines = $quotationService->buildInlineLinesFromQuotationHeader($header);
        } else {
            $this->lines = $quotationService->buildInlineLines($enquiry);
        }

        $this->loadSampleConfigs($enquiry);
        $this->normalizeSampleConfigs();
        $this->activeStep = $this->resolveOpeningStep($enquiry, $header);
        $this->statusMessage = $this->quotationSent
            ? 'Quotation '.$this->quoteNumber.' has been sent. Saved sample configuration and pricing are shown below.'
            : '';
        $this->statusLevel = $this->quotationSent ? 'info' : 'info';
        $this->showModal = true;
        $this->dispatch('show-process-enquiry-modal');
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->dispatch('hide-process-enquiry-modal');
    }

    public function goToStep(string $step): void
    {
        if (! in_array($step, ['review', 'sample_config', 'pricing'], true)) {
            return;
        }

        if ($step === 'pricing') {
            $this->ensureQuotationHeader();
        }

        if ($step === 'sample_config') {
            $this->quotationManuallyEdited = false;
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

        $this->maybeAutoMergeAssignedPricelist($enquiry);

        $this->normalizeSampleConfigs();
        $this->activeStep = 'sample_config';
        $this->setStatus('info', '');
    }

    public function syncFromContractPricelist(): void
    {
        if ($this->enquiryId === null || ! $this->crmCustomerId) {
            $this->setStatus('error', 'Customer is required to sync from contract pricelist.');

            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $assignedPricelist = $pricing->resolveCustomerAssignedPricelist($this->crmCustomerId);

        if ($assignedPricelist === null) {
            $this->setStatus('error', 'No contract pricelist is assigned to this customer. Assign a pricelist in the customer record first.');

            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
        if ($enquiry === null) {
            return;
        }

        $trfSeeds = $this->buildTrfLineSeedsForConfigSync($enquiry);
        $syncService = app(CommercialEnquiryConfigSyncService::class);
        $this->sampleConfigs = $syncService->mergePricelistIntoSampleConfigs(
            $this->crmCustomerId,
            $this->sampleConfigs,
            $trfSeeds,
            true,
            $assignedPricelist,
        );

        $this->setStatus('success', 'Sample configuration updated from contract pricelist.');
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
        $this->sampleConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs);

        try {
            $configService->validateConfigs($this->sampleConfigs, requireLabSection: true);
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
        $this->lines = $this->mergePreservedQuotationLineValues($existingLines, $this->lines);
        $this->quotationManuallyEdited = false;

        try {
            $this->ensureQuotationHeader();
            $this->persistQuotationLines();
            $this->activeStep = 'pricing';
            $this->setStatus('info', '');
        } catch (Throwable $exception) {
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function updatedLines(): void
    {
        $this->quotationManuallyEdited = true;
        $this->refreshLineLabMetrics();
        $this->persistQuotationLines();
    }

    public function openAddQuotationLineModal(): void
    {
        if (! $this->crmCustomerId) {
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

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->reindexQuotationLines();
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
            $this->setStatus('error', 'No contract pricelist is assigned to this customer. Price cannot be reset from pricelist.');

            return;
        }

        $line = $this->lines[$index];
        $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
        $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
        $elementId = (string) ($line['analysis_element_id'] ?? '');

        $this->lines[$index]['unit_price'] = $pricing->resolveLinePrice(
            $pricelist,
            $sampleTypeId,
            $analysisTypeId,
            $elementId !== '' ? $elementId : null,
        );

        $this->quotationManuallyEdited = true;
        $this->finalizeQuotationLineMutation();
        $this->setStatus('success', 'Line price reset from contract pricelist.');
    }

    public function generatePdf(): void
    {
        try {
            $header = $this->ensureQuotationHeader();
            $this->persistQuotationLines();
            $this->persistSampleConfiguration();

            app(QuotationFromEnquiryService::class)->generatePdf($header);
            $header->refresh();

            $this->quoteNumber = (string) $header->quote_number;
            $this->pdfGenerated = ! empty($header->upload_url);
            $this->setStatus('success', 'Quotation PDF generated.');
        } catch (Throwable $exception) {
            $this->pdfGenerated = false;
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function sendQuotation(): void
    {
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

        try {
            $enquiry = SampleSubmissionRequest::query()
                ->with(['customer', 'contact', 'requestedAnalyses'])
                ->find($this->enquiryId);

            if ($enquiry === null) {
                throw new \RuntimeException('Enquiry not found.');
            }

            $header = $this->ensureQuotationHeader();
            $this->persistQuotationLines();
            $this->persistSampleConfiguration($enquiry);

            app(QuotationFromEnquiryService::class)->sendToCustomer(
                $enquiry,
                $header->fresh(),
                $this->sendPortal,
                $this->sendEmail,
            );

            $enquiry = SampleSubmissionRequest::query()
                ->with('currentQuotation')
                ->find($this->enquiryId);

            if ($enquiry !== null) {
                $enquiry = app(QuotationFromEnquiryService::class)->ensureEnquiryReflectsSentQuotation($enquiry);
                $this->enquiryStatus = (string) $enquiry->status;
                $this->quotationSent = true;
            }

            $this->dispatch('process-enquiry-completed');

            if (strtolower($this->sourceChannel) === 'walk_in') {
                $this->setStatus('success', 'Quotation sent by email. Open the request view page to record walk-in acceptance, then capture the PO.');

                return;
            }

            $this->closeWizard();
            session()->flash('message', 'Quotation sent to customer.');
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
        $this->sampleConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs);

        if (Schema::hasColumn('sample_submission_requests', 'enquiry_sample_configuration')) {
            $enquiry->enquiry_sample_configuration = $this->sampleConfigs;
            $enquiry->save();
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

    private function loadSampleConfigs(SampleSubmissionRequest $enquiry): void
    {
        $configService = app(AcceptanceFormSampleConfigService::class);
        $stored = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];

        if ($stored !== []) {
            $this->sampleConfigs = $stored;
            $this->normalizeSampleConfigs();
            $this->backfillLabSectionIdsOnConfigs();

            return;
        }

        $instance = $enquiry->submissionFormInstance;
        $sampleLineLookup = collect(is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [])
            ->keyBy(fn (array $line): string => (string) ($line['sample_type_id'] ?? '').'::'.(string) ($line['analysis_type_id'] ?? ''));

        $prefillLines = collect($this->lines)->map(function (array $line, int $index) use ($sampleLineLookup): array {
            $lookupKey = (string) ($line['sample_type_id'] ?? '').'::'.(string) ($line['analysis_type_id'] ?? '');
            $enquiryLine = $sampleLineLookup->get($lookupKey);

            return [
                'line_no' => $index + 1,
                'sample_type_id' => $line['sample_type_id'] ?? null,
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_element_id' => $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? 'Parameter',
                'number_of_samples' => (int) ($line['quantity'] ?? 1),
                'sample_condition' => is_array($enquiryLine) ? ($enquiryLine['sample_condition'] ?? null) : null,
                'sample_condition_id' => is_array($enquiryLine) ? ($enquiryLine['sample_condition_id'] ?? null) : null,
            ];
        })->all();

        if ($prefillLines === [] && is_array($enquiry->sample_lines) && $enquiry->sample_lines !== []) {
            $prefillLines = collect($enquiry->sample_lines)->map(function (array $line, int $index): array {
                return [
                    'line_no' => $index + 1,
                    'sample_type_id' => $line['sample_type_id'] ?? $enquiry->sample_type_id ?? null,
                    'analysis_type_id' => $line['analysis_type_id'] ?? $enquiry->matrix_id ?? null,
                    'analysis_element_id' => $line['analysis_element_id'] ?? null,
                    'parameter_label' => $line['parameter_label'] ?? 'Parameter',
                    'number_of_samples' => (int) ($line['number_of_samples'] ?? $enquiry->number_of_samples ?? 1),
                    'sample_condition' => $line['sample_condition'] ?? null,
                    'sample_condition_id' => $line['sample_condition_id'] ?? null,
                ];
            })->all();
        }

        $this->sampleConfigs = $configService->buildConfigsFromPrefill($prefillLines, $instance);
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
            ->with(['requestedAnalyses', 'currentQuotation', 'customer', 'testRequestFormInstance'])
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

    private function setStatus(string $level, string $message, bool $autoDismiss = false): void
    {
        $this->statusLevel = $level;
        $this->statusMessage = $message;
        $this->statusAutoDismiss = $autoDismiss || $this->isTransientStatusMessage($message);
    }

    private function isTransientStatusMessage(string $message): bool
    {
        return str_contains($message, 'No contract pricelist is assigned to this customer.');
    }

    private function finalizeQuotationLineMutation(): void
    {
        $this->quotationManuallyEdited = true;

        if ($this->enquiryId !== null) {
            $enquiry = SampleSubmissionRequest::query()->find($this->enquiryId);
            if ($enquiry !== null) {
                $quotationService = app(QuotationFromEnquiryService::class);
                $this->lines = $quotationService->applyTaxFromAssignedPricelist($enquiry, $this->lines, false);
            }
        }

        $this->refreshLineLabMetrics();
        $this->persistQuotationLines();
    }

    private function refreshLineLabMetrics(): void
    {
        $this->lines = app(UncertaintyBudgetResolver::class)->enrichLinesWithLabMetrics($this->lines);
    }

    private function maybeAutoMergeAssignedPricelist(SampleSubmissionRequest $enquiry): void
    {
        if (! $this->crmCustomerId) {
            return;
        }

        $stored = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];
        if ($stored !== []) {
            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $assignedPricelist = $pricing->resolveCustomerAssignedPricelist($this->crmCustomerId);

        if ($assignedPricelist === null) {
            return;
        }

        $syncService = app(CommercialEnquiryConfigSyncService::class);
        $this->sampleConfigs = $syncService->mergePricelistIntoSampleConfigs(
            $this->crmCustomerId,
            $this->sampleConfigs,
            $this->buildTrfLineSeedsForConfigSync($enquiry),
            true,
            $assignedPricelist,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $previousLines
     * @param  list<array<string, mixed>>  $newLines
     * @return list<array<string, mixed>>
     */
    private function mergePreservedQuotationLineValues(array $previousLines, array $newLines): array
    {
        $previousByKey = collect($previousLines)->keyBy(
            fn (array $line): string => $this->quotationLineMergeKey($line)
        );

        return array_map(function (array $line) use ($previousByKey): array {
            $previous = $previousByKey->get($this->quotationLineMergeKey($line));
            if ($previous === null) {
                return $line;
            }

            $line['unit_price'] = (float) ($previous['unit_price'] ?? $line['unit_price'] ?? 0);
            $line['tax'] = (float) ($previous['tax'] ?? $line['tax'] ?? 0);
            $line['subcontracted'] = (bool) ($previous['subcontracted'] ?? $line['subcontracted'] ?? false);

            return $line;
        }, $newLines);
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

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTrfLineSeedsForConfigSync(SampleSubmissionRequest $enquiry): array
    {
        $seeds = [];

        foreach (is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [] as $line) {
            $seeds[] = [
                'sample_type_id' => $line['sample_type_id'] ?? null,
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_element_id' => $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? 'Parameter',
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
            ];
        }

        $enquiry->loadMissing('requestedAnalyses');
        foreach ($enquiry->requestedAnalyses as $analysis) {
            $seeds[] = [
                'sample_type_id' => $analysis->sample_type_id ?? null,
                'analysis_type_id' => $analysis->analysis_type_id ?? null,
                'analysis_element_id' => $analysis->analysis_element_id ?? $analysis->analysis_key ?? null,
                'parameter_label' => $analysis->analysis_label ?? 'Parameter',
                'number_of_samples' => max(1, (int) ($analysis->number_of_samples ?? 1)),
            ];
        }

        return $seeds;
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
            'subcontracted' => false,
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
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return false;
        }

        if ($this->hasElementQuotationLinesForAnalysis($sampleTypeId, $analysisTypeId)) {
            return false;
        }

        return ! $this->isWholeAnalysisTypeInQuotationTable($sampleTypeId, $analysisTypeId);
    }

    private function isWholeAnalysisTypeInQuotationTable(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        return collect($this->lines)->contains(
            fn (array $line): bool => $this->quotationLineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && empty($line['analysis_element_id'])
        );
    }

    private function hasElementQuotationLinesForAnalysis(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        return collect($this->lines)->contains(
            fn (array $line): bool => $this->quotationLineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && ! empty($line['analysis_element_id'])
        );
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function quotationLineMatchesSampleAndAnalysis(
        array $line,
        ?string $sampleTypeId,
        ?string $analysisTypeId,
    ): bool {
        return (string) ($line['sample_type_id'] ?? '') === (string) $sampleTypeId
            && (string) ($line['analysis_type_id'] ?? '') === (string) $analysisTypeId;
    }
}
