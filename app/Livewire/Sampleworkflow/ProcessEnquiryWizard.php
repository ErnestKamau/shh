<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\EnquiryReviewDisplayService;
use App\Services\Commercial\QuotationFromEnquiryService;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class ProcessEnquiryWizard extends Component
{
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

    /** @var list<array{
     *     sample_description: string,
     *     qty: string,
     *     sample_type: string,
     *     sample_condition: string,
     *     tests: string
     * }> */
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

    /** @return list<array{key: string, label: string}> */
    public function getWizardStepsProperty(): array
    {
        return [
            ['key' => 'review', 'label' => 'Review enquiry'],
            ['key' => 'quotation', 'label' => 'Quotation & send'],
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
                'currentQuotation',
                'submissionFormInstance.submissionForm',
                'submissionFormInstance.crmCustomer',
            ])
            ->find($enquiryId);

        if ($enquiry === null) {
            return;
        }

        $this->enquiryId = $enquiry->id;
        $this->activeStep = 'review';
        $display = app(EnquiryReviewDisplayService::class);

        $this->enquiryNotes = $enquiry->staffCommercialNotes();
        $this->customerFeedbackNotes = $enquiry->customerFeedbackNotes();
        $this->customerName = $display->customerName($enquiry);
        $this->requestReference = (string) ($enquiry->unique_identification ?? $enquiry->getFormattedNumberAttribute());
        $this->enquiryStatus = (string) $enquiry->status;
        $this->sourceChannel = (string) ($enquiry->source_channel ?? '');
        $this->collectionDataRows = $display->collectionDataRows($enquiry);
        $this->statementOfConformityOptions = $display->statementOfConformityOptions($enquiry);
        $this->displaySampleRows = $display->sampleRows($enquiry);
        $this->requestedTests = $display->requestedTests($enquiry);
        $this->submissionFormInstanceId = $enquiry->submission_form_instance_id;
        $this->submissionFormId = $enquiry->submissionFormInstance?->submission_form_id;
        $this->sampleLines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        $this->quotationHeaderId = $enquiry->current_quotation_header_id;
        $this->quoteNumber = (string) ($enquiry->currentQuotation?->quote_number ?? '');
        $this->sendPortal = true;
        $this->sendEmail = false;
        $this->statusMessage = '';
        $this->statusLevel = 'info';
        $this->pdfGenerated = ! empty($enquiry->currentQuotation?->upload_url);
        $this->lines = app(QuotationFromEnquiryService::class)->buildInlineLines($enquiry);
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
        if (! in_array($step, ['review', 'quotation'], true)) {
            return;
        }

        if ($step === 'quotation') {
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

        try {
            $this->ensureQuotationHeader();
            $this->activeStep = 'quotation';
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

            app(QuotationFromEnquiryService::class)->sendToCustomer(
                $enquiry,
                $header->fresh(),
                $this->sendPortal,
                $this->sendEmail,
            );

            $this->closeWizard();
            $this->dispatch('process-enquiry-completed');
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
            session()->flash('message', 'Quotation marked as accepted. Request is ready for physical receive.');
        } catch (Throwable $exception) {
            $this->setStatus('error', $exception->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sampleworkflow.process-enquiry-wizard');
    }

    private function persistQuotationLines(): void
    {
        $header = $this->resolveQuotationHeader();
        if ($header === null || $this->lines === []) {
            return;
        }

        app(QuotationFromEnquiryService::class)->persistInlineLines($header, $this->lines);
        $this->quoteNumber = (string) $header->fresh()->quote_number;
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
        $enquiry->refresh();

        $this->quotationHeaderId = $header->id;
        $this->quoteNumber = (string) $header->quote_number;
        $this->lines = $quotationService->buildInlineLines($enquiry);
        $this->pdfGenerated = ! empty($header->upload_url);

        return $header;
    }

    private function setStatus(string $level, string $message): void
    {
        $this->statusLevel = $level;
        $this->statusMessage = $message;
    }
}
