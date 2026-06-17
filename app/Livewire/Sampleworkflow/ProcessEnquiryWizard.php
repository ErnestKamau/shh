<?php

namespace App\Livewire\Sampleworkflow;

use App\Livewire\Sampleworkflow\Concerns\ManagesSampleConfigurationWizard;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\QuotationHeader;
use App\Services\Commercial\EnquiryReviewDisplayService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class ProcessEnquiryWizard extends Component
{
    use ManagesSampleConfigurationWizard;

    public bool $showModal = false;

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

    public bool $pdfGenerated = false;

    public bool $quotationSent = false;

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
        $this->crmCustomerId = (string) ($enquiry->crm_customer_id ?? '');
        $this->quotationHeaderId = $enquiry->current_quotation_header_id;
        $this->quoteNumber = (string) ($enquiry->currentQuotation?->quote_number ?? '');
        $this->statusMessage = '';
        $this->statusLevel = 'info';
        $this->pdfGenerated = ! empty($header?->upload_url);
        $this->quotationSent = $this->enquiryQuotationWasSent($enquiry, $header);

        if ($header !== null && $header->details->isNotEmpty()) {
            $this->lines = $quotationService->buildInlineLinesFromQuotationHeader($header);
        } else {
            $this->lines = $quotationService->buildInlineLines($enquiry);
        }

        $this->loadSampleConfigs($enquiry);
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

        $this->activeStep = $step;
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

        $enquiry->enquiry_notes = SampleSubmissionRequest::mergeEnquiryNotesPreservingFeedback(
            $this->enquiryNotes,
            $enquiry->enquiry_notes
        );
        if ($enquiry->status === SampleSubmissionRequest::STATUS_REQUESTED
            || $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW) {
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
        }
        $enquiry->save();

        $this->activeStep = 'sample_config';
        $this->setStatus('info', '');
    }

    public function saveSampleConfigAndContinue(): void
    {
        if ($this->enquiryId === null || ! $this->crmCustomerId) {
            $this->setStatus('error', 'Customer is required for sample configuration.');

            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $this->sampleConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs);

        try {
            $configService->validateConfigs($this->sampleConfigs);
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

        $this->lines = $quotationService->buildInlineLinesFromAcceptanceLines($enquiry, $acceptanceLines);

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
        $this->persistQuotationLines();
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
            ->with(['requestedAnalyses', 'currentQuotation'])
            ->find($this->enquiryId);

        if ($enquiry === null) {
            throw new \RuntimeException('Enquiry not found.');
        }

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

    private function setStatus(string $level, string $message): void
    {
        $this->statusLevel = $level;
        $this->statusMessage = $message;
    }
}
