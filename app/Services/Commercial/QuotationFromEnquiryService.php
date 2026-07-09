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
use App\Services\Billing\QuotationPricingResolver;
use App\Services\Billing\QuotationReportService;
use App\Services\Billing\QuotationRevisionService;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use RuntimeException;

final class QuotationFromEnquiryService
{
    public function __construct(
        private AcceptanceFormPricingService $pricingService,
        private UncertaintyBudgetResolver $uncertaintyBudgetResolver,
        private QuotationLineTaxResolver $taxResolver,
        private QuotationRevisionService $quotationRevisionService,
        private QuotationPricingResolver $quotationPricingResolver,
    ) {}

    public function createOrOpen(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $enquiry->loadMissing(['customer', 'contact', 'requestedAnalyses']);

        if ($this->isSourceQuotationFeatureAvailable()
            && $enquiry->quotation_source_mode === SampleSubmissionRequest::QUOTATION_SOURCE_FROM_EXISTING
            && ! empty($enquiry->selected_source_quotation_header_id)) {
            $linked = $this->resolveLinkedSourceQuotation($enquiry);
            if ($linked !== null) {
                return $linked;
            }
        }

        if ($enquiry->current_quotation_header_id) {
            $existing = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
            if ($existing !== null) {
                $existing = $this->syncHeaderCustomerFromEnquiry($existing, $enquiry);
                $existing = $this->syncHeaderPricelistAndCurrency($existing, $enquiry);

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
            $isSubcontracted = false;

            $label = (string) ($analysis->analysis_label ?? 'Parameter');
            if ($elementId !== '') {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                if ($element !== null) {
                    $label = (string) ($element->analyte->name ?? $element->name ?? $label);
                    $isSubcontracted = (bool) ($element->sub_contracted ?? false);
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
                'subcontracted' => $isSubcontracted,
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
                    'subcontracted' => $this->isElementSubcontracted($elementId),
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
                : $this->quotationPricingResolver->suggestPrefillUnitPrice(
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
                'subcontracted' => (bool) (
                    $line['subcontracted']
                    ?? ($elementId !== '' ? $this->isElementSubcontracted($elementId) : false)
                ),
            ];
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics($lines);
    }

    private function isElementSubcontracted(?string $analysisElementId): bool
    {
        $elementId = trim((string) $analysisElementId);
        if ($elementId === '') {
            return false;
        }

        return (bool) AnalysisElements::query()
            ->whereKey($elementId)
            ->value('sub_contracted');
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
     * @return Collection<int, QuotationHeader>
     */
    public function listReusableCustomerQuotations(string $customerId): Collection
    {
        if ($customerId === '') {
            return collect();
        }

        return QuotationHeader::query()
            ->with('details')
            ->where('crm_customer_id', $customerId)
            ->where('status', 'Quote Complete')
            ->where(function ($query): void {
                $query->whereNull('expiring_date')
                    ->orWhereDate('expiring_date', '>=', now()->toDateString());
            })
            ->orderByDesc('quote_date')
            ->orderByDesc('id')
            ->get();
    }

    public function summarizeQuotationAnalysisTypes(QuotationHeader $header): string
    {
        $header->loadMissing('details');
        $detailIds = $header->details->pluck('id')->all();

        $analysisTypeIds = collect();

        if ($detailIds !== []) {
            $analysisTypeIds = $analysisTypeIds->merge(
                QuotationDetailAnalysisSplit::query()
                    ->whereIn('quotation_detail_id', $detailIds)
                    ->pluck('analysis_type_id')
            );
        }

        foreach ($header->details as $detail) {
            $partIds = array_values(array_filter(array_map(
                'trim',
                explode(',', (string) ($detail->part_no ?? ''))
            )));
            $analysisTypeIds = $analysisTypeIds->merge($partIds);
        }

        $uniqueIds = $analysisTypeIds->unique()->filter()->values();

        if ($uniqueIds->isEmpty()) {
            return '';
        }

        return AnalysisType::query()
            ->whereIn('id', $uniqueIds->all())
            ->orderBy('name')
            ->pluck('name')
            ->implode(', ');
    }

    public function linkExistingQuotationToEnquiry(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $source,
    ): SampleSubmissionRequest {
        if ((string) $source->crm_customer_id !== (string) $enquiry->crm_customer_id) {
            throw new RuntimeException('Selected quotation does not belong to this customer.');
        }

        if ((string) $source->status !== 'Quote Complete') {
            throw new RuntimeException('Only completed quotations can be reused.');
        }

        if ($source->expiring_date !== null
            && Carbon::parse($source->expiring_date)->lt(now()->startOfDay())) {
            throw new RuntimeException('Selected quotation has expired.');
        }

        if ($this->hasSelectedSourceQuotationColumn()) {
            $enquiry->selected_source_quotation_header_id = (string) $source->id;
        }
        $enquiry->current_quotation_header_id = (string) $source->id;
        if ($this->hasQuotationSourceModeColumn()) {
            $enquiry->quotation_source_mode = SampleSubmissionRequest::QUOTATION_SOURCE_FROM_EXISTING;
        }

        if ($enquiry->status === SampleSubmissionRequest::STATUS_REQUESTED
            || $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW) {
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
        }

        $enquiry->save();

        return $enquiry->fresh(['currentQuotation', 'selectedSourceQuotation']);
    }

    public function ensureEditableQuotationForEnquiry(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        $enquiry->refresh();

        if (! $this->isSourceQuotationFeatureAvailable()
            || $enquiry->quotation_source_mode !== SampleSubmissionRequest::QUOTATION_SOURCE_FROM_EXISTING
            || empty($enquiry->selected_source_quotation_header_id)) {
            return $this->createOrOpen($enquiry);
        }

        $sourceId = (string) $enquiry->selected_source_quotation_header_id;
        $currentId = (string) ($enquiry->current_quotation_header_id ?? '');

        if ($currentId !== '' && $currentId !== $sourceId) {
            $existing = QuotationHeader::query()->with('details')->find($currentId);
            if ($existing !== null) {
                return $existing;
            }
        }

        $source = QuotationHeader::query()->with('details')->find($sourceId);
        if ($source === null) {
            throw new RuntimeException('Selected source quotation was not found.');
        }

        if ($currentId === $sourceId) {
            return $this->cloneAsEnquiryQuotation($enquiry, $source);
        }

        return $this->linkExistingQuotationToEnquiry($enquiry, $source)->currentQuotation
            ?? $source;
    }

    public function cloneAsEnquiryQuotation(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $source,
    ): QuotationHeader {
        return DB::transaction(function () use ($enquiry, $source): QuotationHeader {
            $source->loadMissing('details');

            $preparedById = Auth::id();
            if ($preparedById === null) {
                throw new RuntimeException('You must be signed in to clone a quotation.');
            }

            $header = new QuotationHeader();
            $header->crm_customer_id = $source->crm_customer_id;
            $header->crm_customer_contact_id = $this->resolveContactId($enquiry)
                ?? $source->crm_customer_contact_id;
            $header->quote_date = now()->toDateString();
            $header->expiring_date = $source->expiring_date !== null
                ? Carbon::parse($source->expiring_date)->toDateString()
                : now()->addDays(30)->toDateString();
            $header->prepared_by_id = (string) $preparedById;
            $header->quotation_type = $source->quotation_type ?? 'Analysis';
            $header->status = 'Quote Complete';
            $header->pricelist_id = $source->pricelist_id;
            $header->currency_id = $source->currency_id;
            $header->from_enquiry = true;
            $header->sample_submission_request_id = $enquiry->id;
            $header->source_quotation_header_id = $source->id;
            $header->sub_total = $source->sub_total;
            $header->tax = $source->tax;
            $header->total_amount = $source->total_amount;
            $header->service_delivery = $source->service_delivery;
            $header->payments = $source->payments;
            $header->quote_specification = $source->quote_specification;
            $header->additional_info = $source->additional_info;
            $header->payment_info = $source->payment_info;
            $header->subject = $source->subject;
            $header->sample_point_id = $source->sample_point_id;
            $header->sampling_location = $source->sampling_location;
            $header->laboratory_ref = $source->laboratory_ref;
            $header->terms_override = $source->terms_override;
            $header->structured_terms = $source->structured_terms;
            $header->show_loq_column = $source->show_loq_column ?? true;
            $header->show_mu_column = $source->show_mu_column ?? true;
            $header->show_unit_price_column = $source->show_unit_price_column ?? true;
            $header->is_draft = 0;
            $header->is_complete = 1;
            $header->is_approved = 1;
            $header->save();

            AmSpecQuotationNumberGenerator::assignIfMissing($header);

            foreach ($source->details as $detail) {
                $cloned = QuotationDetails::query()->create([
                    'quotation_header_id' => $header->id,
                    'sample_type' => $detail->sample_type,
                    'quantity' => $detail->quantity,
                    'unit_price' => $detail->unit_price,
                    'tax' => $detail->tax,
                    'part_no' => $detail->part_no,
                    'accredited_analytes' => $detail->accredited_analytes,
                    'subcontracted_analytes' => $detail->subcontracted_analytes,
                    'default_analytes' => $detail->default_analytes,
                    'sub_acc_analytes' => $detail->sub_acc_analytes,
                    'description' => $detail->description,
                    'item_name' => $detail->item_name,
                    'photo_url' => $detail->photo_url,
                    'invoicable_item_id' => $detail->invoicable_item_id,
                ]);

                $splits = QuotationDetailAnalysisSplit::query()
                    ->where('quotation_detail_id', $detail->id)
                    ->get();

                foreach ($splits as $split) {
                    QuotationDetailAnalysisSplit::query()->create([
                        'quotation_detail_id' => $cloned->id,
                        'analysis_type_id' => $split->analysis_type_id,
                    ]);
                }
            }

            $enquiry->current_quotation_header_id = (string) $header->id;
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    public function resolveLinkedSourceQuotation(SampleSubmissionRequest $enquiry): ?QuotationHeader
    {
        if (! $this->isSourceQuotationFeatureAvailable()) {
            return null;
        }

        $sourceId = (string) $enquiry->selected_source_quotation_header_id;
        $currentId = (string) ($enquiry->current_quotation_header_id ?? '');

        if ($currentId !== '') {
            $current = QuotationHeader::query()->find($currentId);
            if ($current !== null) {
                return $this->syncHeaderCustomerFromEnquiry(
                    $this->syncHeaderPricelistAndCurrency($current, $enquiry),
                    $enquiry,
                );
            }
        }

        $source = QuotationHeader::query()->find($sourceId);
        if ($source === null) {
            return null;
        }

        $this->linkExistingQuotationToEnquiry($enquiry->fresh(), $source);

        return $source->fresh(['details']);
    }

    private function hasQuotationSourceModeColumn(): bool
    {
        return Schema::hasColumn('sample_submission_requests', 'quotation_source_mode');
    }

    private function hasSelectedSourceQuotationColumn(): bool
    {
        return Schema::hasColumn('sample_submission_requests', 'selected_source_quotation_header_id');
    }

    private function isSourceQuotationFeatureAvailable(): bool
    {
        return $this->hasQuotationSourceModeColumn() && $this->hasSelectedSourceQuotationColumn();
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
        $lineNo = 1;

        foreach ($header->details->sortBy('id')->values() as $detail) {
            foreach ($this->expandDetailToInlineRows($header, $detail) as $row) {
                $row['line_no'] = $lineNo++;
                $lines[] = $row;
            }
        }

        if ($lines === []) {
            return $lines;
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics($lines);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function expandDetailToInlineRows(QuotationHeader $header, QuotationDetails $detail): array
    {
        $sampleTypeId = trim((string) ($detail->sample_type ?? ''));
        $quantity = max(1, (int) ($detail->quantity ?? 1));
        $unitPrice = (float) ($detail->unit_price ?? 0);
        $tax = (float) ($detail->tax ?? 0);
        $subcontractedIds = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($detail->subcontracted_analytes ?? ''))
        )));

        if (($header->quotation_type ?? 'Analysis') === 'General') {
            return [[
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => '',
                'analysis_type_name' => '',
                'analysis_element_id' => null,
                'parameter_label' => (string) ($detail->item_name ?: $detail->description ?: 'Item'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => $tax,
                'subcontracted' => false,
            ]];
        }

        $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
        $analysisTypeIds = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($detail->part_no ?? ''))
        )));

        $expandedRows = [];

        if ($elementIds !== []) {
            foreach ($elementIds as $elementId) {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                $analysisTypeId = (string) ($element?->analysis_type_id ?? '');
                if ($analysisTypeId === '' && $analysisTypeIds !== []) {
                    $analysisTypeId = $analysisTypeIds[0];
                }

                $expandedRows[] = $this->makeInlineRowFromDetail(
                    $sampleTypeId,
                    $analysisTypeId,
                    $elementId,
                    (string) ($element?->analyte?->name ?? $element?->name ?? $detail->description ?? 'Parameter'),
                    $quantity,
                    $unitPrice,
                    $tax,
                    in_array($elementId, $subcontractedIds, true),
                );
            }
        } elseif ($analysisTypeIds !== []) {
            foreach ($analysisTypeIds as $analysisTypeId) {
                $analysisType = AnalysisType::query()->find($analysisTypeId);
                $elements = $analysisType?->active_analysis_elements() ?? collect();

                if ($elements->isEmpty()) {
                    $expandedRows[] = $this->makeInlineRowFromDetail(
                        $sampleTypeId,
                        $analysisTypeId,
                        null,
                        (string) ($analysisType?->name ?? $detail->description ?? 'Analysis'),
                        $quantity,
                        $unitPrice,
                        $tax,
                        false,
                    );

                    continue;
                }

                foreach ($elements as $element) {
                    $elementId = (string) $element->id;
                    $elementModel = AnalysisElements::query()->with('analyte')->find($elementId);
                    $expandedRows[] = $this->makeInlineRowFromDetail(
                        $sampleTypeId,
                        $analysisTypeId,
                        $elementId,
                        (string) ($elementModel?->analyte?->name ?? $elementModel?->name ?? 'Parameter'),
                        $quantity,
                        $unitPrice,
                        $tax,
                        in_array($elementId, $subcontractedIds, true),
                    );
                }
            }
        } else {
            $expandedRows[] = $this->makeInlineRowFromDetail(
                $sampleTypeId,
                '',
                null,
                (string) ($detail->description ?? 'Parameter'),
                $quantity,
                $unitPrice,
                $tax,
                false,
            );
        }

        if (count($expandedRows) <= 1) {
            return $expandedRows;
        }

        $pricePerRow = round($unitPrice / count($expandedRows), 2);
        $remainder = round($unitPrice - ($pricePerRow * count($expandedRows)), 2);

        foreach ($expandedRows as $index => &$row) {
            $row['unit_price'] = $pricePerRow + ($index === 0 ? $remainder : 0.0);
        }
        unset($row);

        return $expandedRows;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeInlineRowFromDetail(
        string $sampleTypeId,
        string $analysisTypeId,
        ?string $elementId,
        string $parameterLabel,
        int $quantity,
        float $unitPrice,
        float $tax,
        bool $subcontracted,
    ): array {
        return [
            'sample_type_id' => $sampleTypeId,
            'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
            'analysis_type_id' => $analysisTypeId,
            'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
            'analysis_element_id' => $elementId !== null && $elementId !== '' ? $elementId : null,
            'parameter_label' => $parameterLabel,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax' => $tax,
            'subcontracted' => $subcontracted,
        ];
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
            app(QuotationAcceptanceTatService::class)->stampFirstSentAt($enquiry, $now);
            $enquiry->save();

            $instance = $enquiry->submissionFormInstance;
            if ($instance !== null) {
                try {
                    app(SubmissionFormInstanceDocumentAttachmentService::class)->attachQuotation(
                        $instance,
                        $header->fresh() ?? $header,
                        Auth::id(),
                    );
                } catch (\Throwable $exception) {
                    Log::warning('Failed to attach quotation PDF to submission instance.', [
                        'instance_id' => $instance->id,
                        'quotation_id' => $header->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

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

    public function syncHeaderPricelistAndCurrency(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
    ): QuotationHeader {
        $enquiry->loadMissing('customer');
        $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);
        $currencyId = $pricelist?->currency_id ?? $enquiry->customer?->currency_id;

        $dirty = false;

        if ($pricelist !== null && (string) $header->pricelist_id !== (string) $pricelist->id) {
            $header->pricelist_id = $pricelist->id;
            $dirty = true;
        }

        if ($currencyId !== null && (string) $header->currency_id !== (string) $currencyId) {
            $header->currency_id = $currencyId;
            $dirty = true;
        }

        if ($dirty) {
            $header->save();

            return $header->fresh(['currency']) ?? $header;
        }

        return $header;
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
        $acceptedAt = now();

        $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED;
        $enquiry->accepted_quotation_header_id = (string) $quotation->id;
        $enquiry->quotation_accepted_at = $acceptedAt;
        $enquiry->save();

        app(QuotationAcceptanceTatService::class)->recalculateCustomerTat((string) $enquiry->crm_customer_id);

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
