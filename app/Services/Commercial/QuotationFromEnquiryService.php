<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\Billing\PricelistCustomer;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Billing\QuotationReportService;
use App\Services\Billing\QuotationRevisionService;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class QuotationFromEnquiryService
{
    public function __construct(
        private AcceptanceFormPricingService $pricingService,
        private UncertaintyBudgetResolver $uncertaintyBudgetResolver,
        private QuotationLineTaxResolver $taxResolver,
        private QuotationRevisionService $quotationRevisionService,
    ) {}

    public function createOrOpen(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $enquiry->loadMissing(['customer', 'contact', 'requestedAnalyses']);

        if ($enquiry->current_quotation_header_id) {
            $existing = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
            if ($existing !== null) {
                $existing = $this->syncHeaderCustomerFromEnquiry($existing, $enquiry);

                if ($enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW
                    && $existing->sent_to_customer_at !== null) {
                    return $this->createRevision($enquiry, $existing);
                }

                return $existing;
            }
        }

        return DB::transaction(function () use ($enquiry): QuotationHeader {
            $contactId = $this->resolveContactId($enquiry);
            $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);
            $preparedById = Auth::id();

            if ($preparedById === null) {
                throw new RuntimeException('You must be signed in to create a quotation.');
            }

            $header = new QuotationHeader();
            $header->crm_customer_id = $enquiry->crm_customer_id;
            $header->crm_customer_contact_id = $contactId;
            $header->quote_date = now()->toDateString();
            $header->expiring_date = now()->addDays(30)->toDateString();
            $header->prepared_by_id = (string) $preparedById;
            $header->quotation_type = 'Analysis';
            $header->status = 'Quote Complete';
            $header->from_enquiry = true;
            $header->sample_submission_request_id = $enquiry->id;
            $header->pricelist_id = $pricelist?->id;
            $header->currency_id = $pricelist?->currency_id
                ?? $enquiry->customer?->currency_id;
            $header->is_draft = 0;
            $header->is_complete = 1;
            $header->is_approved = 1;
            $header->save();

            AmSpecQuotationNumberGenerator::assignIfMissing($header);

            app(QuotationReportService::class)->seedDefaultTermsOfSale($header);
            app(QuotationReportService::class)->seedDefaultStructuredTerms($header);

            $lines = $this->buildInlineLines($enquiry);
            $this->persistInlineLines($header, $lines);

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            if ($this->hasContractPricelist((string) $enquiry->crm_customer_id)) {
                $enquiry->pricing_source = 'contract';
            }
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildInlineLines(SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing('requestedAnalyses');
        $customerId = (string) $enquiry->crm_customer_id;
        $pricelist = $this->pricingService->resolvePricelist($customerId);
        $lines = [];

        foreach ($enquiry->requestedAnalyses as $index => $analysis) {
            $sampleTypeId = (string) ($analysis->sample_type_id ?? $enquiry->sample_type_id ?? $enquiry->batch_sample_type_id ?? '');
            $analysisTypeId = (string) ($analysis->analysis_type_id ?? $enquiry->matrix_id ?? '');
            $elementId = (string) ($analysis->analysis_element_id ?? $analysis->analysis_key ?? '');
            $qty = max(1, (int) ($analysis->number_of_samples ?? $enquiry->number_of_samples ?? 1));

            $label = (string) ($analysis->analysis_label ?? 'Parameter');
            if ($elementId !== '') {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                if ($element !== null) {
                    $label = (string) ($element->analyte->name ?? $element->name ?? $label);
                }
            }

            $unitPrice = $this->pricingService->resolveLinePrice(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId !== '' ? $elementId : null,
            );

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'parameter_label' => $label,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax' => $this->taxResolver->resolveLineTaxPercent(
                    $pricelist,
                    $sampleTypeId !== '' ? $sampleTypeId : null,
                    $analysisTypeId,
                    $elementId !== '' ? $elementId : null,
                ),
                'subcontracted' => false,
            ];
        }

        if ($lines === [] && is_array($enquiry->parameter_ids) && $enquiry->parameter_ids !== []) {
            foreach ($enquiry->parameter_ids as $index => $parameterId) {
                $sampleTypeId = (string) ($enquiry->sample_type_id ?? '');
                $analysisTypeId = (string) ($enquiry->matrix_id ?? '');
                $elementId = trim((string) $parameterId);
                $qty = max(1, (int) ($enquiry->number_of_samples ?? 1));
                $lines[] = [
                    'line_no' => $index + 1,
                    'sample_type_id' => $sampleTypeId,
                    'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                    'analysis_type_id' => $analysisTypeId,
                    'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                    'analysis_element_id' => $elementId,
                    'parameter_label' => 'Parameter',
                    'quantity' => $qty,
                    'unit_price' => $this->pricingService->resolveLinePrice($pricelist, $sampleTypeId, $analysisTypeId, $elementId),
                    'tax' => $this->taxResolver->resolveLineTaxPercent(
                        $pricelist,
                        $sampleTypeId !== '' ? $sampleTypeId : null,
                        $analysisTypeId,
                        $elementId,
                    ),
                    'subcontracted' => false,
                ];
            }
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics($lines);
    }

    /**
     * @param  list<array<string, mixed>>  $acceptanceLines
     * @return list<array<string, mixed>>
     */
    public function buildInlineLinesFromAcceptanceLines(SampleSubmissionRequest $enquiry, array $acceptanceLines): array
    {
        $customerId = (string) $enquiry->crm_customer_id;
        $pricelist = $this->pricingService->resolveCustomerAssignedPricelist($customerId);
        $lines = [];

        foreach ($acceptanceLines as $index => $line) {
            $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $qty = max(1, (int) ($line['number_of_samples'] ?? $line['quantity'] ?? 1));
            $unitPrice = isset($line['unit_price']) || isset($line['unit_amount'])
                ? (float) ($line['unit_price'] ?? $line['unit_amount'] ?? 0)
                : $this->pricingService->resolveLinePrice(
                    $pricelist,
                    $sampleTypeId,
                    $analysisTypeId,
                    $elementId !== '' ? $elementId : null,
                );

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax' => $this->taxResolver->resolveLineTaxPercent(
                    $pricelist,
                    $sampleTypeId !== '' ? $sampleTypeId : null,
                    $analysisTypeId,
                    $elementId !== '' ? $elementId : null,
                ),
                'subcontracted' => (bool) ($line['subcontracted'] ?? false),
            ];
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics($lines);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyTaxFromAssignedPricelist(SampleSubmissionRequest $enquiry, array $lines, bool $overwrite = true): array
    {
        $pricelist = $this->pricingService->resolveCustomerAssignedPricelist((string) $enquiry->crm_customer_id);

        return $this->taxResolver->applyTaxToLines($pricelist, $lines, $overwrite);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyTaxFromPricelist(SampleSubmissionRequest $enquiry, array $lines, bool $overwrite = true): array
    {
        $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);

        return $this->taxResolver->applyTaxToLines($pricelist, $lines, $overwrite);
    }

    /**
     * Internal quotation snapshot for scheduled contract customers (not sent to customer).
     */
    public function createInternalContractQuotation(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        return DB::transaction(function () use ($enquiry): QuotationHeader {
            $enquiry->loadMissing(['customer', 'contact', 'requestedAnalyses']);

            $contactId = $this->resolveContactId($enquiry);
            $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);
            $preparedById = Auth::id();

            $header = new QuotationHeader();
            $header->crm_customer_id = $enquiry->crm_customer_id;
            $header->crm_customer_contact_id = $contactId;
            $header->quote_date = now()->toDateString();
            $header->expiring_date = now()->addDays(30)->toDateString();
            $header->prepared_by_id = $preparedById !== null ? (string) $preparedById : null;
            $header->quotation_type = 'Analysis';
            $header->status = 'Quote Complete';
            $header->from_enquiry = true;
            $header->sample_submission_request_id = $enquiry->id;
            $header->pricelist_id = $pricelist?->id;
            $header->currency_id = $pricelist?->currency_id
                ?? $enquiry->customer?->currency_id;
            $header->is_draft = 0;
            $header->is_complete = 1;
            $header->is_approved = 1;
            $header->save();

            AmSpecQuotationNumberGenerator::assignIfMissing($header);

            $lines = $this->buildInlineLines($enquiry);
            $this->persistInlineLines($header, $lines);

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->accepted_quotation_header_id = (string) $header->id;
            if ($this->hasContractPricelist((string) $enquiry->crm_customer_id)) {
                $enquiry->pricing_source = 'contract';
            } elseif ((string) $enquiry->pricing_source === '') {
                $enquiry->pricing_source = 'sampling_contract';
            }
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    /**
     * Rebuild inline editor rows from a persisted quotation header.
     *
     * @return list<array<string, mixed>>
     */
    public function buildInlineLinesFromQuotationHeader(QuotationHeader $header): array
    {
        $header->loadMissing('details');

        $lines = [];

        foreach ($header->details->sortBy('id')->values() as $index => $detail) {
            $elementId = trim((string) ($detail->accredited_analytes ?? $detail->default_analytes ?? ''));
            $analysisTypeId = trim((string) ($detail->part_no ?? ''));
            $sampleTypeId = trim((string) ($detail->sample_type ?? ''));

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'parameter_label' => (string) ($detail->description ?? 'Parameter'),
                'quantity' => max(1, (int) ($detail->quantity ?? 1)),
                'unit_price' => (float) ($detail->unit_price ?? 0),
                'tax' => (float) ($detail->tax ?? 0),
                'subcontracted' => trim((string) ($detail->subcontracted_analytes ?? '')) !== '',
            ];
        }

        if ($lines === []) {
            return $lines;
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics($lines);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function persistInlineLines(QuotationHeader $header, array $lines): void
    {
        QuotationDetails::query()->where('quotation_header_id', $header->id)->delete();

        $subTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($lines as $line) {
            $qty = max(1, (int) ($line['quantity'] ?? 1));
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $taxRate = (float) ($line['tax'] ?? 0);
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $elementId = (string) ($line['analysis_element_id'] ?? '');

            $detail = QuotationDetails::query()->create([
                'quotation_header_id' => $header->id,
                'sample_type' => $line['sample_type_id'] ?? null,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax' => $taxRate,
                'part_no' => $analysisTypeId,
                'accredited_analytes' => $elementId,
                'subcontracted_analytes' => ! empty($line['subcontracted']) ? $elementId : '',
                'default_analytes' => $elementId,
                'sub_acc_analytes' => '',
                'description' => (string) ($line['parameter_label'] ?? ''),
            ]);

            if ($analysisTypeId !== '') {
                QuotationDetailAnalysisSplit::query()->create([
                    'quotation_detail_id' => $detail->id,
                    'analysis_type_id' => $analysisTypeId,
                ]);
            }

            $extended = $qty * $unitPrice;
            $subTotal += $extended;
            if ($taxRate > 0) {
                $taxTotal += ($taxRate / 100) * $extended;
            }
        }

        $header->sub_total = $subTotal;
        $header->tax = $taxTotal;
        $header->total_amount = $subTotal + $taxTotal;
        $header->save();
    }

    public function generatePdf(QuotationHeader $header): QuotationHeader
    {
        $this->ensureHeaderReadyForPrint($header);

        $header = app(QuotationReportService::class)->storePdf($header->fresh());

        if (empty($header->upload_url)) {
            throw new RuntimeException('PDF was not saved. Check quotation lines and customer contact.');
        }

        return $header;
    }

    public function sendToCustomer(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        bool $sendPortal = true,
        bool $sendEmail = false,
    ): SampleSubmissionRequest {
        if (! $sendPortal && ! $sendEmail) {
            throw new RuntimeException('Select at least one delivery channel (portal or email).');
        }

        return DB::transaction(function () use ($enquiry, $header, $sendPortal, $sendEmail): SampleSubmissionRequest {
            if (empty($header->upload_url)) {
                $header = $this->generatePdf($header);
            }

            $now = now();
            $header->sent_to_customer_at = $now;
            $header->email_to_customer = $sendEmail ? $now->toDateString() : $header->email_to_customer;
            $header->status = 'Quote Complete';
            $header->save();

            if ($sendEmail && $enquiry->contact?->email) {
                $this->emailQuotation($header, $enquiry);
            }

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            $enquiry->save();

            return $this->ensureEnquiryReflectsSentQuotation($enquiry->fresh(['customer', 'contact', 'requestedAnalyses', 'currentQuotation']));
        });
    }

    public function quotationWasSentToCustomer(SampleSubmissionRequest $enquiry): bool
    {
        $enquiry->loadMissing('currentQuotation');
        $quotation = $enquiry->currentQuotation;

        if ($quotation === null && $enquiry->current_quotation_header_id) {
            $quotation = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
        }

        return $quotation !== null && $quotation->sent_to_customer_at !== null;
    }

    /**
     * Align enquiry status when a quotation was sent but the enquiry row was not updated (e.g. walk-in email before status fix).
     */
    public function ensureEnquiryReflectsSentQuotation(SampleSubmissionRequest $enquiry, ?QuotationHeader $header = null): SampleSubmissionRequest
    {
        $header ??= $enquiry->currentQuotation;

        if ($header === null && $enquiry->current_quotation_header_id) {
            $header = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
        }

        if ($header === null || $header->sent_to_customer_at === null) {
            return $enquiry;
        }

        $terminalStatuses = [
            SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW,
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
        ];

        $dirty = false;

        if ((string) $enquiry->current_quotation_header_id !== (string) $header->id) {
            $enquiry->current_quotation_header_id = $header->id;
            $dirty = true;
        }

        if (! in_array((string) $enquiry->status, $terminalStatuses, true)) {
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            $dirty = true;
        }

        if ($dirty) {
            $enquiry->save();

            return $enquiry->fresh(['customer', 'contact', 'requestedAnalyses', 'currentQuotation']);
        }

        return $enquiry;
    }

    public function createRevision(SampleSubmissionRequest $enquiry, QuotationHeader $priorHeader): QuotationHeader
    {
        return DB::transaction(function () use ($enquiry, $priorHeader): QuotationHeader {
            $header = $this->quotationRevisionService->createRevision($priorHeader, [
                'from_enquiry' => true,
                'sample_submission_request_id' => $enquiry->id,
                'status' => 'Quote Complete',
                'is_complete' => 1,
                'is_approved' => 1,
            ]);

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    public function ensureHeaderReadyForPrint(QuotationHeader $header): QuotationHeader
    {
        $header->loadMissing(['customer', 'contact', 'sampleSubmissionRequest']);

        if (empty($header->crm_customer_id) && $header->sampleSubmissionRequest !== null) {
            $header = $this->syncHeaderCustomerFromEnquiry($header, $header->sampleSubmissionRequest);
        }

        if (empty($header->crm_customer_contact_id) && ! empty($header->crm_customer_id)) {
            $contactId = CustomerContact::query()
                ->where('crm_customer_id', $header->crm_customer_id)
                ->orderBy('id')
                ->value('id');

            if ($contactId !== null) {
                $header->crm_customer_contact_id = (string) $contactId;
                $header->save();
            }
        }

        if (empty($header->prepared_by_id) && Auth::id() !== null) {
            $header->prepared_by_id = (string) Auth::id();
            $header->save();
        }

        if (empty($header->crm_customer_id)) {
            throw new RuntimeException('Quotation is missing a customer.');
        }

        return $header->fresh() ?? $header;
    }

    private function resolveContactId(SampleSubmissionRequest $enquiry): ?string
    {
        if (! empty($enquiry->crm_contact_id)) {
            return (string) $enquiry->crm_contact_id;
        }

        $fallback = CustomerContact::query()
            ->where('crm_customer_id', $enquiry->crm_customer_id)
            ->orderBy('id')
            ->value('id');

        return $fallback !== null ? (string) $fallback : null;
    }

    private function syncHeaderCustomerFromEnquiry(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
    ): QuotationHeader {
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $enquiry->loadMissing(['customer', 'contact']);

        if (empty($header->crm_customer_id) && ! empty($enquiry->crm_customer_id)) {
            $header->crm_customer_id = $enquiry->crm_customer_id;
        }

        if (empty($header->crm_customer_contact_id)) {
            $contactId = $this->resolveContactId($enquiry);
            if ($contactId !== null) {
                $header->crm_customer_contact_id = $contactId;
            }
        }

        if ($header->isDirty()) {
            $header->save();
        }

        return $header->fresh(['customer', 'contact']) ?? $header;
    }

    private function hasContractPricelist(string $customerId): bool
    {
        if ($customerId === '') {
            return false;
        }

        return PricelistCustomer::query()->where('customer_id', $customerId)->exists();
    }

    private function emailQuotation(QuotationHeader $header, SampleSubmissionRequest $enquiry): void
    {
        $contact = $enquiry->contact ?? CustomerContact::query()->find($header->crm_customer_contact_id);
        if ($contact === null || empty($contact->email) || empty($header->upload_url)) {
            return;
        }

        $company = getActiveCompany();
        $name = trim(implode(' ', array_filter([
            $contact->first_name ?? '',
            $contact->middle_name ?? '',
            $contact->last_name ?? '',
        ])));
        $message = 'Quotation '.$header->quote_number.' has been sent to you from '.($company->name ?? 'GCLA').'. Kindly find it attached.';
        $body = 'Hi '.$name.', <br>'.$message.'<br>Regards,<br>'.($company->name ?? 'GCLA');
        $subject = '['.($company->name ?? 'GCLA').'] Quotation '.$header->quote_number;
        $file = storage_path('app'.$header->upload_url);
        notify_user($body, $contact->email, $subject, $file);
    }

    /**
     * Record in-person / walk-in client acceptance of a sent quotation.
     */
    public function recordWalkInAcceptance(
        SampleSubmissionRequest $enquiry,
        ?string $clientPoNumber = null,
        bool $poSkipped = false,
    ): SampleSubmissionRequest {
        $enquiry = $this->ensureEnquiryReflectsSentQuotation(
            $enquiry->fresh(['currentQuotation'])
        );

        if (! $this->quotationWasSentToCustomer($enquiry)) {
            throw new RuntimeException('Quotation has not been sent to the customer yet.');
        }

        if ($enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_SENT) {
            throw new RuntimeException('Quotation can only be accepted when the enquiry is Quotation Sent.');
        }

        $quotation = $enquiry->currentQuotation;

        $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED;
        $enquiry->accepted_quotation_header_id = (string) $quotation->id;
        $enquiry->quotation_accepted_at = now();
        $enquiry->save();

        if ($clientPoNumber !== null || $poSkipped) {
            return app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $enquiry->fresh(),
                (string) $quotation->id,
                [
                    'client_po_number' => $clientPoNumber,
                    'po_skipped' => $poSkipped,
                ],
            );
        }

        return $enquiry->fresh(['customer', 'contact', 'requestedAnalyses']);
    }
}
